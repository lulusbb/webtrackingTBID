<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Klien;
use Illuminate\Support\Facades\Storage;

class MarketingController extends Controller
{


    public function dashboard()
    {
        return view('marketing.dashboard');
    }

        public function klienCreate()
    {
        return view('marketing.create');
    }

    public function klien()
    {
        $kliens = Klien::all(); // ambil semua data dari database
        return view('marketing.klien', compact('kliens')); // kirim ke view
    }

public function klienStore(Request $request)
{
    $data = $request->validate([
        'nama' => 'required|string|max:255',
        'lokasi_lahan' => 'nullable|string',
        'luas_lahan' => 'nullable|string',
        'luas_bangunan' => 'nullable|string',
        'kebutuhan_ruang' => 'nullable|string',
        'sertifikat' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120',
        'arah_mata_angin' => 'nullable|string',
        'batas_keliling' => 'nullable|string',
        'foto_eksisting' => 'nullable|file|mimes:jpg,jpeg,png|max:5120',
        'konsep_bangunan' => 'nullable|string',
        'referensi' => 'nullable|file|mimes:jpg,jpeg,png,pdf,zip|max:5120',
        'budget' => 'nullable|string',
        'share_lokasi' => 'nullable|string',
        'biaya_survei' => 'nullable|string',
        'hoby' => 'nullable|string',
        'aktivitas' => 'nullable|string',
        'prioritas_ruang' => 'nullable|string',
        'kendaraan' => 'nullable|string',
        'estimasi_start' => 'nullable|date',
        'target_user_kos' => 'nullable|string',
        'fasilitas_kos' => 'nullable|string',
        'layout' => 'nullable|file|mimes:pdf,zip|max:5120',
        'desain_3d' => 'nullable|file|mimes:pdf,zip|max:5120',
        'rab_boq' => 'nullable|file|mimes:pdf,zip|max:5120',
        'gambar_kerja' => 'nullable|file|mimes:pdf,zip|max:5120',
        'tanggal_masuk' => 'nullable|date',
    ]);

    foreach (['sertifikat', 'foto_eksisting', 'referensi', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field) {
        if ($request->hasFile($field)) {
            $data[$field] = $request->file($field)->store("klien/{$field}", 'public');
        }
    }

    Klien::create($data);

    return redirect()->route('marketing.klien.index')->with('success', 'Data klien berhasil ditambahkan.');

}

public function klienEdit($id)
{
    $klien = Klien::findOrFail($id);
    return view('marketing.edit', compact('klien'));
}

public function update(Request $request, $id)
{
    $klien = Klien::findOrFail($id);

    $validated = $request->validate([
        'nama' => 'required|string|max:255',
        'lokasi_lahan' => 'nullable|string',
        'luas_lahan' => 'nullable|string',
        'luas_bangunan' => 'nullable|string',
        'arah_mata_angin' => 'nullable|string',
        'budget' => 'nullable|string',
        'share_lokasi' => 'nullable|string',
        'biaya_survei' => 'nullable|string',
        'hoby_klien' => 'nullable|string',
        'jenis_jumlah_kendaraan' => 'nullable|string',
        'konsep_bangunan' => 'nullable|string',
        'tanggal_masuk' => 'nullable|date',
        'estimasi_start' => 'nullable|date',
        'kebutuhan_ruang' => 'nullable|string',
        'batas_keliling_bangunan' => 'nullable|string',
        'aktivitas_klien' => 'nullable|string',
        'prioritas_ruang' => 'nullable|string',
        'target_user_kos' => 'nullable|string',
        'fasilitas_umum_kos' => 'nullable|string',

        // file fields
        'referensi' => 'nullable|file',
        'sertifikat' => 'nullable|file',
        'foto_eksisting' => 'nullable|file',
        'layout' => 'nullable|file',
        'desain_3d' => 'nullable|file',
        'rab_boq' => 'nullable|file',
        'gambar_kerja' => 'nullable|file',
    ]);

    // handle file uploads
    foreach (['referensi', 'sertifikat', 'foto_eksisting', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $file) {
        if ($request->hasFile($file)) {
            $validated[$file] = $request->file($file)->store("klien/{$klien->id}", 'public');
        }
    }

    $klien->update($validated);

    return redirect()->route('marketing.klien.index')->with('success', 'Data klien berhasil diperbarui.');
}

    public function klienUpdate(Request $request, $id)
    {
        $klien = Klien::findOrFail($id);

        $data = $request->except('_token', '_method');

        foreach (['sertifikat', 'foto_eksisting', 'referensi', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field) {
            if ($request->hasFile($field)) {
                if ($klien->$field && Storage::disk('public')->exists($klien->$field)) {
                    Storage::disk('public')->delete($klien->$field);
                }
                $data[$field] = $request->file($field)->store("klien/{$field}", 'public');
            }
        }

        $klien->update($data);

        return redirect()->route('marketing.klien.index')->with('success', 'Data klien berhasil diperbarui.');
    }

    public function klienDestroy($id)
    {
        $klien = Klien::findOrFail($id);

        foreach (['sertifikat', 'foto_eksisting', 'referensi', 'layout', 'desain_3d', 'rab_boq', 'gambar_kerja'] as $field) {
            if ($klien->$field && Storage::disk('public')->exists($klien->$field)) {
                Storage::disk('public')->delete($klien->$field);
            }
        }

        $klien->delete();

        return redirect()->route('marketing.klien.index')->with('success', 'Data klien berhasil dihapus.');
    }

    public function laporan()
    {
        return view('marketing.laporan');
    }

    public function adminDashboard()
    {
        return view('admin.dashboard');
    }
}
