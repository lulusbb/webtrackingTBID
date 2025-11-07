<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon as C;

class SettingsController extends Controller
{
    /* ===================== Konfigurasi umum ===================== */

    /** Tabel yang tidak boleh tampil/di-reset dari UI */
    protected array $protectedTables = [
        'users',
        'migrations',
        'failed_jobs',
        'password_reset_tokens',
        'personal_access_tokens',
        'jobs',
        'job_batches',
    ];

    /** Daftar kartu/indikator yang bisa diatur di Pengaturan */
    protected function kpiCards(): array
    {
        return [
            'klien_masuk'  => 'Klien Masuk',
            'klien_survei' => 'Klien Survei',
            'denah'        => 'Denah & Moodboard',
            'exterior'     => '3D Desain',
            'mep'          => 'MEP & Spek',
            'serter'       => 'Serter Desain',
            'struktur3d'   => '3D Struktur',
            'skema'        => 'Skema Plumbing',
            'rab'          => 'RAB',
            'mou'          => 'MOU',
            'proyek'       => 'Proyek Berjalan',
        ];
    }

    /* ===================== KPI Thresholds ===================== */

    protected function defaults(): array
    {
        // Default agar halaman langsung jalan.
        // Khusus "klien_masuk" contoh default kuning 20–30; lainnya 10–20.
        $base1020 = ['red_lt'=>10, 'yellow_min'=>10, 'yellow_max'=>20, 'green_gt'=>20];

        return [
            'klien_masuk'  => ['red_lt'=>10, 'yellow_min'=>20, 'yellow_max'=>30, 'green_gt'=>30],
            'klien_survei' => $base1020,
            'denah'        => $base1020,
            'exterior'     => $base1020,
            'mep'          => $base1020,
            'serter'       => $base1020,
            'struktur3d'   => $base1020,
            'skema'        => $base1020,
            'rab'          => $base1020,
            'mou'          => $base1020,
            'proyek'       => $base1020,
        ];
    }

    protected function read(): array
    {
        try {
            $row = DB::table('settings')->where('key','kpi_thresholds')->first();
            if (!$row) return $this->defaults();

            $val = is_string($row->value) ? json_decode($row->value, true) : (array) $row->value;
            if (!is_array($val)) $val = [];

            // Pastikan semua key ada (merge dengan defaults)
            return array_replace_recursive($this->defaults(), $val);
        } catch (\Throwable $e) {
            return $this->defaults();
        }
    }

