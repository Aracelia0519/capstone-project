<template>
  <div class="space-y-5">
    <!-- Control bar: choose capture mode -->
    <div class="flex items-center justify-between gap-3 flex-wrap">
      <div class="flex gap-2">
        <button
          type="button"
          :class="['mode-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-all cursor-pointer',
                   mode === 'webcam' ? 'bg-sky-600 text-white border-sky-500 shadow' : 'bg-white text-gray-600 border-gray-300 hover:border-sky-400']"
          @click="switchMode('webcam')"
        >
          Webcam
        </button>
        <button
          type="button"
          :class="['mode-btn px-3 py-1.5 text-xs font-semibold rounded-lg border transition-all cursor-pointer',
                   mode === 'upload' ? 'bg-sky-600 text-white border-sky-500 shadow' : 'bg-white text-gray-600 border-gray-300 hover:border-sky-400']"
          @click="switchMode('upload')"
        >
          Upload
        </button>
      </div>
      <span v-if="statusText" class="text-xs text-gray-500">{{ statusText }}</span>
    </div>

    <!-- Webcam capture pane -->
    <div v-if="mode === 'webcam'" class="space-y-3">
      <div class="relative rounded-xl overflow-hidden bg-gray-900 border border-gray-300">
        <video
          ref="video"
          autoplay
          muted
          playsinline
          class="w-full h-64 object-cover"
        ></video>
        <div v-if="!streamActive" class="absolute inset-0 flex flex-col items-center justify-center text-gray-300 gap-2">
          <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <span class="text-xs">Camera not started</span>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          type="button"
          class="camera-btn flex-1 px-3 py-2 text-sm font-semibold rounded-lg text-white bg-sky-600 hover:bg-sky-700 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="cameraLoading"
          @click="onCameraButton"
        >
          {{ streamActive ? 'Switch Camera' : 'Start Camera' }}
        </button>
        <button
          type="button"
          class="px-4 py-2 text-sm font-bold rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
          :disabled="!streamActive"
          @click="captureFromCamera"
        >
          Capture
        </button>
      </div>
    </div>

    <!-- Upload fallback pane -->
    <div v-if="mode === 'upload'" class="space-y-2">
      <div
        class="relative rounded-xl border-2 border-dashed p-4 text-center cursor-pointer"
        :class="selfiePreview ? 'border-emerald-300 bg-emerald-50' : 'border-gray-300 bg-white hover:bg-gray-50'"
        @dragover.prevent
        @drop.prevent="onDrop"
        @click="$refs.fileInput.click()"
      >
        <input
          ref="fileInput"
          type="file"
          accept="image/*"
          class="hidden"
          @change="onFileChange"
        />
        <p v-if="!selfiePreview" class="text-sm text-gray-500">Drop a selfie here or <span class="text-sky-600 font-medium">browse</span></p>
        <p v-else class="text-emerald-600 text-sm font-medium">✓ Selfie selected — replace by clicking</p>
      </div>
    </div>

    <!-- Preview + verify -->
    <div v-if="selfiePreview" class="space-y-3">
      <img :src="selfiePreview" alt="Selfie preview" class="max-h-48 mx-auto rounded-lg border border-gray-300 shadow-sm" />
      <button
        type="button"
        class="w-full px-4 py-2.5 text-sm font-semibold rounded-lg text-white transition cursor-pointer disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2"
        :class="verifying ? 'bg-gray-400' : 'bg-linear-to-br from-blue-500 to-purple-600 hover:opacity-90'"
        :disabled="verifying || !canVerify"
        @click="runVerification"
      >
        <svg v-if="verifying" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ verifying ? 'Verifying…' : 'Run Face & ID Verification' }}
      </button>
      <p v-if="verifyHint" class="text-xs text-gray-500">{{ verifyHint }}</p>
    </div>

    <!-- Results summary -->
    <div v-if="result" class="rounded-xl border p-4 space-y-3"
         :class="result.credentials_matched ? 'border-emerald-300 bg-emerald-50' : (result.face_detected === false ? 'border-amber-300 bg-amber-50' : 'border-red-300 bg-red-50')">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-2">
          <svg class="w-5 h-5" :class="result.credentials_matched ? 'text-emerald-500' : 'text-red-500'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <span class="text-sm font-semibold" :class="result.credentials_matched ? 'text-emerald-700' : 'text-red-700'">
            {{ result.credentials_matched ? 'Credentials matched' : 'Verification failed' }}
          </span>
        </div>
        <span class="text-xs text-gray-500">{{ result.face_similarity != null ? `${Math.round(result.face_similarity * 100)}% face match` : '' }}</span>
      </div>

      <div class="grid grid-cols-2 gap-2 text-xs">
        <div class="flex items-center gap-1.5">
          <StatusDot :ok="result.face_detected === true" /> Face detected:
          <span class="font-medium" :class="result.face_detected === true ? 'text-emerald-600' : 'text-red-600'">
            {{ result.face_detected === true ? 'Yes' : 'No' }}
          </span>
        </div>
        <div class="flex items-center gap-1.5">
          <StatusDot :ok="result.face_match === true" /> Face match:
          <span class="font-medium" :class="result.face_match === true ? 'text-emerald-600' : 'text-red-600'">
            {{ result.face_match === true ? 'Yes' : 'No' }}
          </span>
        </div>
        <div class="flex items-center gap-1.5">
          <StatusDot :ok="result.name_match === true" /> Name:
          <span class="font-medium" :class="result.name_match === true ? 'text-emerald-600' : 'text-red-600'">
            {{ result.name_match === true ? 'Matched' : 'Not matched' }}
          </span>
        </div>
        <div class="flex items-center gap-1.5">
          <StatusDot :ok="result.id_number_match === true" /> ID number:
          <span class="font-medium" :class="result.id_number_match === true ? 'text-emerald-600' : 'text-red-600'">
            {{ result.id_number_match === true ? 'Matched' : 'Unverified' }}
          </span>
        </div>
      </div>

      <div v-if="result.failure_reason" class="text-xs text-red-600 bg-red-100/70 rounded p-2">{{ result.failure_reason }}</div>

      <!-- Manual review request: shown when at least ONE check passed but not all -->
      <label
        v-if="result.any_match === true && !result.credentials_matched"
        class="flex items-start gap-2 p-3 rounded-lg border border-amber-300 bg-amber-50 cursor-pointer"
      >
        <input
          type="checkbox"
          v-model="manualReviewRequested"
          class="mt-0.5 w-4 h-4 text-amber-600 border-amber-300 rounded focus:ring-amber-500"
        />
        <span class="text-xs text-amber-800 leading-relaxed">
          <span class="font-semibold">Submit for manual review by admin</span> — at least one automatic check
          (face / name / ID number) passed, but not all. Your details will be sent for the admin to manually
          review your requirements and verification before deciding.
        </span>
      </label>

      <!-- All checks failed (verification actually ran): admin will not be able to approve -->
      <div
        v-else-if="result.any_match === false"
        class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded p-2"
      >
        All automatic checks (face, name, ID number) failed. An admin will not be able to approve your account
        until at least one check passes. Please retake a clearer photo of your ID and try again.
      </div>

      <div v-if="result.ocr_text" class="text-[11px] text-slate-500 bg-white/80 border border-slate-200 rounded p-2 max-h-24 overflow-auto whitespace-pre-wrap">
        <span class="font-semibold text-slate-600">OCR read:</span> {{ result.ocr_text }}
      </div>
    </div>

    <!-- ID must-be-image notice -->
    <div v-if="idNotImage" class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded p-3">
      {{ idNotImage }}
    </div>
  </div>
