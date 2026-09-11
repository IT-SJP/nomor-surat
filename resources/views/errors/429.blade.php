@extends('errors.layout')

@section('title', 'Terlalu Banyak Permintaan')
@section('code', '429')
@section('badge', '429 TOO MANY REQUESTS')
@section('theme', 'amber')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>
</svg>
@endsection

@section('description', !empty($exception) && $exception->getMessage() ? $exception->getMessage() : 'Sistem mendeteksi terlalu banyak permintaan dalam waktu singkat. Akses dibatasi sejenak demi keamanan server. Silakan tunggu beberapa saat sebelum mencoba kembali.')