// Types for form field definitions
export type FieldType =
    | 'text'
    | 'textarea'
    | 'image'
    | 'video'
    | 'repeater'
    | 'group'
    | 'link'
    | 'cta'
    | 'icon-select'
    | 'boolean';

export interface FormField {
    key: string;
    type: FieldType;
    label: string;
    props?: Record<string, any>;
    children?: FormField[];
    fields?: FormField[]; // For group/repeater
}

export interface SectionSchema {
    section: string;
    label: string;
    icon: string;
    description?: string;
    fields: FormField[];
}

// ======== SCHEMA DEFINITIONS ========

export const sectionSchemas: SectionSchema[] = [
    // ---- Brand ----
    {
        section: 'brand',
        label: 'Brand & Identitas',
        icon: '🏷️',
        description: 'Informasi dasar brand dan identitas perusahaan — termasuk logo dan favicon',
        fields: [
            { key: 'name', type: 'text', label: 'Nama Brand', props: { placeholder: 'e-Koperasi' } },
            { key: 'tagline', type: 'text', label: 'Tagline', props: { placeholder: 'Platform Digital Koperasi Indonesia' } },
            { key: 'description', type: 'textarea', label: 'Deskripsi', props: { rows: 3, placeholder: 'Deskripsi singkat tentang brand...' } },
            { key: 'logo', type: 'image', label: 'Logo Brand (light mode)' },
            { key: 'favicon', type: 'text', label: 'Favicon URL', props: { placeholder: '/favicon.ico', helpText: 'URL icon tab browser (16x16 atau 32x32)' } },
        ],
    },

    // ---- Navigasi ----
    {
        section: 'nav',
        label: 'Navigasi',
        icon: '🧭',
        description: 'Menu navigasi utama website',
        fields: [
            {
                key: 'items',
                type: 'repeater',
                label: 'Menu Navigasi',
                props: { addLabel: 'Tambah Menu', titleKey: 'label' },
                fields: [
                    { key: 'label', type: 'text', label: 'Label Menu', props: { placeholder: 'Produk' } },
                    { key: 'href', type: 'text', label: 'URL', props: { placeholder: '/#produk' } },
                ],
            },
        ],
    },

    // ---- Footer ----
    {
        section: 'footer',
        label: 'Footer',
        icon: '📋',
        description: 'Footer website — kolom link, deskripsi, dan copyright',
        fields: [
            { key: 'description', type: 'textarea', label: 'Deskripsi Footer', props: { rows: 2 } },
            { key: 'tagline', type: 'text', label: 'Tagline Footer', props: { placeholder: 'Untuk Koperasi Indonesia' } },
            {
                key: 'columns',
                type: 'repeater',
                label: 'Kolom Link',
                props: { addLabel: 'Tambah Kolom', titleKey: 'title' },
                fields: [
                    { key: 'title', type: 'text', label: 'Judul Kolom', props: { placeholder: 'Produk' } },
                    {
                        key: 'links',
                        type: 'repeater',
                        label: 'Link',
                        props: { addLabel: 'Tambah Link', titleKey: 'label' },
                        fields: [
                            { key: 'label', type: 'text', label: 'Label', props: { placeholder: 'Produk Keuangan' } },
                            { key: 'href', type: 'text', label: 'URL', props: { placeholder: '/#produk' } },
                        ],
                    },
                ],
            },
            { key: 'copyright', type: 'text', label: 'Teks Copyright', props: { placeholder: '© 2026 e-Koperasi' } },
        ],
    },

    // ---- Hero ----
    {
        section: 'hero',
        label: 'Hero Section',
        icon: '🎯',
        description: 'Bagian hero utama di halaman depan',
        fields: [
            { key: 'badge', type: 'text', label: 'Badge', props: { placeholder: 'Dipercaya 50+ Koperasi' } },
            { key: 'headline', type: 'textarea', label: 'Headline Utama', props: { rows: 2, placeholder: 'Judul besar hero...' } },
            { key: 'subheadline', type: 'textarea', label: 'Sub-headline', props: { rows: 3, placeholder: 'Deskripsi pendukung...' } },
            {
                key: 'primary_cta',
                type: 'cta',
                label: 'Tombol Utama (CTA)',
                props: { variant: 'primary', labelPlaceholder: 'Coba Demo Gratis', hrefPlaceholder: '/demo' },
            },
            {
                key: 'secondary_cta',
                type: 'cta',
                label: 'Tombol Sekunder',
                props: { variant: 'secondary', labelPlaceholder: 'Lihat Produk', hrefPlaceholder: '/#produk' },
            },
            { key: 'floating_badge', type: 'text', label: 'Floating Badge', props: { placeholder: 'UU PDP Compliant' } },
            {
                key: 'carousel_slides',
                type: 'repeater',
                label: 'Carousel Screenshots',
                props: { addLabel: 'Tambah Slide', titleKey: 'alt' },
                fields: [
                    { key: 'src', type: 'image', label: 'Gambar (WebP)', props: { helpText: 'Gambar screenshot ukuran penuh, format WebP' } },
                    { key: 'src_small', type: 'image', label: 'Gambar Kecil (WebP)', props: { helpText: '640px-wide version untuk mobile' } },
                    { key: 'alt', type: 'text', label: 'Alt Text', props: { placeholder: 'e-Koperasi App - Dashboard utama' } },
                ],
            },
        ],
    },

    // ---- Trust Bar ----
    {
        section: 'trust_bar',
        label: 'Trust Bar',
        icon: '🤝',
        description: 'Bar kepercayaan — menampilkan logo partner atau teks kepercayaan',
        fields: [
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Dipercaya oleh koperasi di berbagai sektor' } },
            {
                key: 'partners',
                type: 'repeater',
                label: 'Logo Partner',
                props: { addLabel: 'Tambah Partner', titleKey: 'name', emptyLabel: 'Belum ada logo partner' },
                fields: [
                    { key: 'name', type: 'text', label: 'Nama Partner', props: { placeholder: 'KSU Tabanan Jaya' } },
                    { key: 'src', type: 'image', label: 'Logo Partner' },
                ],
            },
        ],
    },

    // ---- Statistik ----
    {
        section: 'stats',
        label: 'Statistik',
        icon: '📊',
        description: 'Angka-angka statistik untuk menampilkan kredibilitas',
        fields: [
            {
                key: 'items',
                type: 'repeater',
                label: 'Item Statistik',
                props: { addLabel: 'Tambah Statistik', emptyLabel: 'Belum ada data statistik', titleKey: 'label' },
                fields: [
                    { key: 'value', type: 'text', label: 'Nilai', props: { placeholder: '50+' } },
                    { key: 'label', type: 'text', label: 'Label', props: { placeholder: 'Koperasi Aktif' } },
                ],
            },
        ],
    },

    // ---- Persona Cards ----
    {
        section: 'personas',
        label: 'Persona Cards',
        icon: '👤',
        description: 'Kartu persona untuk setiap role pengguna',
        fields: [
            {
                key: 'items',
                type: 'repeater',
                label: 'Persona',
                props: { addLabel: 'Tambah Persona', titleKey: 'title' },
                fields: [
                    { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Untuk Ketua & Admin' } },
                    { key: 'subtitle', type: 'text', label: 'Subjudul', props: { placeholder: 'Pemimpin koperasi' } },
                    { key: 'description', type: 'textarea', label: 'Deskripsi', props: { rows: 3 } },
                    { key: 'icon', type: 'icon-select', label: 'Icon' },
                ],
            },
        ],
    },

    // ---- Produk ----
    {
        section: 'products',
        label: 'Produk',
        icon: '📦',
        description: 'Showcase produk — highlights, tabungan, dan pinjaman',
        fields: [
            { key: 'youtube_url', type: 'text', label: 'URL Trailer Video', props: { placeholder: 'https://www.youtube.com/watch?v=...', helpText: 'Tautan video trailer produk YouTube' } },
            {
                key: 'highlights',
                type: 'repeater',
                label: 'Highlights',
                props: { addLabel: 'Tambah Highlight', titleKey: 'label' },
                fields: [
                    { key: 'value', type: 'text', label: 'Nilai', props: { placeholder: 'Enkripsi AES-256' } },
                    { key: 'label', type: 'text', label: 'Label', props: { placeholder: 'Keamanan Data' } },
                    { key: 'icon', type: 'icon-select', label: 'Icon' },
                ],
            },
            {
                key: 'saving',
                type: 'repeater',
                label: 'Tabungan / Simpanan',
                props: { addLabel: 'Tambah Produk Tabungan', titleKey: 'name' },
                fields: [
                    { key: 'name', type: 'text', label: 'Nama Produk', props: { placeholder: 'Titipan Harian' } },
                    { key: 'tagline', type: 'text', label: 'Tagline', props: { placeholder: 'Catatan titipan PDL real-time' } },
                    { key: 'icon', type: 'icon-select', label: 'Icon' },
                    { key: 'color', type: 'text', label: 'Warna', props: { placeholder: 'amber, blue, purple, green' } },
                    { key: 'screenshot', type: 'image', label: 'Screenshot' },
                    {
                        key: 'benefits',
                        type: 'repeater',
                        label: 'Manfaat',
                        props: { addLabel: 'Tambah Manfaat' },
                        fields: [
                            { key: 'benefit', type: 'text', label: 'Manfaat', props: { placeholder: 'Input titipan harian dari PDL' } },
                        ],
                    },
                ],
            },
            {
                key: 'loan',
                type: 'repeater',
                label: 'Pinjaman',
                props: { addLabel: 'Tambah Produk Pinjaman', titleKey: 'name' },
                fields: [
                    { key: 'name', type: 'text', label: 'Nama Produk', props: { placeholder: 'Pinjaman Harian' } },
                    { key: 'description', type: 'textarea', label: 'Deskripsi', props: { rows: 2 } },
                    { key: 'badge', type: 'text', label: 'Badge', props: { placeholder: 'Harian' } },
                    { key: 'icon', type: 'icon-select', label: 'Icon' },
                    { key: 'accent', type: 'text', label: 'Warna Aksen', props: { placeholder: 'primary, amber, violet' } },
                    { key: 'screenshot', type: 'image', label: 'Screenshot' },
                    { key: 'rate', type: 'text', label: 'Rate / Bunga', props: { placeholder: '20%' } },
                    { key: 'tenor', type: 'text', label: 'Tenor', props: { placeholder: '14–60 hari' } },
                    { key: 'approval', type: 'text', label: 'Waktu Approval', props: { placeholder: '1×24 jam' } },
                    {
                        key: 'benefits',
                        type: 'repeater',
                        label: 'Manfaat',
                        props: { addLabel: 'Tambah Manfaat' },
                        fields: [
                            { key: 'benefit', type: 'text', label: 'Manfaat', props: { placeholder: 'Proses approval cepat' } },
                        ],
                    },
                ],
            },
        ],
    },

    // ---- Fitur ----
    {
        section: 'features',
        label: 'Fitur',
        icon: '⚡',
        description: 'Grid fitur-fitur utama produk',
        fields: [
            {
                key: 'items',
                type: 'repeater',
                label: 'Fitur',
                props: { addLabel: 'Tambah Fitur', titleKey: 'title' },
                fields: [
                    { key: 'title', type: 'text', label: 'Judul Fitur', props: { placeholder: 'Manajemen Tunai Harian' } },
                    { key: 'description', type: 'textarea', label: 'Deskripsi', props: { rows: 2 } },
                    { key: 'icon', type: 'icon-select', label: 'Icon' },
                ],
            },
        ],
    },

    // ---- Cara Kerja ----
    {
        section: 'how_it_works',
        label: 'Cara Kerja',
        icon: '🔧',
        description: 'Langkah-langkah cara kerja / proses onboarding',
        fields: [
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Cara Kerja' } },
            { key: 'subtitle', type: 'text', label: 'Subjudul', props: { placeholder: 'Dari setup hingga operasional...' } },
            {
                key: 'steps',
                type: 'repeater',
                label: 'Langkah',
                props: { addLabel: 'Tambah Langkah', titleKey: 'title' },
                fields: [
                    { key: 'step', type: 'text', label: 'Nomor Urut', props: { placeholder: '1', helpText: 'Urutan langkah (1, 2, 3, ...)' } },
                    { key: 'title', type: 'text', label: 'Judul Langkah', props: { placeholder: 'Konsultasi & Setup' } },
                    { key: 'description', type: 'textarea', label: 'Deskripsi', props: { rows: 2 } },
                ],
            },
        ],
    },

    // ---- Harga ----
    {
        section: 'pricing',
        label: 'Harga',
        icon: '💰',
        description: 'Tabel harga / paket berlangganan',
        fields: [
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Harga Sederhana & Transparan' } },
            { key: 'subtitle', type: 'text', label: 'Subjudul', props: { placeholder: 'Bayar bulanan. Tanpa biaya setup...' } },
            {
                key: 'tiers',
                type: 'group',
                label: 'Paket Harga',
                fields: [
                    {
                        key: 'starter',
                        type: 'group',
                        label: 'Starter',
                        fields: [
                            { key: 'name', type: 'text', label: 'Nama Paket', props: { placeholder: 'Starter' } },
                            { key: 'price', type: 'text', label: 'Harga', props: { placeholder: 'Rp 499K' } },
                            { key: 'period', type: 'text', label: 'Periode', props: { placeholder: 'per bulan' } },
                            { key: 'highlight', type: 'boolean', label: 'Tandai sebagai Populer', props: { trueLabel: 'Populer', falseLabel: 'Biasa', helpText: 'Tandai paket ini dengan badge "PALING POPULER"' } },
                            { key: 'tagline', type: 'text', label: 'Tagline', props: { placeholder: 'Untuk koperasi kecil...' } },
                            {
                                key: 'features',
                                type: 'repeater',
                                label: 'Fitur',
                                props: { addLabel: 'Tambah Fitur' },
                                fields: [
                                    { key: 'feature', type: 'text', label: 'Fitur', props: { placeholder: 'Hingga 100 anggota' } },
                                ],
                            },
                        ],
                    },
                    {
                        key: 'premium',
                        type: 'group',
                        label: 'Premium',
                        fields: [
                            { key: 'name', type: 'text', label: 'Nama Paket', props: { placeholder: 'Premium' } },
                            { key: 'price', type: 'text', label: 'Harga', props: { placeholder: 'Rp 1.5jt' } },
                            { key: 'period', type: 'text', label: 'Periode', props: { placeholder: 'per bulan' } },
                            { key: 'highlight', type: 'boolean', label: 'Tandai sebagai Populer', props: { trueLabel: 'Populer', falseLabel: 'Biasa', helpText: 'Tandai paket ini dengan badge "PALING POPULER"' } },
                            { key: 'tagline', type: 'text', label: 'Tagline', props: { placeholder: 'Untuk koperasi berkembang...' } },
                            {
                                key: 'features',
                                type: 'repeater',
                                label: 'Fitur',
                                props: { addLabel: 'Tambah Fitur' },
                                fields: [
                                    { key: 'feature', type: 'text', label: 'Fitur', props: { placeholder: 'Hingga 1.000 anggota' } },
                                ],
                            },
                        ],
                    },
                    {
                        key: 'enterprise',
                        type: 'group',
                        label: 'Enterprise',
                        fields: [
                            { key: 'name', type: 'text', label: 'Nama Paket', props: { placeholder: 'Enterprise' } },
                            { key: 'price', type: 'text', label: 'Harga', props: { placeholder: 'Custom' } },
                            { key: 'period', type: 'text', label: 'Periode', props: { placeholder: 'hubungi kami' } },
                            { key: 'highlight', type: 'boolean', label: 'Tandai sebagai Populer', props: { trueLabel: 'Populer', falseLabel: 'Biasa', helpText: 'Tandai paket ini dengan badge "PALING POPULER"' } },
                            { key: 'tagline', type: 'text', label: 'Tagline', props: { placeholder: 'Untuk koperasi besar...' } },
                            {
                                key: 'features',
                                type: 'repeater',
                                label: 'Fitur',
                                props: { addLabel: 'Tambah Fitur' },
                                fields: [
                                    { key: 'feature', type: 'text', label: 'Fitur', props: { placeholder: 'Anggota tidak terbatas' } },
                                ],
                            },
                        ],
                    },
                ],
            },
            { key: 'footer_note', type: 'textarea', label: 'Catatan Footer', props: { rows: 2, placeholder: 'Semua paket include...' } },
        ],
    },

    // ---- Testimoni ----
    {
        section: 'testimonials',
        label: 'Testimoni',
        icon: '💬',
        description: 'Testimonial dari pelanggan',
        fields: [
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Cerita dari Koperasi...' } },
            { key: 'subtitle', type: 'text', label: 'Subjudul', props: { placeholder: 'Bukan testimonial berbayar...' } },
            {
                key: 'items',
                type: 'repeater',
                label: 'Testimoni',
                props: { addLabel: 'Tambah Testimoni', titleKey: 'name' },
                fields: [
                    { key: 'quote', type: 'textarea', label: 'Kutipan', props: { rows: 3, placeholder: 'Isi testimoni...' } },
                    { key: 'name', type: 'text', label: 'Nama', props: { placeholder: 'I Wayan S.' } },
                    { key: 'role', type: 'text', label: 'Role / Jabatan', props: { placeholder: 'Ketua Koperasi, Tabanan' } },
                ],
            },
        ],
    },

    // ---- FAQ ----
    {
        section: 'faqs',
        label: 'FAQ',
        icon: '❓',
        description: 'Pertanyaan yang sering diajukan',
        fields: [
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Pertanyaan yang Sering Diajukan' } },
            {
                key: 'items',
                type: 'repeater',
                label: 'Item FAQ',
                props: { addLabel: 'Tambah FAQ', titleKey: 'q' },
                fields: [
                    { key: 'q', type: 'text', label: 'Pertanyaan', props: { placeholder: 'Berapa lama proses setup?' } },
                    { key: 'a', type: 'textarea', label: 'Jawaban', props: { rows: 3 } },
                ],
            },
        ],
    },

    // ---- CTA ----
    {
        section: 'cta',
        label: 'Call to Action',
        icon: '📣',
        description: 'Bagian CTA / ajakan di akhir halaman',
        fields: [
            { key: 'title', type: 'textarea', label: 'Judul', props: { rows: 2, placeholder: 'Siap Bawa Koperasi Anda...' } },
            { key: 'subtitle', type: 'textarea', label: 'Subjudul', props: { rows: 2 } },
            {
                key: 'primary',
                type: 'cta',
                label: 'Tombol Utama',
                props: { variant: 'primary', labelPlaceholder: 'Request Demo Sekarang', hrefPlaceholder: '/demo' },
            },
            {
                key: 'secondary',
                type: 'cta',
                label: 'Tombol Sekunder',
                props: { variant: 'secondary', labelPlaceholder: 'WhatsApp Tim', hrefPlaceholder: 'https://wa.me/...' },
            },
        ],
    },

    // ---- App Features (Mobile App Showcase) ----
    {
        section: 'app_features',
        label: 'Fitur Aplikasi',
        icon: '📱',
        description: 'Bagian showcase aplikasi mobile — fitur-fitur dengan screenshot',
        fields: [
            { key: 'badge', type: 'text', label: 'Badge', props: { placeholder: 'Mobile App' } },
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Koperasi di Saku Anggota' } },
            { key: 'subtitle', type: 'textarea', label: 'Subjudul', props: { rows: 2, placeholder: 'Deskripsi bagian aplikasi...' } },
            {
                key: 'features',
                type: 'repeater',
                label: 'Fitur Aplikasi',
                props: { addLabel: 'Tambah Fitur', titleKey: 'title' },
                fields: [
                    { key: 'title', type: 'text', label: 'Judul Fitur', props: { placeholder: 'Cek Tabungan & Pinjaman' } },
                    { key: 'description', type: 'textarea', label: 'Deskripsi', props: { rows: 3, placeholder: 'Deskripsi fitur...' } },
                    { key: 'screenshot', type: 'image', label: 'Screenshot', props: { helpText: 'Screenshot aplikasi untuk fitur ini' } },
                    { key: 'icon', type: 'icon-select', label: 'Icon' },
                ],
            },
            { key: 'cta_label', type: 'text', label: 'Tombol CTA', props: { placeholder: 'Coba Demo Sekarang' } },
        ],
    },

    // ---- Demo ----
    {
        section: 'demo',
        label: 'Demo Page',
        icon: '🎮',
        description: 'Pengaturan halaman demo',
        fields: [
            { key: 'badge', type: 'text', label: 'Badge', props: { placeholder: 'Demo Interaktif' } },
            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Coba e-Koperasi Sekarang' } },
            { key: 'subtitle', type: 'textarea', label: 'Subjudul', props: { rows: 2 } },
            { key: 'reset_notice', type: 'text', label: 'Notifikasi Reset', props: { placeholder: 'Akun demo di-reset setiap hari pukul 00:00 WIB.' } },
            { key: 'accounts_intro', type: 'text', label: 'Intro Akun', props: { placeholder: 'Akun Demo Tersedia' } },
            {
                key: 'accounts',
                type: 'repeater',
                label: 'Akun Demo',
                props: { addLabel: 'Tambah Akun', titleKey: 'label' },
                fields: [
                    { key: 'key', type: 'text', label: 'Key Role', props: { placeholder: 'admin', helpText: 'admin, koordinator, pimpinan, colead, cashier, pdl' } },
                    { key: 'label', type: 'text', label: 'Label', props: { placeholder: 'Ketua Koperasi' } },
                    { key: 'email', type: 'text', label: 'Email', props: { placeholder: 'admin@demo.e-koperasi.com' } },
                    { key: 'pin', type: 'text', label: 'PIN / Password', props: { placeholder: '123456' } },
                ],
            },
            {
                key: 'roles_intro',
                type: 'group',
                label: 'Role Akun',
                fields: [
                    { key: 'admin', type: 'text', label: 'Admin', props: { placeholder: 'Ketua Koperasi' } },
                    { key: 'koordinator', type: 'text', label: 'Koordinator', props: { placeholder: 'Koordinator Unit' } },
                    { key: 'pimpinan', type: 'text', label: 'Pimpinan', props: { placeholder: 'Pimpinan' } },
                    { key: 'colead', type: 'text', label: 'Co-Lead', props: { placeholder: 'Co-Lead' } },
                    { key: 'cashier', type: 'text', label: 'Cashier', props: { placeholder: 'Cashier' } },
                    { key: 'pdl', type: 'text', label: 'PDL', props: { placeholder: 'Penagih (PDL)' } },
                ],
            },
        ],
    },

    // ---- Contact ----
    {
        section: 'contact',
        label: 'Kontak',
        icon: '📞',
        description: 'Informasi kontak perusahaan',
        fields: [
            { key: 'email', type: 'text', label: 'Email', props: { placeholder: 'halo@e-koperasi.com', type: 'email' } },
            { key: 'whatsapp', type: 'text', label: 'WhatsApp', props: { placeholder: '+62 812-3456-7890' } },
            { key: 'whatsapp_url', type: 'text', label: 'WhatsApp URL', props: { placeholder: 'https://wa.me/6281234567890', type: 'url' } },
            { key: 'address', type: 'text', label: 'Alamat', props: { placeholder: 'Tabanan, Bali, Indonesia' } },
        ],
    },

    // ---- About ----
    {
        section: 'about',
        label: 'Tentang Kami',
        icon: '🏢',
        description: 'Halaman tentang perusahaan',
        fields: [
            {
                key: 'company',
                type: 'group',
                label: 'Informasi Perusahaan',
                fields: [
                    { key: 'name', type: 'text', label: 'Nama Brand', props: { placeholder: 'e-Koperasi' } },
                    { key: 'legal_name', type: 'text', label: 'Nama Legal', props: { placeholder: 'CV Tabanan Digital Nusantara' } },
                    { key: 'founded', type: 'text', label: 'Tahun Berdiri', props: { placeholder: '2022' } },
                    { key: 'origin', type: 'text', label: 'Asal', props: { placeholder: 'Tabanan, Bali' } },
                    { key: 'mission', type: 'textarea', label: 'Misi', props: { rows: 2 } },
                    { key: 'vision', type: 'textarea', label: 'Visi', props: { rows: 2 } },
                    { key: 'story', type: 'textarea', label: 'Cerita Perusahaan', props: { rows: 4 } },
                    {
                        key: 'values',
                        type: 'repeater',
                        label: 'Nilai Perusahaan',
                        props: { addLabel: 'Tambah Nilai', titleKey: 'title' },
                        fields: [
                            { key: 'title', type: 'text', label: 'Judul', props: { placeholder: 'Transparan' } },
                            { key: 'desc', type: 'text', label: 'Deskripsi', props: { placeholder: 'Setiap transaksi tercatat real-time' } },
                        ],
                    },
                    {
                        key: 'team',
                        type: 'repeater',
                        label: 'Tim',
                        props: { addLabel: 'Tambah Anggota Tim', titleKey: 'name' },
                        fields: [
                            { key: 'name', type: 'text', label: 'Nama', props: { placeholder: 'I Wayan Sudirta' } },
                            { key: 'role', type: 'text', label: 'Role', props: { placeholder: 'CEO & Co-Founder' } },
                            { key: 'bio', type: 'text', label: 'Bio', props: { placeholder: '15 tahun di industri koperasi...' } },
                        ],
                    },
                ],
            },
        ],
    },

    // ---- Legal ----
    {
        section: 'legal',
        label: 'Legal',
        icon: '⚖️',
        description: 'Pengaturan halaman legal — privasi, syarat & ketentuan, dan kepatuhan',
        fields: [
            { key: 'last_updated', type: 'text', label: 'Terakhir Diperbarui', props: { placeholder: '15 Juni 2026' } },
            { key: 'company_name', type: 'text', label: 'Nama Perusahaan (Legal)', props: { placeholder: 'CV Tabanan Digital Nusantara', helpText: 'Nama badan hukum untuk dokumen legal' } },
            { key: 'privacy_email', type: 'text', label: 'Email Privasi', props: { placeholder: 'privacy@e-koperasi.com', helpText: 'Alamat email untuk permintaan data pribadi' } },
            { key: 'jurisdiction', type: 'text', label: 'Yurisdiksi Hukum', props: { placeholder: 'Republik Indonesia' } },
            {
                key: 'privacy',
                type: 'group',
                label: 'Kebijakan Privasi',
                fields: [
                    { key: 'headline', type: 'text', label: 'Judul Halaman', props: { placeholder: 'Kebijakan Privasi' } },
                    { key: 'intro', type: 'textarea', label: 'Pendahuluan', props: { rows: 2, placeholder: 'e-Koperasi berkomitmen melindungi data pribadi Anda sesuai UU PDP Indonesia.' } },
                    { key: 'data_collected', type: 'textarea', label: 'Data yang Dikumpulkan', props: { rows: 3 } },
                    { key: 'data_usage', type: 'textarea', label: 'Penggunaan Data', props: { rows: 3 } },
                    { key: 'data_security', type: 'textarea', label: 'Penyimpanan & Keamanan', props: { rows: 2 } },
                    { key: 'user_rights', type: 'textarea', label: 'Hak Pengguna', props: { rows: 2 } },
                ],
            },
            {
                key: 'terms',
                type: 'group',
                label: 'Syarat & Ketentuan',
                fields: [
                    { key: 'headline', type: 'text', label: 'Judul Halaman', props: { placeholder: 'Syarat & Ketentuan' } },
                    { key: 'intro', type: 'textarea', label: 'Pendahuluan', props: { rows: 2 } },
                    { key: 'services', type: 'textarea', label: 'Layanan', props: { rows: 2 } },
                    { key: 'content_rights', type: 'textarea', label: 'Hak Cipta Konten', props: { rows: 2 } },
                    { key: 'third_party', type: 'textarea', label: 'Tautan Pihak Ketiga', props: { rows: 2 } },
                    { key: 'liability', type: 'textarea', label: 'Batasan Tanggung Jawab', props: { rows: 2 } },
                ],
            },
        ],
    },

    // ---- SEO ----
    {
        section: 'seo',
        label: 'SEO',
        icon: '🔍',
        description: 'Pengaturan SEO dan Open Graph untuk sosial media',
        fields: [
            { key: 'default_title', type: 'text', label: 'Default Title', props: { placeholder: 'e-Koperasi - Platform Digital...' } },
            { key: 'description', type: 'textarea', label: 'Meta Description', props: { rows: 2 } },
            { key: 'keywords', type: 'text', label: 'Keywords', props: { placeholder: 'koperasi, aplikasi koperasi...' } },
            { key: 'canonical_url', type: 'text', label: 'Canonical URL', props: { placeholder: 'https://e-koperasi.com', helpText: 'URL utama website' } },
            {
                key: 'og',
                type: 'group',
                label: 'Open Graph (Social Media Preview)',
                fields: [
                    { key: 'title', type: 'text', label: 'OG Title', props: { placeholder: 'e-Koperasi - Platform Digital Koperasi Indonesia', helpText: 'Judul yang tampil saat dibagikan ke social media' } },
                    { key: 'description', type: 'textarea', label: 'OG Description', props: { rows: 2, helpText: 'Deskripsi yang tampil saat dibagikan ke social media' } },
                    { key: 'image', type: 'image', label: 'OG Image', props: { helpText: 'Gambar preview (1200x630px) untuk social media' } },
                ],
            },
        ],
    },
];

// Helper to get a schema by section key
export function getSectionSchema(section: string): SectionSchema | undefined {
    return sectionSchemas.find((s) => s.section === section);
}

// Build a map for quick lookup
export const sectionSchemaMap: Record<string, SectionSchema> = {};
for (const schema of sectionSchemas) {
    sectionSchemaMap[schema.section] = schema;
}
