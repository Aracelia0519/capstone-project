/**
 * faceVerification.js
 * ---------------------------------------------------------------------------
 * Client-side Identity & Location Verification helpers.
 *
 * Runs entirely in the browser (no API keys, no external network calls required
 * beyond the bundled static assets served from /public):
 *
 *  - Facial recognition  : @vladmandic/face-api  -> compare the live selfie
 *    against the face found in the uploaded valid ID / business-document photo.
 *  - OCR text extraction : tesseract.js (v7)     -> read the printed ID number
 *    and the user's first/last name off the submitted ID photo so the server
 *    can re-validate the credentials and record human-readable reasons.
 *
 * The frontend produces the raw measurements. The backend re-validates name and
 * ID-number matches server-side (credentials_matched) and stores everything in
 * identity_verification_results; face_match is trusted from the client since
 * there is no server-side face API available.
 *
 * IMPORTANT (tesseract.js v7 + Vite): tesseract workers are spawned as real
 * Web Workers that need to load the core `.wasm` glue and the language data at
 * runtime. We therefore resolve workerPath / corePath / langPath against static
 * files copied into `frontend/public` (worker.min.js inside `tesseract-dist/`,
 * core glue inside `tesseract.js-core/`, and `eng.traineddata.gz` inside
 * `tessdata/`). If a future bundler change breaks `new URL(..., import.meta.url)`
 * resolution, simply import the paths with Vite's `?url` suffix instead:
 *   import workerPath from 'tesseract.js/dist/worker.min.js?url'
 *   import corePath from 'tesseract.js-core/tesseract-core-simd-lstm.wasm.js?url'
 */

import * as faceapi from '@vladmandic/face-api'
import { createWorker, OEM } from 'tesseract.js'

// ---------------------------------------------------------------------------
// Tesseract static asset paths (served from /public)
// These get copied by vite-plugin-static-copy or a manual copy in build script.
// ---------------------------------------------------------------------------
const WORKER_PATH = new URL('/tesseract.js-dist/worker.min.js', import.meta.url).href
const CORE_PATH = new URL('/tesseract.js-core/tesseract-core-simd-lstm.wasm.js', import.meta.url).href
const LANG_PATH = '/tessdata' // -> public/tessdata/eng.traineddata.gz
const LANG = 'eng'
const GZIP = true

// ---------------------------------------------------------------------------
// Face-api model directory (served from /public/face-api-models)
// ---------------------------------------------------------------------------
const FACE_MODEL_DIR = '/face-api-models'

let modelsLoaded = false
let tesseractWorker = null

/**
 * Normalise a name/number for fuzzy comparison.
 * Lowercases, collapses whitespace and strips non-alphanumeric characters.
 */
export function normalizeForCompare(value = '') {
  return String(value || '')
    .toLowerCase()
    .replace(/[^a-z0-9]/g, '')
    .trim()
}

/**
 * Levenshtein edit distance (used to absorb single-character OCR misreads).
 */
function levenshtein(a, b) {
  const m = a.length
  const n = b.length
  if (m === 0) return n
  if (n === 0) return m
  let prev = new Array(n + 1).fill(0).map((_, i) => i)
  for (let i = 1; i <= m; i++) {
    const cur = [i]
    for (let j = 1; j <= n; j++) {
      cur[j] = Math.min(
        prev[j] + 1,
        cur[j - 1] + 1,
        prev[j - 1] + (a[i - 1] === b[j - 1] ? 0 : 1)
      )
    }
    prev = cur
  }
  return prev[n]
}

/**
 * Whether any OCR token matches the expected word, allowing a small edit
 * distance for OCR noise (e.g. "CRVZ" vs "CRUZ", "JUAN" vs "JULIAN").
 * Short tokens (< 4 chars) must match exactly to avoid false positives.
 */
function tokenPresent(tokens, needle) {
  if (tokens.includes(needle)) return true
  if (needle.length < 4) return false
  const maxDist = needle.length <= 4 ? 1 : needle.length <= 8 ? 2 : 3
  return tokens.some(
    (t) => Math.abs(t.length - needle.length) <= maxDist && levenshtein(t, needle) <= maxDist
  )
}

/**
 * Tokenise a name into individual lowercase words (keeps accented letters).
 */
function wordTokens(value = '') {
  return (String(value || '').toLowerCase().match(/[a-z\u00e0-\u024f]+/g) || [])
}

