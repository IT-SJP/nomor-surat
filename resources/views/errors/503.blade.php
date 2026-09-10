@extends('errors.layout')

@section('title', 'Sedang Dalam Pemeliharaan')
@section('code', '503')
@section('badge', '503 MAINTENANCE')
@section('theme', 'amber')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>
</svg>
@endsection

@section('description', !empty($exception) && $exception->getMessage() ? $exception->getMessage() : 'Sistem Nomor Surat sedang dalam proses pemeliharaan berkala atau pembaruan sistem. Layanan akan segera aktif kembali dalam beberapa saat.')

@section('primary_button')
<button type="button" onclick="window.location.reload()" class="btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
        <path d="M21 3v5h-5"/>
        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
        <path d="M8 16H3v5"/>
    </svg>
    <span>Cek Ulang Status Layanan</span>
</button>
@endsection