</template>

<script setup>
/**
 * FaceVerificationStep.vue
 * ---------------------------------------------------------------------------
 * Reusable selfie-capture + automatic Identity Verification step.
 *
 * Usage:
 *   <FaceVerificationStep
 *     :id-photo="fileOrIdPhotoUrl"   // the uploaded valid-ID / business document
 *     :id-number="typedIdNumber"     // ID number the user typed
 *     :first-name="user.first_name"
 *     :last-name="user.last_name"
 *     v-model="selfieResultField"    // object: { selfiePhoto, face_detected, face_match, face_similarity,
 *                                    //           ocr_text, ocr_id_number, name_match, id_number_match,
 *                                    //           credentials_matched, failure_reason }
 *   />
 *
 * The parent appends the emitted fields to its FormData on submit:
 *   selfie_photo, face_detected, face_match, face_similarity, ocr_text, ocr_id_number,
 *   typed_id_number (already sent by the parent).
 *
 * If the ID photo is a PDF (allowed for clients), OCR cannot run - the step shows a
 * clear notice and returns verification with reasons instead of crashing.
 */
import { ref, computed, h, onMounted, onBeforeUnmount } from 'vue'
import {
  namesMatch,
  idNumbersMatch,
  loadFaceApiModels,
  toImageElement,
  detectFace,
  compareFaces,
  runOcr,
} from '@/utils/faceVerification'