/**
 * Compare two names allowing common OCR noise (separators, case, extra
 * whitespace), OCR reordering, and single-character OCR misreads. IDs print
 * names as "SURNAME, GIVEN NAME" (e.g. Philippine National ID / ePhilID) or as
 * "GIVEN NAME SURNAME" - both orders are accepted.
 *
 * Strategy:
 *  1. exact / substring compare of normalized strings, then
 *  2. the FIRST and LAST words of the typed name must both be found in the OCR
 *     text (any order, fuzzy-tolerant). Anything extra on the ID - middle
 *     names/initials, prefixes like "SURNAME," - is simply ignored.
 */
export function namesMatch(nameA, nameB) {
  const a = normalizeForCompare(nameA)
  const b = normalizeForCompare(nameB)
  if (!a || !b) return false
  if (a === b || a.includes(b) || b.includes(a)) return true

  const wordsA = wordTokens(nameA)
  const wordsB = wordTokens(nameB)
  if (wordsB.length === 0) return false
  const first = wordsB[0]
  const last = wordsB[wordsB.length - 1]
  if (first === last) return tokenPresent(wordsA, first)
  return tokenPresent(wordsA, first) && tokenPresent(wordsA, last)
}

/**
 * Compare two ID numbers ignoring separators, case and leading/trailing spaces.
 */
export function idNumbersMatch(idA, idB) {
  const a = normalizeForCompare(idA)
  const b = normalizeForCompare(idB)
  if (!a || !b) return false
  return a === b || a.includes(b) || b.includes(a)
}

/**
 * Load face-api models once (idempotent).
 * Uses TinyFaceDetector + FaceLandmarks68 + FaceRecognition.
 */
export async function loadFaceApiModels() {
  if (modelsLoaded) return
  await Promise.all([
    faceapi.nets.tinyFaceDetector.loadFromUri(FACE_MODEL_DIR),
    faceapi.nets.faceLandmark68Net.loadFromUri(FACE_MODEL_DIR),
    faceapi.nets.faceRecognitionNet.loadFromUri(FACE_MODEL_DIR),
  ])
  modelsLoaded = true
}

/**
 * Turn a File/Blob/HTMLImageElement/HTMLVideoElement/HTMLCanvasElement into a
 * face-api compatible HTMLImageElement.
 */
export function toImageElement(input, maxDim = 640) {
  return new Promise((resolve, reject) => {
    if (input instanceof HTMLImageElement && input.complete && input.naturalWidth > 0) {
      resolve(input)
      return
    }
    if (input instanceof HTMLCanvasElement || input instanceof HTMLVideoElement) {
      resolve(input)
      return
    }
    const objectUrl = typeof input === 'string' ? input : URL.createObjectURL(input)
    const img = new Image()
    img.onload = () => {
      if (img.naturalWidth > maxDim || img.naturalHeight > maxDim) {
        const scale = Math.min(maxDim / img.naturalWidth, maxDim / img.naturalHeight, 1)
        const canvas = document.createElement('canvas')
        canvas.width = Math.round(img.naturalWidth * scale)
        canvas.height = Math.round(img.naturalHeight * scale)
        canvas.getContext('2d').drawImage(img, 0, 0, canvas.width, canvas.height)
        if (typeof input !== 'string') URL.revokeObjectURL(objectUrl)
        resolve(canvas)
      } else {
        if (typeof input !== 'string') URL.revokeObjectURL(objectUrl)
        resolve(img)
      }
    }
    img.onerror = () => {
      if (typeof input !== 'string') URL.revokeObjectURL(objectUrl)
      reject(new Error('Could not read image file'))
    }
    img.src = objectUrl
  })
}

/**
 * Detect a single face descriptor from an image.
 * Returns null when no face is detected, when multiple faces are present, or
 * when confidence is too low.
 */
