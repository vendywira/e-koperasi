/**
 * Mendapatkan CSRF token dari <meta name="csrf-token"> tag.
 * Tag ini di-render oleh Blade di app.blade.php dan selalu tersedia
 * di Inertia SPA. JANGAN gunakan cookie XSRF-TOKEN untuk X-CSRF-TOKEN
 * karena cookie terenkripsi, raw token ada di meta tag.
 */
export function csrfToken(): string {
    return document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';
}