const props = defineProps({
  idPhoto: { type: [File, String, Object], default: null },
  idNumber: { type: String, default: '' },
  firstName: { type: String, default: '' },
  lastName: { type: String, default: '' },
  idType: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const video = ref(null)
const fileInput = ref(null)
const mode = ref('webcam')
const selfieFile = ref(null)
const selfiePreview = ref('')
const stream = ref(null)
const streamActive = ref(false)
const cameraLoading = ref(false)

const verifying = ref(false)
const result = ref(null)
const idNotImage = ref('')
const manualReviewRequested = ref(false)

// Tiny inline status dot so the parent doesn't need to import anything extra.
const StatusDot = {
  // Rendered via a small inline span in runVerification's result summary.
}

/* ---------------------------------------------------------------------------
 * Mode switching
 * ------------------------------------------------------------------------- */
function switchMode(next) {
  mode.value = next
  if (next === 'upload') stopCamera()
}

/* ---------------------------------------------------------------------------
 * Webcam
 * ------------------------------------------------------------------------- */
// The camera is addressed by its real device (from enumerateDevices) so an
// external webcam works like any other device. `facingMode` is only used as a
// last resort on devices with no enumerable list (rare).
let facingMode = 'user'
const videoDevices = ref([])
const deviceIndex = ref(0)
let starting = false
// True once the user picked a camera from the dropdown - after that we never
// silently re-point at a different device.
let userPickedCamera = false

// Virtual cameras (e.g. OBS Virtual Camera) only deliver frames while their
// host app is running, so they are skipped when auto-selecting a default.
function isVirtualDevice(dev) {
  return /virtual|obs|ndi|droidcam/i.test((dev && dev.label) || '')
}

function deviceLabel(idx) {
  const d = videoDevices.value[idx]
  return (d && d.label) || `Camera ${idx + 1}`
}

async function refreshVideoDevices() {
  if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices) return
  try {
    const devs = await navigator.mediaDevices.enumerateDevices()
    videoDevices.value = devs.filter((d) => d.kind === 'videoinput')

    if (!userPickedCamera) {
      // Prefer a real camera over a virtual one (OBS Virtual Camera blocks
      // the stream whenever OBS is not running).
      const realIdx = videoDevices.value.findIndex((d) => !isVirtualDevice(d))
      if (realIdx >= 0) deviceIndex.value = realIdx
    } else if (deviceIndex.value >= videoDevices.value.length) {
      deviceIndex.value = Math.max(0, videoDevices.value.length - 1)
    }
  } catch (e) {
    videoDevices.value = []
  }
}

// Constraint strategies, tried in order until one produces a working stream.
// An external webcam on Windows can reject one style of request while another
// works fine, so on failure we fall through: real device id -> plain default
// (the plain `video: true` style that used to work) -> facing mode.
function cameraStrategies() {
  const dev = videoDevices.value[deviceIndex.value]
  return [
    dev && dev.deviceId ? { deviceId: { ideal: dev.deviceId } } : null,
    true,
    { facingMode: { ideal: facingMode } },
  ]
}

function describeCameraError(e) {
  const name = (e && (e.name || e.message)) || 'UnknownError'
  if (/NotAllowed|Permission|Security/i.test(name)) {
    return 'Camera permission was denied. Click the camera icon in the address bar, allow camera access for this site, then press Start Camera again (or use Upload).'
  }
  if (/NotFound|DevicesNotFound/i.test(name)) {
    return 'No camera was detected on this device. Connect a webcam or use the Upload option.'
  }
  if (/NotReadable|TrackStart|in use|busy/i.test(name)) {
    return 'The camera could not be accessed after several automatic retries. Close any other tab or app using the camera, then press Start Camera again. If it still fails, fully refresh this page once (Ctrl+F5) - Chrome on Windows can keep a webcam locked to the old page session - or use the Upload option instead.'
  }
  if (/Overconstrained/i.test(name)) {
    return 'Your camera could not provide the requested mode. Press Switch Camera / Start Camera again (or use Upload).'
  }
  return 'Could not start the camera (' + name + '). Please allow camera permission or use Upload.'
}

function isRetryableError(e) {
  const name = (e && (e.name || e.message)) || ''
  // NotReadable / AbortError / "in use" usually mean the device was still
  // releasing from a previous stream - waiting and retrying fixes it.
  return /NotReadable|TrackStart|AbortError|in use|busy/i.test(name)
}

function isHardError(e) {
  const name = (e && (e.name || e.message)) || ''
  // Permission / no-device errors will not be fixed by another constraint
  // style, so they fail immediately with a clear message.
  return /NotAllowed|Permission|Security|NotFound|DevicesNotFound/i.test(name)
}

async function startCamera() {
  if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
    cameraLoading.value = false
    idNotImage.value = 'Webcam is not available in this browser. Use the Upload option below.'
    return
  }
  if (starting) return
  starting = true
  cameraLoading.value = true
  stopCamera() // release any existing stream first

  // Give the browser a moment to fully release the previous stream so the
  // device is not reported as "busy" by the next getUserMedia call.
  await new Promise((r) => setTimeout(r, 200))

  if (videoDevices.value.length === 0) await refreshVideoDevices()

  let lastError = null

  const strategies = cameraStrategies()
  for (let s = 0; s < strategies.length; s++) {
    const videoConstraint = strategies[s]
    if (videoConstraint === null) continue

    // For each style, allow one quick retry on transient "busy" errors.
    for (let attempt = 1; attempt <= 2; attempt++) {
      try {
        console.info('[FaceVerificationStep] camera attempt', 'strategy=' + s, 'device=' + deviceLabel(deviceIndex.value))
        const newStream = await navigator.mediaDevices.getUserMedia({
          video: videoConstraint,
          audio: false,
        })
        stream.value = newStream
        if (video.value) {
          video.value.srcObject = newStream
          await video.value.play().catch(() => {})
        }
        streamActive.value = true
        idNotImage.value = ''
        cameraLoading.value = false
        starting = false
        // Labels only appear after permission is granted - refresh so the
        // camera picker dropdown below shows real device names.
        refreshVideoDevices()
        return
      } catch (e) {
        lastError = e
        stopCamera()
        if (isHardError(e)) {
          streamActive.value = false
          idNotImage.value = describeCameraError(e)
          cameraLoading.value = false
          starting = false
          return
        }
        if (!isRetryableError(e) || attempt >= 2) break
        // Retryable: wait a moment, then try this style once more.
        await new Promise((r) => setTimeout(r, 500))
      }
    }
  }

  streamActive.value = false
  const selectedDevice = videoDevices.value[deviceIndex.value]
  const virtualHint = selectedDevice && isVirtualDevice(selectedDevice)
    ? ' The selected camera "' + deviceLabel(deviceIndex.value) + '" is a virtual camera - start the app that provides it (e.g. OBS Studio) or choose your real webcam from the camera picker below.'
    : ''
  idNotImage.value = describeCameraError(lastError) + virtualHint
  cameraLoading.value = false
  starting = false
  // Permission was granted even if the stream failed, so now labels are
  // available - refresh so the camera picker shows real device names.
  refreshVideoDevices()
}