export async function detectFace(request) {
  await loadFaceApiModels()
  const el = await toImageElement(request)
  const detections = await faceapi
    .detectAllFaces(el, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
    .withFaceLandmarks()
    .withFaceDescriptors()

  if (detections.length === 0) {
    return { face_detected: false, descriptor: null, count: 0, reason: 'No face was detected in the photo. Make sure the photo is clear and facing forward.' }
  }
  if (detections.length > 1) {
    return { face_detected: true, descriptor: null, count: detections.length, reason: `Multiple faces detected (${detections.length}). Please use a photo with only you in it.` }
  }
  return { face_detected: true, descriptor: detections[0].descriptor, count: 1, reason: null }
}

/**
 * Compute face similarity (0..1) and match boolean.
 * - similarity = 1 - (euclideanDistance / 2), clamped to [0, 1]
 * - face_match = similarity >= threshold (default 0.35)
 */
export function compareFaces(descriptorA, descriptorB, threshold = 0.35) {
  if (!descriptorA || !descriptorB) {
    return { face_match: false, face_similarity: 0 }
  }
  const distance = faceapi.euclideanDistance(descriptorA, descriptorB)
  const similarity = Math.max(0, Math.min(1, 1 - distance / 2))
  return { face_match: similarity >= threshold, face_similarity: Math.round(similarity * 10000) / 10000 }
}

/**
 * Lazily create a long-lived Tesseract worker. The first call downloads the
 * (~9MB) language data; subsequent calls reuse the already loaded worker.
 */
export async function getTesseractWorker() {
  if (tesseractWorker) return tesseractWorker
  tesseractWorker = await createWorker(LANG, OEM.LSTM_ONLY, {
    workerPath: WORKER_PATH,
    corePath: CORE_PATH,
    langPath: LANG_PATH,
    gzip: GZIP,
  })
  return tesseractWorker
}

/**
 * Prepare an image for OCR: bring it to a good working size (upscaling small
 * screenshots of eIDs, downscaling huge photos), convert to grayscale and
 * stretch contrast so tesseract can read thin, low-contrast print.
 */
function prepareOcrCanvas(imageSrc) {
  return new Promise((resolve, reject) => {
    const isElement = imageSrc instanceof HTMLImageElement || imageSrc instanceof HTMLCanvasElement
    const srcUrl = isElement ? null : URL.createObjectURL(imageSrc)
    const img = new Image()
    img.onload = () => {
      if (srcUrl) URL.revokeObjectURL(srcUrl)
      const W = img.naturalWidth
      const H = img.naturalHeight
      if (!W || !H) return reject(new Error('Could not read image for OCR'))

      // Work at ~2200px on the longest side: enough detail for small print on
      // phone screenshots, with generous upscaling for low-res images.
      const scale = Math.min(2.5, 2200 / Math.max(W, H))
      const cw = Math.max(1, Math.round(W * scale))
      const ch = Math.max(1, Math.round(H * scale))
      const canvas = document.createElement('canvas')
      canvas.width = cw
      canvas.height = ch
      const ctx = canvas.getContext('2d', { willReadFrequently: true })
      ctx.drawImage(img, 0, 0, cw, ch)

      // Grayscale + contrast stretch into the 8..248 range.
      const imageData = ctx.getImageData(0, 0, cw, ch)
      const data = imageData.data
      let min = 255
      let max = 0
      for (let i = 0; i < data.length; i += 4) {
        const v = (0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2]) | 0
        data[i] = data[i + 1] = data[i + 2] = v
        if (v < min) min = v
        if (v > max) max = v
      }
      const range = max - min || 1
      const k = 240 / range
      for (let i = 0; i < data.length; i += 4) {
        data[i] = data[i + 1] = data[i + 2] = ((data[i] - min) * k + 8) | 0
      }
      ctx.putImageData(imageData, 0, 0)
      resolve(canvas)
    }
    img.onerror = () => {
      if (srcUrl) URL.revokeObjectURL(srcUrl)
      reject(new Error('Could not read image for OCR'))
    }
    img.src = srcUrl || (imageSrc instanceof HTMLCanvasElement ? imageSrc.toDataURL('image/png') : imageSrc.src)
  })
}

/**
 * Run OCR on an image and return the raw text plus a best-effort ID-number.
 * The image is preprocessed (upscale / grayscale / contrast) before recognition;
 * if the first pass returns (near) nothing, it retries once in sparse-text mode,
 * which is more forgiving on busy card layouts.
 */
export async function runOcr(imageFileOrUrl) {
  const worker = await getTesseractWorker()
  const prepared = await prepareOcrCanvas(imageFileOrUrl)

  let text = ''
  try {
    const { data } = await worker.recognize(prepared, { psm: '3' })
    text = (data && (data.text || '')) || ''
  } catch (e) {
    text = ''
  }

  if (!text.trim()) {
    try {
      const retry = await worker.recognize(prepared, { psm: '11' }) // sparse text
      const retryText = (retry.data && (retry.data.text || '')) || ''
      if (retryText.trim()) text = retryText
    } catch (e) {
      // keep first-pass result
    }
  }

  const trimmed = text.trim()
  return {
    ocr_text: trimmed,
    ocr_id_number: extractIdNumber(trimmed),
  }
}

