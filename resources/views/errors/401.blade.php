@extends('errors.layout')

@section('title', 'Sesi Login Diperlukan')
@section('code', '401')
@section('badge', '401 UNAUTHORIZED')
@section('theme', 'red')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <rect width="18" height="11" x="3" y="11" rx="2" ry="2"/>
    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
</svg>
@endsection

@section('description', !empty($exception) && $exception->getMessage() ? $exception->getMessage() : 'Anda harus masuk (login) terlebih dahulu melalui portal Absenku SJP untuk dapat mengakses Sistem Nomor Surat.')

@section('home_label', 'Buka Portal Absenku SJP')
@section('home_url', config('services.sso.absen_url', env('ABSEN_APP_URL', 'https://absenkusjp.com')))