function switchCamera() {
  // With multiple cameras, cycle through the real devices (works with external
  // webcams). With one device, fall back to the old front/back flip (mobile).
  if (videoDevices.value.length > 1) {
    deviceIndex.value = (deviceIndex.value + 1) % videoDevices.value.length
    userPickedCamera = true
  } else {
    facingMode = facingMode === 'user' ? 'environment' : 'user'
  }
  startCamera()
}

function onCameraButton() {
  if (streamActive.value) switchCamera()
  else startCamera()
}

function stopCamera() {
  starting = false
  if (stream.value) {
    stream.value.getTracks().forEach((t) => t.stop())
    stream.value = null
  }
  streamActive.value = false
  if (video.value) video.value.srcObject = null
}

function captureFromCamera() {
  if (!streamActive.value || !video.value) return
  const canvas = document.createElement('canvas')
  canvas.width = video.value.videoWidth || 640
  canvas.height = video.value.videoHeight || 480
  canvas.getContext('2d').drawImage(video.value, 0, 0, canvas.width, canvas.height)
  canvas.toBlob((blob) => {
    if (!blob) return
    if (selfiePreview.value) URL.revokeObjectURL(selfiePreview.value)
    selfieFile.value = new File([blob], 'selfie.jpg', { type: 'image/jpeg' })
    selfiePreview.value = URL.createObjectURL(blob)
    // Stop the camera after a successful capture; verification runs on the file.
    stopCamera()
  }, 'image/jpeg', 0.92)
}

