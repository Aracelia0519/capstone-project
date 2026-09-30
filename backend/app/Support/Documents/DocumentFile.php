<?php

namespace App\Support\Documents;

/**
 * Absolute URLs for uploaded business documents.
 *
 * `Storage::url()` returns a root-relative path such as `/storage/supplier_verification/x.png`.
 * That is correct only when the browser is served by the same host as `public/`. In
 * development the API runs on :8000 while Vite serves the app on :5173, and the
 * Vite dev server answers an unknown `/storage/...` with the SPA fallback: HTTP 200,
 * Content-Type text/html. An `<img>` given that URL fails silently -- no broken-image
 * icon, no console error, just a document that never appears.
 *
 * AdminUserController already worked around this on the frontend by rebuilding the
 * URL from the axios base URL. Doing it here means the payload is correct for every
 * consumer instead of only the screens that remembered to patch it.
 */
class DocumentFile
{
    /**
     * An absolute, browser-reachable URL for a stored document.
     *
     * Request-aware: `url()` derives the host from the incoming request, so this
     * returns http://localhost:8000/... during a call to the API regardless of
     * what APP_URL says -- which matters, because APP_URL here is
     * http://localhost with no port while the API listens on :8000.
     *
     * Outside a request (queue job, console) there is no host to ask for, so it
     * falls back to APP_URL. `runningInConsole()` rather than `bound('request')`:
     * a console command still has a request instance bound, and using it there
     * would produce a URL on the wrong port.
     */
    public static function url(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $relative = 'storage/' . ltrim($path, '/');

        return app()->runningInConsole()
            ? rtrim(config('app.url'), '/') . '/' . $relative
            : url($relative);
    }
}
