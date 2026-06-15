<x-mail::message>
# Demo Request Baru

Ada permintaan demo dari koperasi.

**Nama:** {{ $data['name'] }}
**Jabatan:** {{ $data['role'] }}
**Koperasi:** {{ $data['cooperative_name'] }}
**Jumlah Anggota:** {{ $data['member_count'] }}
**WhatsApp:** {{ $data['whatsapp'] }}

@if($data['message'] ?? null)
**Pesan:**
{{ $data['message'] }}
@endif

<x-mail::button :url="'https://wa.me/' . preg_replace('/[^0-9]/', '', $data['whatsapp'])">
Balas via WhatsApp
</x-mail::button>
</x-mail::message>
