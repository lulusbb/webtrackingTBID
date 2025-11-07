<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\Project; // jika ada model projek

class CeoController extends Controller
{
    // Dashboard Utama versi CEO (boleh re-use view yang sudah ada)
    public function dashboard()
    {
        // return view('admin.dashboard-utama'); // jika sudah ada
        return view('ceo.dashboard');            // fallback view baru di langkah 5
    }

    // Daftar Proyek Selesai (read-only)
    public function proyekSelesai()
    {
        // Contoh query — sesuaikan nama model/kolom Anda:
        // $projects = Project::where('status', 'selesai')->latest()->paginate(20);
        // return view('ceo.proyek-selesai', compact('projects'));

        return view('ceo.proyek-selesai'); // fallback view kosong, langkah 5
    }
}