/**
 * Best-effort extraction of an ID / registration number from OCR text.
 *
 * Order of preference:
 *  1. Philippine National ID style: 12 digits grouped 4-4-4 by space/dash/dot
 *     (also matches a compact 12-digit run).
 *  2. Long compact digit runs (>= 8), ignoring date-of-birth-like values.
 *  3. Alphanumeric runs (driver's license "A01-12-345678", PRC, passport),
 *     preferring tokens close to "number", "no", "id", etc.
 */
export function extractIdNumber(text = '') {
  if (!text) return null

  // 1) PH National ID: "1234 5678 9012" (12) or "4720-6490-5718-7409" (16),
  //    separated by spaces/dashes/dots on one line. Never joins across newlines,
  //    the first group must not sit directly after a date separator, and
  //    DOB-like year starts are skipped.
  const groupedMatches = text.match(
    /(?<![\d.\/])\d{4}[ \u00a0.\-]? ?\d{4}[ \u00a0.\-]? ?\d{4}(?:[ \u00a0.\-]? ?\d{4})?(?!\d)/g
  ) || []
  const nonYear = groupedMatches.filter((m) => !/^(19|20)\d{2}/.test(m))
  // Prefer a full 16-digit match over a truncated 12-digit one.
  const best = nonYear.find((m) => m.replace(/[^0-9]/g, '').length === 16) || nonYear[0]
  if (best) return best.replace(/[^0-9]/g, '')

  // 2) Long compact digit runs (UMID / SSS / postal style).
  const digitRuns = text.match(/\b\d{8,16}\b/g) || []
  const nonDates = digitRuns.filter((d) => !/^(19|20)\d{6,14}$/.test(d))
  if (nonDates.length > 0) return nonDates[0]

  // 3) Candidate tokens: alphanumeric runs of length >= 3, optionally hyphenated.
  const candidates = text.match(/[A-Z0-9][A-Z0-9-]{2,30}/g) || []
  if (candidates.length === 0) return null

  // Heuristics: prefer a token containing both letters and digits that isn't
  // a pure date, pure words-only, or a year.
  const clean = candidates.filter((c) => {
    const t = c.replace(/-/g, '')
    const hasLetter = /[A-Za-z]/.test(t)
    const hasDigit = /\d/.test(t)
    const notYear = !/^(19|20)\d{2}$/.test(t)
    return (hasLetter || hasDigit) && (hasLetter && hasDigit || t.length >= 8) && notYear
  })

  const scored = clean
    .map((c) => {
      let score = 0
      if (/^(no|no\.|number|number\.|id|i\.d\.|reg|reg\.|registration)/i.test(c)) score += 3
      if (/^[A-Z]{1,3}[0-9]/.test(c)) score += 2 // NG-1234 style
      if (c.length >= 8) score += 1
      if (c.length <= 32) score += 1
      return { c, score }
    })
    .sort((a, b) => b.score - a.score)

  return scored.length > 0 ? scored[0].c : (clean.length > 0 ? clean[0] : null)
}

/**
 * Convenience: run the full facial + OCR verification in one shot for an URL
 * or File based image, used mainly by tests / non-interactive flows.
 */
export async function runFaceVerification({ selfie, idPhoto }) {
  const selfieResult = await detectFace(selfie)
  const idResult = await detectFace(idPhoto)
  if (!selfieResult.face_detected || !idResult.face_detected) {
    const failure_reason = [
      !selfieResult.face_detected ? selfieResult.reason : null,
      !idResult.face_detected ? idResult.reason : null,
    ]
      .filter(Boolean)
      .join(' ')
    return {
      face_detected: selfieResult.face_detected && idResult.face_detected,
      face_match: false,
      face_similarity: 0,
      failure_reason: failure_reason || null,
    }
  }

  const match = compareFaces(selfieResult.descriptor, idResult.descriptor)
  return {
    face_detected: true,
    face_match: match.face_match,
    face_similarity: match.face_similarity,
    failure_reason: match.face_match ? null : 'The face in your selfie does not match the face in your submitted ID photo.',
  }
}

export const tessaphore = {
  getWorker: getTesseractWorker,
  isModelsLoaded: () => modelsLoaded,
}
