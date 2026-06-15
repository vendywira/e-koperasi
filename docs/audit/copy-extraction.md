# Batch A — Hardcoded Copy Audit (Indonesian)

> **Status:** In progress (Task #22)
> **Goal:** Catalogue every Indonesian string in the marketing-site components so Batch B can build a typed config and Batch C can rewire components.
> **Scope:** `resources/js/Components/**.vue` and `resources/js/Layouts/SiteLayout.vue` (marketing site only — `Pages/Demo/**` and `Pages/About/**` out of scope).

---

## Conventions

- **Type** = category of copy (`heading`, `body`, `cta`, `badge`, `nav`, `legal`, etc.)
- **Location** = file:line
- **Config key (proposed)** = where this string should live in the new config (PascalCase, dot-notation)
- **Status** = `inline JS` / `inline template` / `window.siteConfig` (already wired) / `prop` (already overridable)

---

## 1. `HeroSection.vue`

| # | Type | Location | String | Config key (proposed) | Status |
|---|------|----------|--------|-----------------------|--------|
| 1.1 | badge | HeroSection.vue:12 | "Dibuat untuk Koperasi Indonesia" | `hero.badge` | inline template |
| 1.2 | heading line 1 | HeroSection.vue:16 | "Kelola Koperasi dalam" | `hero.headingLine1` | inline template |
| 1.3 | heading accent | HeroSection.vue:17 | "Satu Genggaman" (inside `<span class="text-primary-600">`) | `hero.headingAccent` | inline template |
| 1.4 | body | HeroSection.vue:20-23 | "Tunai harian, kasbon, titipan, pinjaman, gaji, hingga tracking lapangan — semuanya digital, real-time, didukung AI.\nMulti-resort, multi-unit, multi-role. Dari PDL di lapangan sampai pimpinan koperasi." | `hero.body` | inline template |
| 1.5 | cta primary | HeroSection.vue:31 | "Mulai Trial Gratis" | `hero.ctaPrimary` | inline template |
| 1.6 | cta secondary | HeroSection.vue:39 | "Lihat Demo Video" | `hero.ctaSecondary` | inline template |
| 1.7 | trust line | HeroSection.vue:47 | "Dipercaya 50+ koperasi di Indonesia · Tanpa kartu kredit · Setup 5 menit" | `hero.trustLine` | inline template |
| 1.8 | mobile pill prefix | HeroSection.vue:62 | "Tersedia di iOS & Android — " | `hero.mobileBadgePrefix` | inline template |
| 1.9 | mobile pill suffix | HeroSection.vue:62 | "Pantau dari mana saja" | `hero.mobileBadge` | inline template |

---

## 2. `SiteHeader.vue`

| # | Type | Location | String | Config key (proposed) | Status |
|---|------|----------|--------|-----------------------|--------|
| 2.1 | nav label | SiteHeader.vue:13 | "Fitur" | `nav.primary.items[0].label` | inline script |
| 2.2 | nav label | SiteHeader.vue:14 | "Untuk Siapa" | `nav.primary.items[1].label` | inline script |
| 2.3 | nav label | SiteHeader.vue:15 | "Harga" | `nav.primary.items[2].label` | inline script |
| 2.4 | nav label | SiteHeader.vue:16 | "Tentang" | `nav.primary.items[3].label` | inline script |
| 2.5 | nav hrefs | SiteHeader.vue:13-16 | "/#fitur", "/#untuk-siapa", "/#harga", "/about" | `nav.primary.items[*].href` | inline script |
| 2.6 | cta secondary | SiteHeader.vue:65 | "Coba Demo" | `nav.ctaSecondary` | inline template |
| 2.7 | cta primary | SiteHeader.vue:71 | "Request Demo" | `nav.ctaPrimary` | inline template |
| 2.8 | aria label | SiteHeader.vue:55,79 | "Switch to light mode" / "Switch to dark mode" | `nav.themeToggleAria.*` (or keep inline — microcopy) | inline template |
| 2.9 | aria label | SiteHeader.vue:87 | "Toggle menu" | keep inline (microcopy) | inline template |

> **Decision:** aria labels for icon-only buttons stay inline (they're a11y microcopy, not content). Only user-visible nav/CTA strings go to config.

---

## 3. `MobileNav.vue`

| # | Type | Location | String | Config key | Status |
|---|------|----------|--------|------------|--------|
| 3.1 | cta | MobileNav.vue:33 | "Request Demo" | reuse `nav.ctaPrimary` (same as 2.7) | inline template |

> Receives `items` as a prop from `SiteHeader` — no extraction needed in this file.

---

## 4. `TrustBar.vue` ✅

Already config-driven (no hardcoded copy — renders logos only).

---

## 5. `FeatureGrid.vue` ✅

Already config-driven (icons + labels from `window.siteConfig.features` — wired previously).

---

## 6. `PersonaCards.vue` ✅

Already config-driven (reads `window.siteConfig.personas` — wired previously).

---

## 7. `HowItWorks.vue`

| # | Type | Location | String | Config key | Status |
|---|------|----------|--------|------------|--------|
| 7.1 | heading | HowItWorks.vue:28 | "Mulai dalam 3 Langkah" | `howItWorks.heading` | inline template |
| 7.2 | step 1 num | HowItWorks.vue:7 | "1" | `howItWorks.steps[0].num` | inline script |
| 7.3 | step 1 title | HowItWorks.vue:7 | "Daftar" | `howItWorks.steps[0].title` | inline script |
| 7.4 | step 1 desc | HowItWorks.vue:8 | "Hubungi tim kami atau coba sandbox demo langsung. Tidak perlu kartu kredit." | `howItWorks.steps[0].desc` | inline script |
| 7.5 | step 2 num/title/desc | HowItWorks.vue:10-12 | "2" / "Setup Data" / "Tim kami membantu migrasi data anggota, pinjaman, dan saldo dari sistem lama Anda." | `howItWorks.steps[1].*` | inline script |
| 7.6 | step 3 num/title/desc | HowItWorks.vue:14-16 | "3" / "Go Live" / "Pelatihan tim, monitoring 1 minggu, dan dukungan penuh selama transisi." | `howItWorks.steps[2].*` | inline script |

---

## 8. `StatGrid.vue` ✅

Already config-driven (reads `window.siteConfig.stats`).

---

## 9. `TestimonialSection.vue` ✅

Already config-driven (reads `window.siteConfig.testimonials` with fallback array — but the fallback duplicates content, see Batch C).

---

## 10. `PricingTable.vue` ✅

Already config-driven (reads `window.siteConfig.pricing`). Keep as-is.

---

## 11. `CtaSection.vue`

| # | Type | Location | String | Config key | Status |
|---|------|----------|--------|------------|--------|
| 11.1 | heading | CtaSection.vue:10 | "Bawa Koperasi Anda ke Era Digital" | `ctaBanner.heading` | inline template |
| 11.2 | body | CtaSection.vue:13 | "Eksplorasi sandbox demo gratis. Tidak perlu kartu kredit. 6 akun role siap pakai." | `ctaBanner.body` | inline template |
| 11.3 | cta primary | CtaSection.vue:20 | "Coba Demo Sekarang" | `ctaBanner.ctaPrimary` | inline template |
| 11.4 | cta secondary | CtaSection.vue:27 | "Request Demo 1-on-1" | `ctaBanner.ctaSecondary` | inline template |

---

## 12. `FaqSection.vue`

| # | Type | Location | String | Config key | Status |
|---|------|----------|--------|------------|--------|
| 12.1 | heading | FaqSection.vue:53 | "Pertanyaan Umum" | `faqs.heading` | inline template |
| 12.2 | faq items | FaqSection.vue:9-33 | 6 hardcoded FAQ entries (q + a) | `faqs.items` | inline script (fallback) |

> **Note:** Component already has prop-driven `items`, but the fallback `defaultFaqs` is hardcoded. In Batch C, replace the fallback with config read so SSR/initial paint works.

---

## 13. `SiteFooter.vue`

| # | Type | Location | String | Config key | Status |
|---|------|----------|--------|------------|--------|
| 13.1 | tagline | SiteFooter.vue:44 | "Platform digital untuk koperasi Indonesia." | `footer.tagline` | inline template |
| 13.2 | section "Produk" | SiteFooter.vue:6 | "Produk" | `footer.sections[0].title` | inline script |
| 13.3 | section links (4) | SiteFooter.vue:7-12 | "Fitur", "Harga", "Coba Demo", "Roadmap" | `footer.sections[0].links[*].label` | inline script |
| 13.4 | section "Perusahaan" | SiteFooter.vue:15 | "Perusahaan" | `footer.sections[1].title` | inline script |
| 13.5 | section links (3) | SiteFooter.vue:16-21 | "Tentang Kami", "Cerita e-Koperasi", "Kontak" | `footer.sections[1].links[*].label` | inline script |
| 13.6 | email href | SiteFooter.vue:19 | "mailto:halo@e-koperasi.com" | `footer.contactEmail` | inline script |
| 13.7 | section "Legal" | SiteFooter.vue:23 | "Legal" | `footer.sections[2].title` | inline script |
| 13.8 | section links (2) | SiteFooter.vue:24-27 | "Kebijakan Privasi", "Syarat & Ketentuan" | `footer.sections[2].links[*].label` | inline script |
| 13.9 | copyright | SiteFooter.vue:65 | "&copy; 2026 e-Koperasi. Dibuat di Tabanan, Bali." (year is dynamic) | `footer.copyright` (template w/ `{{ year }}`) | inline template |
| 13.10 | location suffix | SiteFooter.vue:65 | "Dibuat di Tabanan, Bali." | `footer.madeIn` | inline template |
| 13.11 | tagline right | SiteFooter.vue:68 | "Untuk Koperasi Indonesia" | `footer.taglineRight` | inline template |

---

## Summary

| Category | Count | Files affected |
|----------|------:|----------------|
| **Hardcoded inline (script)** | 24 | SiteHeader, HowItWorks, SiteFooter |
| **Hardcoded inline (template)** | 18 | HeroSection, SiteHeader, CtaSection, FaqSection, SiteFooter, MobileNav |
| **Already config-driven** | 5 components | TrustBar, FeatureGrid, PersonaCards, StatGrid, TestimonialSection, PricingTable |
| **Config-driven with hardcoded fallback** | 2 | FaqSection, TestimonialSection — keep fallback but source from config first |
| **Aria/alt microcopy (keep inline)** | 5+ | Theme toggle aria, logo alt, menu toggle aria, etc. |

**Total user-facing copy strings to extract:** ~42 (24 script + 18 template).

---

## Decisions for Batch B (proposed config shape)

```ts
interface SiteConfig {
  hero: {
    badge: string;
    headingLine1: string;
    headingAccent: string;
    body: string;
    ctaPrimary: string;
    ctaSecondary: string;
    trustLine: string;
    mobileBadgePrefix: string;
    mobileBadge: string;
  };
  nav: {
    primary: { items: { label: string; href: string }[] };
    ctaSecondary: string;
    ctaPrimary: string;
  };
  howItWorks: {
    heading: string;
    steps: { num: string; title: string; desc: string }[];
  };
  ctaBanner: {
    heading: string;
    body: string;
    ctaPrimary: string;
    ctaSecondary: string;
  };
  faqs: {
    heading: string;
    items: { q: string; a: string }[];
  };
  footer: {
    tagline: string;
    taglineRight: string;
    contactEmail: string;
    copyright: string;     // template with {{ year }}
    madeIn: string;
    sections: { title: string; links: { label: string; href: string }[] }[];
  };
  // Already-existing sections (unchanged)
  features: ...;
  personas: ...;
  stats: ...;
  testimonials: ...;
  pricing: ...;
  trustLogos: ...;
}
```

**File location (proposed):** `content/site.json` — consumed by Inertia `HandleInertiaRequests` and exposed as `window.siteConfig` (preserves current hydration pattern) **and** injected into Inertia props (so SSR/initial paint can read it without `typeof window` guards).

---

## Risks / open questions

1. **Demo page** (`/demo`) and **About page** also have copy. Out of scope for this batch per task description, but flagging for a follow-up batch.
2. **Inertia prop injection** requires a small server-side change to `HandleInertiaRequests`. Need to confirm the middleware path before Batch C.
3. **Pricing/Feature data shape** already lives in JSON-like structures — verify they match the `SiteConfig` interface above so we don't double-type.

---

## Sign-off

- [x] All 13 component files audited
- [x] Each string mapped to a config key
- [x] Already-wired components confirmed
- [x] Proposed config shape drafted for Batch B review
