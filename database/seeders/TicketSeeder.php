<?php

namespace Database\Seeders;

use App\Models\Ticket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $client = User::firstOrCreate(
            ['email' => 'client@demo.com'],
            [
                'name' => 'Ahmad Koperasi Sejahtera',
                'password' => bcrypt('password'),
                'role' => 'client',
                'phone' => '081234567890',
            ]
        );

        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name' => 'Budi Santoso',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '081234567891',
            ]
        );

        $itOps = User::firstOrCreate(
            ['email' => 'itops@demo.com'],
            [
                'name' => 'Citra Dewi',
                'password' => bcrypt('password'),
                'role' => 'it-ops',
                'phone' => '081234567892',
            ]
        );

        // Clean tickets from demo user to allow re-run
        Ticket::where('user_id', $client->id)->delete();

        // Ticket 1: Pending
        $t1 = Ticket::create([
            'user_id' => $client->id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject' => 'Tidak bisa login ke dashboard client',
            'description' => "Assalamualaikum,\n\nSaya sudah beberapa hari ini tidak bisa login ke dashboard client. Setiap kali memasukkan email dan password yang benar, selalu muncul pesan \"Invalid credentials\".\n\nMohon bantuannya.\n\nTerima kasih.",
            'status' => 'pending',
            'priority' => 'high',
        ]);

        // Ticket 2: In Progress (with replies)
        $t2 = Ticket::create([
            'user_id' => $client->id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject' => 'Data anggota tidak muncul di laporan',
            'description' => "Laporan bulanan yang saya generate tidak menampilkan semua data anggota. Sekitar 10 anggota tidak muncul. Sudah saya coba refresh beberapa kali tapi tetap sama.",
            'status' => 'in_progress',
            'priority' => 'medium',
            'assigned_to' => $itOps->id,
        ]);

        TicketReply::create(['ticket_id' => $t2->id, 'user_id' => $itOps->id, 'message' => 'Terima kasih sudah melaporkan. Saat ini sedang kami periksa databasenya. Ada indikasi beberapa data anggota yang tidak sinkron. Kami update lagi dalam 1x24 jam.']);
        TicketReply::create(['ticket_id' => $t2->id, 'user_id' => $client->id, 'message' => 'Baik pak, terima kasih informasinya. Saya tunggu update-nya.']);

        // Ticket 3: Solved
        $t3 = Ticket::create([
            'user_id' => $client->id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject' => 'Cara export data simpanan ke Excel',
            'description' => 'Apakah ada fitur export data simpanan ke format Excel? Saya tidak menemukan tombol export di halaman simpanan.',
            'status' => 'solved',
            'priority' => 'low',
            'assigned_to' => $admin->id,
        ]);

        TicketReply::create(['ticket_id' => $t3->id, 'user_id' => $admin->id, 'message' => 'Untuk saat ini fitur export Excel belum tersedia. Tapi kami sudah memasukkannya ke roadmap. Sementara, Bapak bisa download CSV dari halaman laporan dan membukanya di Excel.']);
        TicketReply::create(['ticket_id' => $t3->id, 'user_id' => $client->id, 'message' => 'Baik, terima kasih. Saya coba dulu pakai CSV.']);
        TicketReply::create(['ticket_id' => $t3->id, 'user_id' => $admin->id, 'message' => 'Update: fitur export Excel untuk data simpanan sudah tersedia sekarang di menu Laporan -> Export Excel. Silakan dicoba!']);

        // Ticket 4: Closed
        $t4 = Ticket::create([
            'user_id' => $client->id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject' => 'Notifikasi email tidak masuk',
            'description' => 'Setelah update minggu lalu, saya tidak mendapatkan notifikasi email untuk konfirmasi transaksi dan pengingat pembayaran. Setting notifikasi sudah aktif.',
            'status' => 'close',
            'priority' => 'high',
            'assigned_to' => $itOps->id,
        ]);

        TicketReply::create(['ticket_id' => $t4->id, 'user_id' => $itOps->id, 'message' => 'Kami sudah investigasi dan ternyata ada perubahan pada mail server. Sudah kami perbaiki konfigurasinya. Silakan cek kembali email Anda.']);
        TicketReply::create(['ticket_id' => $t4->id, 'user_id' => $client->id, 'message' => 'Sudah masuk semua notifikasinya. Terima kasih banyak!']);
        TicketReply::create(['ticket_id' => $t4->id, 'user_id' => $itOps->id, 'message' => 'Sama-sama. Senang bisa membantu.']);

        // Ticket 5: Acknowledge
        $t5 = Ticket::create([
            'user_id' => $client->id,
            'ticket_number' => Ticket::generateTicketNumber(),
            'subject' => 'Permintaan penambahan menu laporan laba rugi',
            'description' => 'Kami ingin mengajukan penambahan menu laporan laba rugi di dashboard client. Fitur ini sangat kami butuhkan untuk laporan bulanan ke pengurus.',
            'status' => 'acknowledge',
            'priority' => 'medium',
            'assigned_to' => $admin->id,
        ]);

        TicketReply::create(['ticket_id' => $t5->id, 'user_id' => $admin->id, 'message' => 'Terima kasih atas masukannya. Kami sudah terima permintaan ini dan akan didiskusikan dengan tim developer. Estimasi sementara 2-3 minggu.']);

        $this->command->info('=== Ticket Seeder Selesai ===');
        $this->command->info('Login:');
        $this->command->info('  Client -> client@demo.com / password');
        $this->command->info('  Admin  -> admin@demo.com / password');
        $this->command->info('  IT-Ops -> itops@demo.com / password');
    }
}
