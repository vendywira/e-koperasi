<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('About', [
            'company' => [
                'name' => 'e-Koperasi',
                'legal_name' => 'CV Tabanan Digital Nusantara',
                'founded' => '2022',
                'origin' => 'Tabanan, Bali',
                'mission' => 'Mendigitalisasi koperasi Indonesia agar lebih efisien, transparan, dan modern.',
                'vision' => 'Menjadi platform standar operasional koperasi Indonesia pada 2030.',
                'story' => 'e-Koperasi lahir dari kebutuhan nyata KSU Tabanan Jaya — koperasi serba usaha yang melayani 3000+ anggota di Bali. Sistem manual Excel dan WhatsApp tidak lagi memadai. Kami membangun platform all-in-one: dari pencatatan kasbon, pinjaman, slip gaji, sampai tracking GPS penagih. Setelah terbukti di internal, kami membuka platform ini untuk koperasi lain di Indonesia.',
                'values' => [
                    ['title' => 'Trasparan', 'desc' => 'Setiap transaksi tercatat real-time, audit-ready.'],
                    ['title' => 'Indonesia-First', 'desc' => 'Dibangun oleh orang Indonesia, untuk koperasi Indonesia.'],
                    ['title' => 'Pragmatis', 'desc' => 'Fitur yang solving masalah nyata, bukan nice-to-have.'],
                ],
                'team' => [
                    ['name' => 'I Wayan Sudirta', 'role' => 'CEO & Co-Founder', 'bio' => '15 tahun di industri koperasi, Ketua KSU Tabanan Jaya.'],
                    ['name' => 'I Made Wirawan', 'role' => 'CTO & Co-Founder', 'bio' => 'Full-stack engineer, membangun sistem dari 0.'],
                ],
            ],
        ]);
    }
}
