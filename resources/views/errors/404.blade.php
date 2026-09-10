@extends('errors.layout')

@section('title', 'Halaman Tidak Ditemukan')
@section('code', '404')
@section('badge', '404 NOT FOUND')
@section('theme', 'blue')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <circle cx="12" cy="12" r="10"/>
    <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>
</svg>
@endsection

@section('description', !empty($exception) && $exception->getMessage() ? $exception->getMessage() : 'Halaman yang Anda tuju tidak dapat ditemukan. Kemungkinan alamat URL salah, halaman telah dipindahkan, atau tautan sudah kedaluwarsa.')