<?php

namespace App\Exports;

use App\Models\Klien;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class KliensExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function __construct(
        protected ?string $awal = null,
        protected ?string $akhir = null
    ) {}

    public function query()
    {
        $q = Klien::query()->withoutTrashed(); // hanya klien aktif (belum di-cancel)

        // filter tanggal: pakai tanggal_masuk kalau ada, fallback created_at
        if ($this->awal || $this->akhir) {
            $start = $this->awal  ? Carbon::parse($this->awal)->startOfDay() : Carbon::minValue();
            $end   = $this->akhir ? Carbon::parse($this->akhir)->endOfDay()   : Carbon::maxValue();

            $q->where(function ($x) use ($start, $end) {
                $x->whereBetween('tanggal_masuk', [$start, $end])
                  ->orWhere(function ($y) use ($start, $end) {
                      $y->whereNull('tanggal_masuk')
                        ->whereBetween('created_at', [$start, $end]);
                  });
            });
        }

        return $q->orderBy('created_at', 'asc');
    }

    public function headings(): array
    {
        return [
            // urutkan sesuai $fillable + timestamps
            'nama',
            'lokasi_lahan',
            'luas_lahan',
            'luas_bangunan',
            'kebutuhan_ruang',
            'sertifikat_url',
            'arah_mata_angin',
            'batas_keliling',
            'foto_eksisting_url',
            'konsep_bangunan',
            'referensi_url',
            'budget',
            'share_lokasi',
            'biaya_survei',
            'hoby',
            'aktivitas',
            'prioritas_ruang',
            'kendaraan',
            'estimasi_start',
            'target_user_kos',
            'fasilitas_kos',
            'layout_url',
            'desain_3d_url',
            'rab_boq_url',
            'gambar_kerja_url',
            'tanggal_masuk',
            'email',
            'alamat_tinggal',
            'no_hp',
            'kode_proyek',
            'kelas',
            'keterangan',
            'created_at',
            'updated_at',
        ];
    }

    public function map($r): array
    {
        // helper: konversi date ke format Excel jika ada
        $toExcelDate = function ($val) {
            if (empty($val)) return null;
            $c = $val instanceof Carbon ? $val : Carbon::parse($val);
            return ExcelDate::dateTimeToExcel($c);
        };

        // helper: url file storage
        $fileUrl = function ($path) {
            return $path ? (config('app.url') . Storage::url($path)) : null;
        };

        return [
            $r->nama,
            $r->lokasi_lahan,
            $r->luas_lahan,
            $r->luas_bangunan,
            $r->kebutuhan_ruang,
            $fileUrl($r->sertifikat),
            $r->arah_mata_angin,
            $r->batas_keliling,
            $fileUrl($r->foto_eksisting),
            $r->konsep_bangunan,
            $fileUrl($r->referensi),
            // budget sebagai angka (biar bisa diformat di Excel)
            $r->budget ? (float) $r->budget : 0,
            $r->share_lokasi,
            $r->biaya_survei,
            $r->hoby,
            $r->aktivitas,
            $r->prioritas_ruang,
            $r->kendaraan,
            $toExcelDate($r->estimasi_start),
            $r->target_user_kos,
            $r->fasilitas_kos,
            $fileUrl($r->layout),
            $fileUrl($r->desain_3d),
            $fileUrl($r->rab_boq),
            $fileUrl($r->gambar_kerja),
            $toExcelDate($r->tanggal_masuk ?: $r->created_at),
            $r->email,
            $r->alamat_tinggal,
            $r->no_hp,
            $r->kode_proyek,
            $r->kelas,
            $r->keterangan,
            $toExcelDate($r->created_at),
            $toExcelDate($r->updated_at),
        ];
    }

    public function columnFormats(): array
    {
        // Sesuaikan huruf kolom:
        // A  nama
        // B  lokasi_lahan
        // ...
        // L  budget
        // S  estimasi_start
        // Z  tanggal_masuk
        // AG created_at
        // AH updated_at
        return [
            'L'  => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // budget
            'S'  => NumberFormat::FORMAT_DATE_YYYYMMDD2,           // estimasi_start
            'Z'  => NumberFormat::FORMAT_DATE_YYYYMMDD2,           // tanggal_masuk
            'AG' => NumberFormat::FORMAT_DATE_YYYYMMDD2,           // created_at
            'AH' => NumberFormat::FORMAT_DATE_YYYYMMDD2,           // updated_at
        ];
    }
}
