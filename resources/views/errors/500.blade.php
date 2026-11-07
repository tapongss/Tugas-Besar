@extends('errors.layout')

@section('title', 'Terjadi kesalahan sistem')
@section('icon', 'alert')
@section('code', '500')

@section('message')
    Sistem mengalami kendala saat memproses permintaan Anda. Silakan coba beberapa saat lagi. Jika masalah berlanjut, hubungi administrator modul dengan menyertakan waktu kejadian.
@endsection
