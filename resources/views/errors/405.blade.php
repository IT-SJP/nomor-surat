@extends('errors.layout')

@section('title', 'Metode Tidak Diizinkan')
@section('code', '405')
@section('badge', '405 METHOD NOT ALLOWED')
@section('theme', 'purple')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="10"/>
    <line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
</svg>
@endsection

@section('description', !empty($exception) && $exception->getMessage() ? $exception->getMessage() : 'Metode permintaan (HTTP Method) yang dikirimkan tidak didukung oleh sistem untuk rute ini. Silakan kembali dan coba gunakan formulir yang disediakan.')