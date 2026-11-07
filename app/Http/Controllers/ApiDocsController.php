<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function index(): View
    {
        $base = url('/api/v1');

        $endpoints = [
            [
                'group' => 'Gedung',
                'color' => 'sky',
                'items' => [
                    ['method' => 'GET', 'path' => '/buildings', 'description' => 'Daftar gedung (paginasi, pencarian, filter status).', 'auth' => false],
                    ['method' => 'GET', 'path' => '/buildings/{id}', 'description' => 'Detail gedung beserta daftar ruang.', 'auth' => false],
                    ['method' => 'POST', 'path' => '/buildings', 'description' => 'Menambah gedung baru.', 'auth' => true],
                    ['method' => 'PUT', 'path' => '/buildings/{id}', 'description' => 'Memperbarui data gedung.', 'auth' => true],
                    ['method' => 'DELETE', 'path' => '/buildings/{id}', 'description' => 'Menghapus gedung tanpa ruang.', 'auth' => true],
                ],
            ],
            [
                'group' => 'Ruang',
                'color' => 'teal',
                'items' => [
                    ['method' => 'GET', 'path' => '/rooms', 'description' => 'Daftar ruang (filter gedung, jenis, status, fasilitas).', 'auth' => false],
                    ['method' => 'GET', 'path' => '/rooms/{id}', 'description' => 'Detail ruang beserta fasilitas dan kondisi.', 'auth' => false],
                    ['method' => 'POST', 'path' => '/rooms', 'description' => 'Menambah ruang beserta daftar fasilitas.', 'auth' => true],
                    ['method' => 'PUT', 'path' => '/rooms/{id}', 'description' => 'Memperbarui ruang dan sinkronisasi fasilitas.', 'auth' => true],
                    ['method' => 'DELETE', 'path' => '/rooms/{id}', 'description' => 'Menghapus ruang tanpa riwayat jadwal.', 'auth' => true],
                ],
            ],
            [
                'group' => 'Fasilitas',
                'color' => 'violet',
                'items' => [
                    ['method' => 'GET', 'path' => '/facilities', 'description' => 'Daftar master fasilitas.', 'auth' => false],
                    ['method' => 'GET', 'path' => '/facilities/{id}', 'description' => 'Detail fasilitas dan ruang pengguna.', 'auth' => false],
                    ['method' => 'POST', 'path' => '/facilities', 'description' => 'Menambah fasilitas baru.', 'auth' => true],
                    ['method' => 'PUT', 'path' => '/facilities/{id}', 'description' => 'Memperbarui fasilitas.', 'auth' => true],
                    ['method' => 'DELETE', 'path' => '/facilities/{id}', 'description' => 'Menghapus fasilitas yang belum terpasang.', 'auth' => true],
                ],
            ],
            [
                'group' => 'Jadwal Sidang',
                'color' => 'amber',
                'items' => [
                    ['method' => 'GET', 'path' => '/schedules', 'description' => 'Daftar jadwal dengan filter tanggal, ruang, status.', 'auth' => false],
                    ['method' => 'GET', 'path' => '/schedules/{id}', 'description' => 'Detail jadwal sidang.', 'auth' => false],
                    ['method' => 'POST', 'path' => '/schedules', 'description' => 'Membuat jadwal dengan validasi bentrok ruang.', 'auth' => true],
                    ['method' => 'PUT', 'path' => '/schedules/{id}', 'description' => 'Memperbarui jadwal dan validasi bentrok.', 'auth' => true],
                    ['method' => 'DELETE', 'path' => '/schedules/{id}', 'description' => 'Menghapus jadwal sidang.', 'auth' => true],
                ],
            ],
        ];

        return view('api-docs', [
            'base' => $base,
            'endpoints' => $endpoints,
            'demoTokens' => [
                'Administrator' => 'ta-admin-demo-token',
                'Operator' => 'ta-operator-demo-token',
                'Viewer (hanya baca)' => 'ta-viewer-demo-token',
            ],
        ]);
    }
}