    protected function save(array $val): void
    {
        DB::table('settings')->updateOrInsert(
            ['key'=>'kpi_thresholds'],
            [
                'value'      => json_encode($val, JSON_UNESCAPED_UNICODE),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    public function index()
    {
        $cards      = $this->kpiCards();
        $thresholds = $this->read();
        $resetInfos = $this->buildResetInfos();

        return view('admin.settings.index', compact('thresholds','resetInfos','cards'));
    }

public function updateThresholds(Request $r)
{
    $cards = $this->kpiCards();

    // 1) Rules & attribute names (dinamis untuk SEMUA kartu)
    $rules = $nice = [];
    foreach ($cards as $key => $label) {
        $rules["{$key}.red_lt"]      = 'required|integer|min:0';
        $rules["{$key}.yellow_min"]  = 'required|integer|min:0';
        $rules["{$key}.yellow_max"]  = "required|integer|min:0|gte:{$key}.yellow_min";
        $rules["{$key}.green_gt"]    = 'required|integer|min:0';

        $nice["{$key}.red_lt"]       = "{$label} — Merah (<)";
        $nice["{$key}.yellow_min"]   = "{$label} — Kuning (min)";
        $nice["{$key}.yellow_max"]   = "{$label} — Kuning (max)";
        $nice["{$key}.green_gt"]     = "{$label} — Hijau (>)";
    }

    // 2) Validasi dasar
    $data = validator($r->all(), $rules, [], $nice)->validate();

    // 3) Validasi urutan (inklusif) untuk tiap kartu
    foreach ($cards as $key => $label) {
        if (!isset($data[$key])) continue;

        $v     = $data[$key];
        $red   = (int)($v['red_lt'] ?? 0);
        $ymin  = (int)($v['yellow_min'] ?? 0);
        $ymax  = (int)($v['yellow_max'] ?? 0);
        $green = (int)($v['green_gt'] ?? 0);

        // Harus: Merah ≤ Kuning(min) ≤ Kuning(max) ≤ Hijau
        if ($red > $ymin || $ymin > $ymax || $ymax > $green) {
            return back()
                ->withErrors(["{$key}.red_lt" => "Urutan {$label} harus: Merah ≤ Kuning(min) ≤ Kuning(max) ≤ Hijau."])
                ->withInput();
        }
    }

    // 4) Merge dengan existing supaya key lain tetap ada
    $save = $this->read();
    foreach ($cards as $key => $_) {
        $save[$key] = $data[$key] ?? ($save[$key] ?? []);
    }

    // 5) Simpan
    $this->save($save);
    return redirect()->route('settings.index')->with('ok', 'Indikator berhasil disimpan.');
}


    /* ===================== Reset DB (UI data) ===================== */

    /** Label khusus beberapa tabel agar tampil rapi di UI. Bebas kamu ubah. */
    protected function resetTableMap(): array
    {
        return [
            'settings'        => 'Settings',
            'kliens'          => 'Klien Masuk',
            'klienfixsurvei'  => 'Klien Survei',
            'denahs'          => 'Denah & Moodboard',
            'exteriors'       => '3D Desain',
            'meps'            => 'MEP & Spek',
            'tahap_akhirs'    => 'Serter Desain',
            'struktur_3ds'    => '3D Struktur',
            'skemas'          => 'Skema Plumbing',
            'rabs'            => 'RAB',
            'mous'            => 'MOU',
            'proyekjalans'    => 'Proyek Berjalan',
        ];
    }

    /** Pilih kolom tanggal yang ada pada suatu tabel. */
    protected function pickDateColumn(string $table): ?string
    {
        foreach (['tanggal_masuk','tgl_masuk','tanggal','date','created_at','updated_at'] as $c) {
            if (Schema::hasColumn($table, $c)) return $c;
        }
        return null;
    }

    /** Ambil semua base tables dari database. */
    protected function listAllTables(): array
    {
        $rows = DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"');
        $tables = [];
        foreach ($rows as $r) {
            $arr = (array) $r;
            $tables[] = reset($arr); // kolom pertama = nama tabel
        }
        return $tables;
    }

    /** Data untuk menampilkan daftar tabel di blade. */
    protected function buildResetInfos(): array
    {
        $custom  = $this->resetTableMap();
        $exclude = $this->protectedTables;

        $out = [];
        foreach ($this->listAllTables() as $table) {
            if (in_array($table, $exclude, true)) continue;
            if (!Schema::hasTable($table)) continue;

            $dateCol = $this->pickDateColumn($table);
            $count   = (int) DB::table($table)->count();
            $min     = $dateCol ? DB::table($table)->min($dateCol) : null;
            $max     = $dateCol ? DB::table($table)->max($dateCol) : null;

            $label = $custom[$table] ?? Str::of($table)->replace('_',' ')->title();

            $out[] = [
                'table'   => $table,
                'label'   => $label,
                'count'   => $count,
                'dateCol' => $dateCol,
                'min'     => $min,
                'max'     => $max,
            ];
        }

        usort($out, fn($a,$b) => strcasecmp($a['label'], $b['label']));
        return $out;
    }

    /* ===================== Reset DB (aksi eksekusi) ===================== */

    public function resetDatabase(Request $r)
    {
        // Normalisasi input
        $tables      = array_values(array_filter((array) $r->input('tables', [])));
        $truncateAll = (bool) $r->boolean('truncate_all');

        // terima beberapa kemungkinan nama field konfirmasi
        $confirmText = strtoupper(trim((string) (
            $r->input('confirm_text') ??
            $r->input('confirm') ??
            $r->input('confirm-text') ?? ''   // kalau id dipakai sebagai name
        )));

        // terima beberapa nama field tanggal
        $startStr = $r->input('start') ?? $r->input('start_date');
        $endStr   = $r->input('end')   ?? $r->input('end_date');

        if ($confirmText !== 'RESET') {
            return back()->with('warn', 'Ketik "RESET" untuk konfirmasi.')->withInput();
        }
        if (empty($tables)) {
            return back()->with('warn','Pilih minimal satu tabel.')->withInput();
        }

        // Proteksi tabel sensitif (server-side)
        $tables = array_values(array_filter($tables, function ($t) {
            return !in_array($t, $this->protectedTables, true) && Schema::hasTable($t);
        }));
        if (empty($tables)) {
            return back()->with('warn','Tidak ada tabel yang valid untuk direset.')->withInput();
        }

        // Parse tanggal (opsional)
        $start = $startStr ? C::parse($startStr)->startOfDay() : null;
        $end   = $endStr   ? C::parse($endStr)->endOfDay()   : null;
        if ($start && $end && $end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        try {
            DB::transaction(function () use ($tables, $truncateAll, $start, $end) {
                Schema::disableForeignKeyConstraints();

                foreach ($tables as $table) {
                    if ($truncateAll) {
                        // TRUNCATE (lebih cepat & reset AUTO_INCREMENT)
                        DB::table($table)->truncate();
                        continue;
                    }

                    // Hapus by range tanggal jika kolom tanggal ada & range lengkap
                    $dateCol = $this->pickDateColumn($table);
                    if ($dateCol && $start && $end) {
                        DB::table($table)
                            ->where($dateCol, '>=', $start)
                            ->where($dateCol, '<=', $end)
                            ->delete();
                    } else {
                        // Tidak ada kolom tanggal / range tidak lengkap → hapus semua isi
                        DB::table($table)->delete();
                    }
                }

                Schema::enableForeignKeyConstraints();
            });

            return back()->with('ok','Reset database berhasil dijalankan.');
        } catch (\Throwable $e) {
            try { Schema::enableForeignKeyConstraints(); } catch (\Throwable $ignored) {}
            return back()->with('warn','Reset gagal: '.$e->getMessage());
        }
    }
}
