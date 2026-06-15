/**
 * Shared composable for image path optimization (PNG → WebP).
 *
 * Used by HeroSection, AppFeature, and ProductShowcase to avoid
 * duplicating the same toWebP / toWebPSmall helpers.
 */
export function useImageOptimization() {
    const WEBP_DIR = '/images/app-screenshots/webp/';

    /** Convert a PNG path under /images/app-screenshots/ → full-size WebP */
    function toWebP(pngPath: string): string {
        return pngPath
            .replace('/images/app-screenshots/', WEBP_DIR)
            .replace('.png', '.webp');
    }

    /** Convert a PNG path under /images/app-screenshots/ → 640 px-wide WebP */
    function toWebPSmall(pngPath: string): string {
        return pngPath
            .replace('/images/app-screenshots/', WEBP_DIR)
            .replace('.png', '-sm.webp');
    }

    return { toWebP, toWebPSmall };
}
