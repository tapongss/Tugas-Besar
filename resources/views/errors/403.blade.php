@extends('errors.layout')

@section('title', 'Akses ditolak')
@section('icon', 'lock')
@section('code', '403')

@section('message')
    Akun Anda tidak memiliki izin untuk mengakses halaman ini. Peran <strong>Viewer</strong> hanya dapat melihat data, sedangkan perubahan data memerlukan peran Operator atau Administrator.
@endsection
