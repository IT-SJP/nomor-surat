@extends('errors.layout')

@section('title', 'Terjadi Kesalahan Server')
@section('code', '500')
@section('badge', '500 INTERNAL ERROR')
@section('theme', 'red')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <rect width="20" height="8" x="2" y="2" rx="2" ry="2"/>
    <rect width="20" height="8" x="2" y="14" rx="2" ry="2"/>
    <line x1="6" y1="6" x2="6.01" y2="6"/>
    <line x1="6" y1="18" x2="6.01" y2="18"/>
</svg>
@endsection

@section('description', !empty($exception) && $exception->getMessage() && !app()->isProduction() ? $exception->getMessage() : 'Terjadi kendala teknis internal pada sistem saat memproses permintaan Anda. Silakan coba beberapa saat lagi atau hubungi Tim IT jika kendala berlanjut.')