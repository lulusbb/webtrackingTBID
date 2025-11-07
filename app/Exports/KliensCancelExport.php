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

class KliensCancelExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    public function __construct(
        protected ?string $awal = null,
        protected ?string $akhir = null
    ) {}

    public function query()
    {
        $q = Klien::onlyTrashed();

        // Filter by deleted_at (tanggal cancel)
        if ($this->awal || $this->akhir) {
            $start = $this->awal  ? Carbon::parse($this->awal)->startOfDay() : Carbon::minValue();
            $end   = $this->akhir ? Carbon::parse($this->akhir)->endOfDay()   : Carbon::maxValue();
            $q->whereBetween('deleted_at', [$start, $end]);
        }

        return $q->orderBy('deleted_at', 'asc');
    }

    public function headings(): array
    {
        return [
            // persis urutan $fillable + convert kolom file menjadi *_url
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
        ];
    }

    public function map($r): array
    {
        $toExcelDate = function ($val) {
            if (empty($val)) return null;
            $c = $val instanceof Carbon ? $val : Carbon::parse($val);
            return ExcelDate::dateTimeToExcel($c);
        };

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
        ];
    }

    public function columnFormats(): array
    {
        // budget = kolom ke-12 => 'L'
        // estimasi_start = kolom ke-19 => 'S'
        // tanggal_masuk  = kolom ke-26 => 'Z'
        return [
            'L' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
            'S' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
            'Z' => NumberFormat::FORMAT_DATE_YYYYMMDD2,
        ];
    }
}