/* ---------------------------------------------------------------------------
 * Upload fallback
 * ------------------------------------------------------------------------- */
function onFileChange(e) {
  const file = e.target.files && e.target.files[0]
  if (file) setSelfie(file)
}

function onDrop(e) {
  const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0]
  if (file) setSelfie(file)
}

function setSelfie(file) {
  if (!file || !/^image\//.test(file.type)) return
  if (selfiePreview.value) URL.revokeObjectURL(selfiePreview.value)
  selfieFile.value = file
  selfiePreview.value = URL.createObjectURL(file)
}

/* ---------------------------------------------------------------------------
 * Verification
 * ------------------------------------------------------------------------- */
const canVerify = computed(() => {
  return Boolean(selfieFile.value && props.idPhoto && props.idNumber)
})

const verifyHint = computed(() => {
  if (verifying.value) return 'Verifying your identity — please wait a few seconds.'
  if (!selfieFile.value) return 'Capture or upload a selfie first.'
  if (!props.idPhoto) return 'Upload your valid ID / business document first.'
  if (!props.idNumber) return 'Type your ID number first.'
  return 'Make sure the selfie and ID photo are clear and well lit.'
})

const statusText = computed(() => {
  if (cameraLoading.value) return 'Starting camera…'
  if (streamActive.value) return 'Camera ready'
  if (mode.value === 'webcam') return 'Camera not started'
  return 'Upload a selfie'
})

async function runVerification() {
  if (!canVerify.value || verifying.value) return
  verifying.value = true
  result.value = null
  try {
    // ID must be an image for OCR + face detection; PDFs are allowed for clients
    // but cannot be verified client-side.
    const idPhoto = props.idPhoto
    const idIsImage = idPhoto instanceof File
      ? /^image\//.test(idPhoto.type)
      : (typeof idPhoto === 'string' ? /\.(jpe?g|png|webp|bmp|gif)$/i.test(idPhoto) : false)

    if (!idIsImage) {
      idNotImage.value = 'Your ID is a PDF / not an image, so automatic OCR and facial comparison cannot run. Please upload an image of your ID to verify face & name automatically, or continue without automatic verification.'
      result.value = {
        credentials_matched: false,
        face_detected: false,
        face_match: false,
        face_similarity: 0,
        name_match: false,
        id_number_match: false,
        any_match: null,
        failure_reason: 'ID is not an image (PDF). Face & OCR verification could not run.',
      }
      emit('update:modelValue', {
        ...result.value,
        selfiePhoto: selfieFile.value,
        manual_review_requested: false,
      })
      return
    }

    await loadFaceApiModels()

    // 1) Face detection on both images.
    const selfieDet = await detectFace(await toImageElement(selfieFile.value))
    const idDet = await detectFace(await toImageElement(idPhoto))

    let face_match = false
    let face_similarity = 0
    if (selfieDet.face_detected && idDet.face_detected) {
      const cmp = compareFaces(selfieDet.descriptor, idDet.descriptor)
      face_match = cmp.face_match
      face_similarity = cmp.face_similarity
    }

    // 2) OCR the ID photo to extract the printed ID number.
    let ocr_text = ''
    let ocr_id_number = null
    try {
      const ocr = await runOcr(idPhoto)
      ocr_text = ocr.ocr_text || ''
      ocr_id_number = ocr.ocr_id_number || null
    } catch (e) {
      ocr_text = ''
    }

    // 3) Compare the OCR name against the typed name, and OCR ID against typed ID.
    //    Only the user's FIRST and LAST name (from the users table) are compared;
    //    middle names / initials found on the ID are ignored.
    const typedName = `${props.firstName} ${props.lastName}`.trim()
    const name_match = ocr_text ? namesMatch(ocr_text, typedName) : false
    const id_number_match = ocr_id_number
      ? idNumbersMatch(ocr_id_number, props.idNumber)
      : false

    const credentials_matched = Boolean(face_match && name_match && id_number_match)
    const failureParts = []
    if (!face_match) failureParts.push('Face match failed.')
    if (!name_match && ocr_text) {
      failureParts.push(`Name on the ID did not match your account name (first name "${props.firstName || '—'}", last name "${props.lastName || '—'}").`)
    }
    if (!id_number_match && ocr_id_number) {
      failureParts.push(`ID number on the ID (${ocr_id_number}) does not match the number you entered (${props.idNumber}).`)
    }
    if (!id_number_match && !ocr_id_number && ocr_text) {
      failureParts.push('ID number could not be recognised on the ID photo.')
    }
    if (!ocr_text) {
      failureParts.push('The ID photo could not be read by OCR. Please upload a clear, well-lit image of your ID (a screenshot or photo, not a PDF).')
    }

    result.value = {
      credentials_matched,
      any_match: Boolean(face_match || name_match || id_number_match),
      face_detected: selfieDet.face_detected && idDet.face_detected,
      face_match,
      face_similarity,
      name_match,
      id_number_match,
      ocr_text,
      ocr_id_number,
      failure_reason: failureParts.length ? failureParts.join(' ') : null,
    }
    // A manual-review request only makes sense when at least one check passed
    // but not everything (fully matched users don't need one; all-failed users
    // cannot be approved by an admin until a check passes).
    if (!result.value.any_match || result.value.credentials_matched) {
      manualReviewRequested.value = false
    }
    emit('update:modelValue', {
      ...result.value,
      selfiePhoto: selfieFile.value,
      manual_review_requested: manualReviewRequested.value,
    })
  } catch (e) {
    result.value = {
      credentials_matched: false,
      any_match: null,
      face_detected: false,
      face_match: false,
      face_similarity: 0,
      name_match: false,
      id_number_match: false,
      failure_reason: 'Verification could not be completed: ' + (e && e.message ? e.message : 'unknown error'),
    }
    emit('update:modelValue', {
      ...result.value,
      selfiePhoto: selfieFile.value,
      manual_review_requested: false,
    })
  } finally {
    verifying.value = false
  }
}

onMounted(() => {
  // Populate the camera picker as soon as the step is shown. Labels appear
  // fully after the first successful camera start / permission grant.
  refreshVideoDevices()
})

onBeforeUnmount(() => stopCamera())
</script>

<style scoped>
</style>
