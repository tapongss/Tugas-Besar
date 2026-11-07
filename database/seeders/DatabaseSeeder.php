<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Building;
use App\Models\DefenseSession;
use App\Models\Facility;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = $this->seedUsers();
        $buildings = $this->seedBuildings();
        $facilities = $this->seedFacilities();
        $this->seedRooms($buildings, $facilities);
        $this->seedSchedules($admin);
        $this->seedActivities($admin);
    }

    private function seedUsers(): User
    {
        $accounts = [
            [
                'name' => 'Dr. Andi Wijaya',
                'email' => 'admin@ta.test',
                'role' => User::ROLE_ADMIN,
                'phone' => '081234567890',
                'token' => 'ta-admin-demo-token',
            ],
            [
                'name' => 'Rina Kusuma',
                'email' => 'operator@ta.test',
                'role' => User::ROLE_OPERATOR,
                'phone' => '081298765432',
                'token' => 'ta-operator-demo-token',
            ],
            [
                'name' => 'Budi Santoso',
                'email' => 'viewer@ta.test',
                'role' => User::ROLE_VIEWER,
                'phone' => '081211223344',
                'token' => 'ta-viewer-demo-token',
            ],
        ];

        $admin = null;

        foreach ($accounts as $account) {
            $user = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => 'password',
                    'role' => $account['role'],
                    'phone' => $account['phone'],
                    'is_active' => true,
                    'api_token' => hash('sha256', $account['token']),
                    'last_login_at' => now()->subHours(random_int(1, 48)),
                ]
            );

            $admin ??= $user;
        }

        return $admin;
    }

    /**
     * @return array<string, Building>
     */
    private function seedBuildings(): array
    {
        $rows = [
            ['code' => 'GD-A', 'name' => 'Gedung Utama', 'address' => 'Jl. Pendidikan No. 1, Kampus Pusat', 'floors' => 4, 'notes' => 'Gedung utama fakultas dengan ruang sidang dan aula serbaguna.'],
            ['code' => 'GD-B', 'name' => 'Gedung Fakultas Teknik', 'address' => 'Jl. Pendidikan No. 3, Kampus Timur', 'floors' => 3, 'notes' => 'Pusat laboratorium multimedia dan ruang seminar teknik.'],
            ['code' => 'GD-C', 'name' => 'Gedung Pascasarjana', 'address' => 'Jl. Pendidikan No. 5, Kampus Barat', 'floors' => 3, 'notes' => 'Khusus program magister dan doktor.'],
        ];

        $buildings = [];

        foreach ($rows as $row) {
            $buildings[$row['code']] = Building::updateOrCreate(
                ['code' => $row['code']],
                [...$row, 'is_active' => true]
            );
        }

        return $buildings;
    }

    /**
     * @return array<string, Facility>
     */
    private function seedFacilities(): array
    {
        $rows = [
            ['code' => 'FS-PRJ', 'name' => 'Proyektor Epson EB-X06', 'category' => 'display', 'unit' => 'unit', 'description' => 'Proyektor 3600 lumens dengan koneksi HDMI dan VGA.'],
            ['code' => 'FS-SCR', 'name' => 'Layar Proyektor 120 inci', 'category' => 'display', 'unit' => 'unit', 'description' => 'Layar gulung manual untuk presentasi.'],
            ['code' => 'FS-PC', 'name' => 'PC All-in-One Lenovo', 'category' => 'komputer', 'unit' => 'unit', 'description' => 'Komputer presentasi dengan Windows 11 dan Microsoft Office.'],
            ['code' => 'FS-CAM', 'name' => 'Kamera Konferensi Logitech', 'category' => 'komputer', 'unit' => 'unit', 'description' => 'Kamera 1080p untuk sidang daring atau hybrid.'],
            ['code' => 'FS-MIC', 'name' => 'Microphone Wireless', 'category' => 'audio', 'unit' => 'unit', 'description' => 'Microphone nirkabel untuk penguji dan mahasiswa.'],
            ['code' => 'FS-AUD', 'name' => 'Sound System Ruangan', 'category' => 'audio', 'unit' => 'set', 'description' => 'Speaker aktif dan mixer audio.'],
            ['code' => 'FS-WIFI', 'name' => 'Router Wi-Fi 6', 'category' => 'jaringan', 'unit' => 'unit', 'description' => 'Akses internet kampus dengan bandwidth tinggi.'],
            ['code' => 'FS-AC', 'name' => 'AC Split 1.5 PK', 'category' => 'pendingin', 'unit' => 'unit', 'description' => 'Pendingin ruangan dengan kapasitas 1.5 PK.'],
            ['code' => 'FS-MJR', 'name' => 'Meja Sidang', 'category' => 'furnitur', 'unit' => 'unit', 'description' => 'Meja panjang untuk dewan penguji.'],
            ['code' => 'FS-KRS', 'name' => 'Kursi Penguji', 'category' => 'furnitur', 'unit' => 'unit', 'description' => 'Kursi ergonomis untuk dosen penguji.'],
            ['code' => 'FS-BRD', 'name' => 'Papan Tulis', 'category' => 'furnitur', 'unit' => 'unit', 'description' => 'Papan tulis whiteboard 120 x 240 cm.'],
        ];

        $facilities = [];

        foreach ($rows as $row) {
            $facilities[$row['code']] = Facility::updateOrCreate(
                ['code' => $row['code']],
                [...$row, 'is_active' => true]
            );
        }

        return $facilities;
    }

    /**
     * @param  array<string, Building>  $buildings
     * @param  array<string, Facility>  $facilities
     */
    private function seedRooms(array $buildings, array $facilities): void
    {
        $rows = [
            [
                'code' => 'A.201', 'building' => 'GD-A', 'name' => 'Ruang Sidang Utama', 'floor' => 2,
                'type' => 'ruang_sidang', 'capacity' => 40, 'status' => 'tersedia',
                'notes' => 'Ruang sidang utama dengan tata letak U dan pencahayaan alami.',
                'facilities' => [
                    'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'baik'], 'FS-PC' => [1, 'baik'],
                    'FS-MIC' => [3, 'baik'], 'FS-AUD' => [1, 'baik'], 'FS-AC' => [2, 'baik'],
                    'FS-MJR' => [4, 'baik'], 'FS-KRS' => [6, 'baik'], 'FS-CAM' => [1, 'baik'],
                ],
            ],
            [
                'code' => 'A.202', 'building' => 'GD-A', 'name' => 'Ruang Ujian 1', 'floor' => 2,
                'type' => 'ruang_ujian', 'capacity' => 20, 'status' => 'tersedia',
                'notes' => 'Ruang ujian fleksibel untuk sidang proposal dan hasil.',
                'facilities' => [
                    'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'baik'], 'FS-AC' => [1, 'baik'],
                    'FS-MJR' => [2, 'baik'], 'FS-KRS' => [4, 'baik'], 'FS-BRD' => [1, 'rusak_ringan'],
                ],
            ],
            [
                'code' => 'A.203', 'building' => 'GD-A', 'name' => 'Ruang Ujian 2', 'floor' => 2,
                'type' => 'ruang_ujian', 'capacity' => 18, 'status' => 'perbaikan',
                'notes' => 'Dalam perbaikan instalasi kelistrikan hingga akhir bulan.',
                'facilities' => [
                    'FS-PRJ' => [1, 'rusak_berat'], 'FS-SCR' => [1, 'baik'], 'FS-AC' => [1, 'rusak_ringan'],
                    'FS-MJR' => [2, 'baik'], 'FS-KRS' => [4, 'baik'],
                ],
            ],
            [
                'code' => 'A.301', 'building' => 'GD-A', 'name' => 'Aula Serbaguna', 'floor' => 3,
                'type' => 'aula', 'capacity' => 120, 'status' => 'tersedia',
                'notes' => 'Digunakan untuk seminar hasil dan yudisium.',
                'facilities' => [
                    'FS-PRJ' => [2, 'baik'], 'FS-SCR' => [1, 'baik'], 'FS-AUD' => [1, 'baik'],
                    'FS-MIC' => [4, 'baik'], 'FS-AC' => [4, 'baik'], 'FS-WIFI' => [1, 'baik'],
                ],
            ],
            [
                'code' => 'B.101', 'building' => 'GD-B', 'name' => 'Lab Multimedia', 'floor' => 1,
                'type' => 'laboratorium', 'capacity' => 30, 'status' => 'tersedia',
                'notes' => 'Laboratorium dengan 30 PC untuk ujian berbasis komputer.',
                'facilities' => [
                    'FS-PC' => [30, 'baik'], 'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'baik'],
                    'FS-AC' => [3, 'baik'], 'FS-WIFI' => [1, 'baik'], 'FS-AUD' => [1, 'rusak_ringan'],
                ],
            ],
            [
                'code' => 'B.102', 'building' => 'GD-B', 'name' => 'Ruang Sidang Teknik', 'floor' => 1,
                'type' => 'ruang_sidang', 'capacity' => 25, 'status' => 'tersedia',
                'notes' => 'Ruang sidang khusus program studi teknik informatika.',
                'facilities' => [
                    'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'baik'], 'FS-PC' => [1, 'baik'],
                    'FS-MIC' => [2, 'baik'], 'FS-AC' => [2, 'baik'], 'FS-MJR' => [3, 'baik'], 'FS-KRS' => [5, 'baik'],
                ],
            ],
            [
                'code' => 'B.201', 'building' => 'GD-B', 'name' => 'Ruang Diskusi 1', 'floor' => 2,
                'type' => 'ruang_diskusi', 'capacity' => 12, 'status' => 'tersedia',
                'notes' => 'Ruang kecil untuk bimbingan dan diskusi kelompok.',
                'facilities' => [
                    'FS-PC' => [1, 'baik'], 'FS-BRD' => [1, 'baik'], 'FS-AC' => [1, 'baik'], 'FS-WIFI' => [1, 'baik'],
                ],
            ],
            [
                'code' => 'B.202', 'building' => 'GD-B', 'name' => 'Ruang Seminar', 'floor' => 2,
                'type' => 'aula', 'capacity' => 60, 'status' => 'tersedia',
                'notes' => 'Ruang seminar dengan kursi kuliah dan panggung kecil.',
                'facilities' => [
                    'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'baik'], 'FS-AUD' => [1, 'baik'],
                    'FS-MIC' => [2, 'baik'], 'FS-AC' => [3, 'baik'], 'FS-CAM' => [1, 'baik'],
                ],
            ],
            [
                'code' => 'C.301', 'building' => 'GD-C', 'name' => 'Ruang Sidang Pascasarjana', 'floor' => 3,
                'type' => 'ruang_sidang', 'capacity' => 30, 'status' => 'tersedia',
                'notes' => 'Ruang sidang tesis dan disertasi dengan fasilitas telekonferensi.',
                'facilities' => [
                    'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'baik'], 'FS-PC' => [1, 'baik'],
                    'FS-CAM' => [1, 'baik'], 'FS-MIC' => [3, 'baik'], 'FS-AC' => [2, 'baik'],
                    'FS-MJR' => [4, 'baik'], 'FS-KRS' => [6, 'baik'], 'FS-WIFI' => [1, 'baik'],
                ],
            ],
            [
                'code' => 'C.302', 'building' => 'GD-C', 'name' => 'Ruang Ujian Pascasarjana', 'floor' => 3,
                'type' => 'ruang_ujian', 'capacity' => 15, 'status' => 'tersedia',
                'notes' => 'Ruang ujian dengan meja bundar.',
                'facilities' => [
                    'FS-PRJ' => [1, 'baik'], 'FS-SCR' => [1, 'rusak_ringan'], 'FS-AC' => [1, 'baik'],
                    'FS-MJR' => [1, 'baik'], 'FS-KRS' => [4, 'baik'], 'FS-WIFI' => [1, 'baik'],
                ],
            ],
            [
                'code' => 'C.101', 'building' => 'GD-C', 'name' => 'Ruang Sidang Mini', 'floor' => 1,
                'type' => 'ruang_sidang', 'capacity' => 10, 'status' => 'nonaktif',
                'notes' => 'Dialihfungsikan menjadi ruang arsip akademik.',
                'facilities' => [
                    'FS-MJR' => [1, 'baik'], 'FS-KRS' => [3, 'rusak_ringan'],
                ],
            ],
        ];

        foreach ($rows as $row) {
            $room = Room::updateOrCreate(
                ['code' => $row['code']],
                [
                    'building_id' => $buildings[$row['building']]->id,
                    'name' => $row['name'],
                    'floor' => $row['floor'],
                    'type' => $row['type'],
                    'capacity' => $row['capacity'],
                    'status' => $row['status'],
                    'notes' => $row['notes'],
                ]
            );

            $sync = [];
            foreach ($row['facilities'] as $code => [$quantity, $condition]) {
                $sync[$facilities[$code]->id] = [
                    'quantity' => $quantity,
                    'condition' => $condition,
                    'note' => null,
                ];
            }

            $room->facilities()->sync($sync);
        }
    }

    private function seedSchedules(User $admin): void
    {
        $rooms = Room::pluck('id', 'code');

        $rows = [
            ['student_nim' => '2110101001', 'date' => today()->subDay(), 'start' => '09:00', 'end' => '11:00', 'room' => 'A.202', 'status' => 'selesai', 'title' => 'Sidang Skripsi — Sistem Informasi Akademik', 'name' => 'Fajar Nugroho', 'type' => 'skripsi'],
            ['student_nim' => '2110101002', 'date' => today()->subDays(3), 'start' => '13:00', 'end' => '15:30', 'room' => 'B.101', 'status' => 'selesai', 'title' => 'Seminar Hasil — Deteksi Objek Citra Medis', 'name' => 'Ayu Lestari', 'type' => 'hasil'],
            ['student_nim' => '2110101003', 'date' => today(), 'start' => '08:00', 'end' => '09:30', 'room' => 'A.201', 'status' => 'selesai', 'title' => 'Sidang Skripsi — Analisis Sentimen Media Sosial', 'name' => 'Rizky Ramadhan', 'type' => 'skripsi'],
            ['student_nim' => '2110101004', 'date' => today(), 'start' => '10:00', 'end' => '12:00', 'room' => 'B.102', 'status' => 'berlangsung', 'title' => 'Seminar Proposal — Sistem Rekomendasi Wisata', 'name' => 'Nadia Putri', 'type' => 'proposal'],
            ['student_nim' => '2110101005', 'date' => today(), 'start' => '13:00', 'end' => '15:00', 'room' => 'C.301', 'status' => 'dijadwalkan', 'title' => 'Sidang Skripsi — Optimasi Jaringan Saraf Tiruan', 'name' => 'Bagas Pratama', 'type' => 'skripsi'],
            ['student_nim' => '2110101006', 'date' => today()->addDay(), 'start' => '09:00', 'end' => '11:00', 'room' => 'A.201', 'status' => 'dijadwalkan', 'title' => 'Sidang Skripsi — Keamanan Aplikasi Mobile', 'name' => 'Salsabila Zahra', 'type' => 'skripsi'],
            ['student_nim' => '2110101007', 'date' => today()->addDay(), 'start' => '13:00', 'end' => '15:30', 'room' => 'B.101', 'status' => 'dijadwalkan', 'title' => 'Seminar Hasil — Segmentasi Citra Satelit', 'name' => 'Dimas Saputra', 'type' => 'hasil'],
            ['student_nim' => '2110101008', 'date' => today()->addDays(2), 'start' => '08:30', 'end' => '10:00', 'room' => 'A.202', 'status' => 'dijadwalkan', 'title' => 'Seminar Proposal — Pemodelan Data Cuaca', 'name' => 'Intan Maharani', 'type' => 'proposal'],
            ['student_nim' => '2110101009', 'date' => today()->addDays(2), 'start' => '10:00', 'end' => '12:00', 'room' => 'C.302', 'status' => 'dijadwalkan', 'title' => 'Sidang Tesis — Evaluasi Kebijakan Publik Digital', 'name' => 'Hendra Gunawan', 'type' => 'ujian_lain'],
            ['student_nim' => '2110101010', 'date' => today()->addDays(3), 'start' => '09:00', 'end' => '11:30', 'room' => 'B.202', 'status' => 'dijadwalkan', 'title' => 'Seminar Hasil — Sistem Monitoring Pertanian', 'name' => 'Maya Anggraini', 'type' => 'hasil'],
            ['student_nim' => '2110101011', 'date' => today()->addDays(4), 'start' => '13:00', 'end' => '15:00', 'room' => 'A.201', 'status' => 'dijadwalkan', 'title' => 'Sidang Skripsi — Blockchain untuk Rantai Pasok', 'name' => 'Yoga Prasetyo', 'type' => 'skripsi'],
            ['student_nim' => '2110101012', 'date' => today()->addDays(5), 'start' => '08:00', 'end' => '10:00', 'room' => 'C.301', 'status' => 'batal', 'title' => 'Seminar Proposal — Personalisasi Pembelajaran', 'name' => 'Larasati Dewi', 'type' => 'proposal'],
            ['student_nim' => '2110101013', 'date' => today()->addDays(7), 'start' => '09:00', 'end' => '11:00', 'room' => 'B.102', 'status' => 'dijadwalkan', 'title' => 'Seminar Hasil — Prediksi Harga Komoditas', 'name' => 'Arif Setiawan', 'type' => 'hasil'],
            ['student_nim' => '2110101014', 'date' => today()->addDays(10), 'start' => '10:00', 'end' => '12:00', 'room' => 'A.202', 'status' => 'dijadwalkan', 'title' => 'Sidang Skripsi — Rekayasa Perangkat Lunak Adaptif', 'name' => 'Sinta Permata', 'type' => 'skripsi'],
        ];

        foreach ($rows as $row) {
            $date = $row['date']->toDateString();

            DefenseSession::updateOrCreate(
                ['student_nim' => $row['student_nim'], 'date' => $date],
                [
                    'room_id' => $rooms[$row['room']],
                    'created_by' => $admin->id,
                    'title' => $row['title'],
                    'student_name' => $row['name'],
                    'type' => $row['type'],
                    'start_time' => $row['start'],
                    'end_time' => $row['end'],
                    'status' => $row['status'],
                    'supervisor_name' => 'Dr. Ahmad Fauzi, M.Kom.',
                    'examiner_name' => 'Dr. Siti Nurhaliza, M.T.',
                    'notes' => null,
                ]
            );
        }
    }

    private function seedActivities(User $admin): void
    {
        if (ActivityLog::exists()) {
            return;
        }

        $room = Room::where('code', 'A.201')->first();
        $facility = Facility::where('code', 'FS-PRJ')->first();
        $schedule = DefenseSession::orderBy('date')->first();

        $activities = [
            ['auth.login', $admin, $admin, "{$admin->name} masuk ke sistem.", now()->subMinutes(30)],
            ['room.updated', $room, $admin, 'Memperbarui fasilitas dan kapasitas Ruang Sidang Utama.', now()->subHours(2)],
            ['facility.updated', $facility, $admin, 'Menandai proyektor Epson EB-X06 dalam kondisi baik.', now()->subHours(5)],
            ['schedule.created', $schedule, $admin, 'Menjadwalkan sidang skripsi di Ruang Sidang Utama.', now()->subDay()],
            ['building.created', Building::where('code', 'GD-C')->first(), $admin, 'Menambahkan Gedung Pascasarjana.', now()->subDays(2)],
        ];

        foreach ($activities as [$action, $subject, $user, $description, $createdAt]) {
            ActivityLog::create([
                'user_id' => $user?->id,
                'action' => $action,
                'subject_type' => $subject?->getMorphClass(),
                'subject_id' => $subject?->getKey(),
                'description' => $description,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }
}
