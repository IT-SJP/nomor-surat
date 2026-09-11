@extends('errors.layout')

@section('title', 'Akses Tidak Diizinkan')
@section('code', '403')
@section('badge', '403 FORBIDDEN')
@section('theme', 'red')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
    <line x1="9.5" y1="9.5" x2="14.5" y2="14.5"/>
    <line x1="14.5" y1="9.5" x2="9.5" y2="14.5"/>
</svg>
@endsection

@section('description', !empty($reason) ? $reason : (!empty($exception) && $exception->getMessage() ? $exception->getMessage() : 'Akun Anda tidak memiliki izin atau wewenang untuk mengakses halaman atau fitur ini. Silakan hubungi Administrator jika Anda merasa ini adalah kekeliruan.'))