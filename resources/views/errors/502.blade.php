@extends('errors.layout')

@section('title', 'Gerbang Server Bermasalah')
@section('code', '502')
@section('badge', '502 BAD GATEWAY')
@section('theme', 'slate')

@section('icon')
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round">
    <path d="M5 12.55a11 11 0 0 1 14.08 0"/>
    <path d="M1.42 9a16 16 0 0 1 21.16 0"/>
    <path d="M8.53 16.11a6 6 0 0 1 6.95 0"/>
    <line x1="12" y1="20" x2="12.01" y2="20"/>
    <line x1="2" y1="2" x2="22" y2="22"/>
</svg>
@endsection

@section('description', 'Server perantara (gateway) tidak menerima respons yang valid dari layanan upstream. Silakan coba muat ulang beberapa saat lagi.')