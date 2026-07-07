@extends('emails.layout')
@section('subject', 'Pengingat Penanganan Tiket')
@section('content')
    @php
        $heading = match ($stage) {
            1 => 'Pengingat (H+1): Masih terdapat tiket yang belum diproses.',
            3 => 'Eskalasi (H+3): Tiket belum ditangani selama 3 hari kerja.',
            5 => 'Eskalasi (H+5): Tiket telah melewati batas waktu penanganan.',
            default => 'Pengingat penanganan tiket.',
        };
    @endphp

    <p><strong>{{ $heading }}</strong></p>

    <p>Berikut daftar tiket yang belum berpindah ke status <em>Diproses</em>:</p>

    <table role="presentation" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;margin:16px 0;font-size:13px;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="border-bottom:1px solid #e5e7eb;">Nomor Tiket</th>
            <th align="left" style="border-bottom:1px solid #e5e7eb;">Kategori</th>
            <th align="left" style="border-bottom:1px solid #e5e7eb;">Judul</th>
            <th align="left" style="border-bottom:1px solid #e5e7eb;">Tanggal</th>
        </tr>
        @foreach ($tickets as $t)
            <tr>
                <td style="border-bottom:1px solid #f0f0f0;">{{ $t->ticket_number }}</td>
                <td style="border-bottom:1px solid #f0f0f0;">{{ $t->category->name }}</td>
                <td style="border-bottom:1px solid #f0f0f0;">{{ $t->title }}</td>
                <td style="border-bottom:1px solid #f0f0f0;">{{ $t->created_at->format('d/m/Y') }}</td>
            </tr>
        @endforeach
    </table>

    <p style="margin:24px 0;">
        <a href="{{ route('admin.tickets.index') }}" style="background:#008a41;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:6px;display:inline-block;">Buka Dashboard</a>
    </p>

    <p style="color:#6b7280;">Total: {{ $tickets->count() }} tiket.</p>
@endsection
