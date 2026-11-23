<?php

namespace Database\Seeders;

use App\Enums\Gender;
use App\Enums\JenisIzinTinggal;
use App\Models\Abg;
use App\Models\Affidavit;
use App\Models\LaporanIzinTinggal;
use App\Models\Penerbitan;
use App\Models\Permohonan;
use App\Models\RealisasiPnbp;
use App\Models\Wna;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now('Asia/Jakarta')->startOfDay();
        $currentYear = (int) $now->year;
        $kabupatens = ['Ponorogo', 'Pacitan', 'Trenggalek'];

        $countries = [
            'China', 'Malaysia', 'Korea Selatan', 'Jepang', 'Amerika Serikat',
            'Yaman', 'Filipina', 'Singapura', 'Australia', 'Jerman',
            'Inggris', 'Thailand', 'Belanda', 'India', 'Prancis',
        ];

        // ---------------------------------------------------------------------
        // 1. Data WNA (~50 data, varied for early warning & 4 permit categories)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy WNA...');
        $wnaCount = 50;
        $wnaExisting = Wna::count();
        if ($wnaExisting < $wnaCount) {
            $toCreate = $wnaCount - $wnaExisting;
            for ($i = 0; $i < $toCreate; $i++) {
                $idx = $wnaExisting + $i + 1;
                $gender = fake()->randomElement([Gender::LakiLaki, Gender::Perempuan]);
                $kab = fake()->randomElement($kabupatens);
                $country = fake()->randomElement($countries);
                $permit = fake()->randomElement([JenisIzinTinggal::ITAS, JenisIzinTinggal::ITAP, JenisIzinTinggal::VOA, JenisIzinTinggal::ITK]);

                // Determine masa_berlaku distribution:
                // 10% kedaluwarsa, 10% merah (1-14 days), 10% kuning (15-30 days), 15% biru (31-90 days), 55% normal aktif (>90 days)
                $randCategory = fake()->numberBetween(1, 100);
                if ($randCategory <= 10) {
                    // Kedaluwarsa (-5 to -90 days)
                    $masaBerlaku = $now->copy()->subDays(fake()->numberBetween(5, 90))->toDateString();
                } elseif ($randCategory <= 20) {
                    // Merah (1 to 14 days)
                    $masaBerlaku = $now->copy()->addDays(fake()->numberBetween(1, 14))->toDateString();
                } elseif ($randCategory <= 30) {
                    // Kuning (15 to 30 days)
                    $masaBerlaku = $now->copy()->addDays(fake()->numberBetween(15, 30))->toDateString();
                } elseif ($randCategory <= 45) {
                    // Biru (31 to 90 days)
                    $masaBerlaku = $now->copy()->addDays(fake()->numberBetween(31, 90))->toDateString();
                } else {
                    // Normal aktif (>90 days to 1 year)
                    $masaBerlaku = $now->copy()->addDays(fake()->numberBetween(95, 365))->toDateString();
                }

                Wna::create([
                    'no_register' => sprintf('REG-WNA-%05d', $idx),
                    'no_paspor' => strtoupper(fake()->lexify('?').fake()->numerify('#######')),
                    'nama' => fake()->name(),
                    'jenis_kelamin' => $gender,
                    'kewarganegaraan' => $country,
                    'jenis_izin_tinggal' => $permit,
                    'izin_tinggal_ke' => fake()->randomElement(['1 (Pertama)', '2 (Kedua)', '3 (Ketiga)']),
                    'masa_berlaku' => $masaBerlaku,
                    'penjamin' => fake()->company(),
                    'alamat_penjamin' => fake()->address(),
                    'nomor_telepon' => fake()->phoneNumber(),
                    'kabupaten' => $kab,
                    'keterangan' => fake()->optional(0.3)->sentence(),
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 2. Data Pendaftaran ABG (~35 data, varied for early warning ABG)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy Pendaftaran ABG...');
        $abgCount = 35;
        $abgExisting = Abg::count();
        if ($abgExisting < $abgCount) {
            $toCreateAbg = $abgCount - $abgExisting;
            for ($i = 0; $i < $toCreateAbg; $i++) {
                $idx = $abgExisting + $i + 1;
                $gender = fake()->randomElement([Gender::LakiLaki, Gender::Perempuan]);
                $kab = fake()->randomElement($kabupatens);
                $partnerCountry = fake()->randomElement(['Malaysia', 'Singapura', 'Jepang', 'Australia', 'Belanda', 'Amerika Serikat', 'Jerman', 'Korea Selatan']);

                // Tanggal Lahir distribution:
                // ~50% normal anak (< 17 th)
                // ~15% menjelang 18 th (17.5 - 18 th)
                // ~10% normal usia 19 th
                // ~15% menjelang 21 th (20.5 - 21 th)
                // ~10% lewat 21 th (kedaluwarsa)
                $randAge = fake()->numberBetween(1, 100);
                if ($randAge <= 15) {
                    // Menjelang 18 (17.5 to 18 th) -> lahir 18 tahun lalu + 10 s/d 160 hari
                    $tglLahir = $now->copy()->subYears(18)->addDays(fake()->numberBetween(10, 160))->toDateString();
                } elseif ($randAge <= 30) {
                    // Menjelang 21 (20.5 to 21 th) -> lahir 21 tahun lalu + 10 s/d 160 hari
                    $tglLahir = $now->copy()->subYears(21)->addDays(fake()->numberBetween(10, 160))->toDateString();
                } elseif ($randAge <= 40) {
                    // Lewat 21 th (kedaluwarsa) -> lahir > 21 th lalu (-20 s/d -400 hari)
                    $tglLahir = $now->copy()->subYears(21)->subDays(fake()->numberBetween(20, 400))->toDateString();
                } elseif ($randAge <= 50) {
                    // Usia 19 th (normal dual citizenship)
                    $tglLahir = $now->copy()->subYears(19)->subDays(fake()->numberBetween(20, 150))->toDateString();
                } else {
                    // Anak-anak normal (usia 2 - 16 th)
                    $tglLahir = $now->copy()->subYears(fake()->numberBetween(2, 16))->subDays(fake()->numberBetween(1, 300))->toDateString();
                }

                Abg::create([
                    'no_register' => sprintf('REG-ABG-%05d', $idx),
                    'nama' => fake()->name(),
                    'tempat_lahir' => fake()->city(),
                    'tanggal_lahir' => $tglLahir,
                    'jenis_kelamin' => $gender,
                    'kewarganegaraan' => 'Indonesia / '.$partnerCountry,
                    'kabupaten' => $kab,
                    'nama_ayah' => fake()->name('male'),
                    'kewarganegaraan_ayah' => $partnerCountry,
                    'nama_ibu' => fake()->name('female'),
                    'kewarganegaraan_ibu' => 'Indonesia',
                    'nomor_telepon' => fake()->phoneNumber(),
                    'alamat' => fake()->address(),
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 3. Data Pendaftaran Affidavit (~30 data)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy Pendaftaran Affidavit...');
        $affCount = 30;
        $affExisting = Affidavit::count();
        if ($affExisting < $affCount) {
            $toCreateAff = $affCount - $affExisting;
            for ($i = 0; $i < $toCreateAff; $i++) {
                $idx = $affExisting + $i + 1;
                $gender = fake()->randomElement([Gender::LakiLaki, Gender::Perempuan]);
                $kab = fake()->randomElement($kabupatens);
                $partnerCountry = fake()->randomElement(['Singapura', 'Malaysia', 'Australia', 'Jepang', 'Belanda', 'Amerika Serikat']);

                Affidavit::create([
                    'no_register' => sprintf('REG-AFF-%05d', $idx),
                    'no_paspor' => strtoupper(fake()->lexify('?').fake()->numerify('#######')),
                    'nama' => fake()->name(),
                    'tempat_lahir' => fake()->city(),
                    'tanggal_lahir' => $now->copy()->subYears(fake()->numberBetween(2, 16))->subDays(fake()->numberBetween(1, 300))->toDateString(),
                    'jenis_kelamin' => $gender,
                    'kewarganegaraan' => 'Indonesia / '.$partnerCountry,
                    'kabupaten' => $kab,
                    'masa_berlaku' => $now->copy()->addMonths(fake()->numberBetween(6, 36))->toDateString(),
                    'nama_ayah' => fake()->name('male'),
                    'kewarganegaraan_ayah' => $partnerCountry,
                    'nama_ibu' => fake()->name('female'),
                    'kewarganegaraan_ibu' => 'Indonesia',
                    'nomor_telepon' => fake()->phoneNumber(),
                    'alamat' => fake()->address(),
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 4. Data Permohonan Keimigrasian (~40 data, spread across current year)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy Permohonan Keimigrasian...');
        $permohonanCount = 40;
        $permohonanExisting = Permohonan::count();
        if ($permohonanExisting < $permohonanCount) {
            $toCreatePerm = $permohonanCount - $permohonanExisting;
            $layananOptions = [
                'Perpanjangan ITAS', 'Pemberian ITAS Baru', 'Perpanjangan ITK',
                'Alih Status ITAP', 'Pemberian EPO', 'Pendaftaran ABG', 'Penerbitan Affidavit',
            ];

            for ($i = 0; $i < $toCreatePerm; $i++) {
                $gender = fake()->randomElement(['male', 'female']);
                $name = fake()->name($gender);
                $country = fake()->randomElement($countries);
                $kab = fake()->randomElement($kabupatens);
                $layanan = fake()->randomElement($layananOptions);
                $randomMonth = fake()->numberBetween(1, max(1, (int) $now->month));
                $randomDay = fake()->numberBetween(1, 28);
                $tgl = Carbon::create($currentYear, $randomMonth, $randomDay)->toDateString();

                Permohonan::create([
                    'no_paspor' => strtoupper(fake()->lexify('?').fake()->numerify('#######')),
                    'nama' => $name,
                    'kewarganegaraan' => $country,
                    'layanan' => $layanan,
                    'tanggal' => $tgl,
                    'kabupaten' => $kab,
                    'permohonan' => sprintf('Permohonan %s pemohon %s warga negara %s di wilayah %s', strtolower($layanan), $name, $country, $kab),
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 5. Data Penerbitan Dokumen Keimigrasian (~35 data)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy Penerbitan Dokumen...');
        $penerbitanCount = 35;
        $penerbitanExisting = Penerbitan::count();
        if ($penerbitanExisting < $penerbitanCount) {
            $toCreatePenerbitan = $penerbitanCount - $penerbitanExisting;
            $penerbitanLayanan = [
                'Penerbitan ITAS Elektronik', 'Penerbitan Kartu ITAP',
                'Penerbitan Kartu Affidavit', 'Pemberian Izin Tinggal Kunjungan',
                'Penerbitan Surat Keterangan Imigrasi',
            ];

            for ($i = 0; $i < $toCreatePenerbitan; $i++) {
                $name = fake()->name();
                $country = fake()->randomElement($countries);
                $kab = fake()->randomElement($kabupatens);
                $layanan = fake()->randomElement($penerbitanLayanan);
                $randomMonth = fake()->numberBetween(1, max(1, (int) $now->month));
                $randomDay = fake()->numberBetween(1, 28);
                $tgl = Carbon::create($currentYear, $randomMonth, $randomDay)->toDateString();

                Penerbitan::create([
                    'no_paspor' => strtoupper(fake()->lexify('?').fake()->numerify('#######')),
                    'nama' => $name,
                    'kewarganegaraan' => $country,
                    'layanan' => $layanan,
                    'tanggal' => $tgl,
                    'kabupaten' => $kab,
                    'keterangan' => sprintf('Dokumen %s telah disetujui dan diterbitkan pada tanggal %s', $layanan, $tgl),
                ]);
            }
        }

        // ---------------------------------------------------------------------
        // 6. Data Laporan Izin Tinggal (12 s/d 24 bulan unik)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy Laporan Izin Tinggal...');
        $kategoriLaporan = ['Izin Tinggal Kunjungan', 'Izin Tinggal Terbatas', 'Izin Tinggal Tetap'];
        $layananLaporan = ['Pemberian Baru', 'Perpanjangan', 'Alih Status'];

        // Sediakan data untuk 12 bulan terakhir
        for ($m = 0; $m < 12; $m++) {
            $bulanDate = $now->copy()->subMonths($m)->startOfMonth()->toDateString();
            LaporanIzinTinggal::updateOrCreate(
                ['bulan' => $bulanDate],
                [
                    'kategori' => $kategoriLaporan[$m % count($kategoriLaporan)],
                    'layanan' => $layananLaporan[$m % count($layananLaporan)],
                    'disetujui_l' => fake()->numberBetween(15, 60),
                    'disetujui_p' => fake()->numberBetween(10, 45),
                    'ditolak_l' => fake()->numberBetween(0, 4),
                    'ditolak_p' => fake()->numberBetween(0, 3),
                    'keterangan' => sprintf('Rekapitulasi berkas keimigrasian periode bulan %s', Carbon::parse($bulanDate)->translatedFormat('F Y')),
                ]
            );
        }

        // ---------------------------------------------------------------------
        // 7. Data Realisasi PNBP (12 bulan tahun berjalan, unik)
        // ---------------------------------------------------------------------
        $this->command?->info('Menyemai data dummy Realisasi PNBP...');
        for ($month = 1; $month <= 12; $month++) {
            $bulanDate = Carbon::create($currentYear, $month, 1)->toDateString();
            $target = fake()->randomFloat(2, 90000000, 200000000);
            // Realisasi antara 80% s/d 118% dari target
            $multiplier = fake()->randomFloat(4, 0.82, 1.18);
            $realisasi = round($target * $multiplier, 2);

            RealisasiPnbp::updateOrCreate(
                ['bulan' => $bulanDate],
                [
                    'target' => $target,
                    'realisasi' => $realisasi,
                    'keterangan' => sprintf('Laporan penerimaan negara bukan pajak periode %s %d', Carbon::create($currentYear, $month, 1)->translatedFormat('F'), $currentYear),
                ]
            );
        }

        $this->command?->info('Seluruh data dummy berhasil disemai ke dalam database!');
    }
}
