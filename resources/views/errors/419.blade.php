@extends('errors.layout')

@section('title', 'Sesi Halaman Berakhir')
@section('code', '419')
@section('badge', '419 PAGE EXPIRED')
@section('theme', 'amber')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="10"/>
    <polyline points="12 6 12 12 16 14"/>
</svg>
@endsection

@section('description', 'Sesi token keamanan formulir telah kedaluwarsa karena tidak ada aktivitas dalam beberapa waktu. Silakan muat ulang halaman untuk melanjutkan.')

@section('primary_button')
<button type="button" onclick="window.location.reload()" class="btn-primary">
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
        <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
        <path d="M21 3v5h-5"/>
        <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
        <path d="M8 16H3v5"/>
    </svg>
    <span>Muat Ulang Halaman</span>
</button>
@endsection