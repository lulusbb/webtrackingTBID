<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StudioController extends Controller
{
        public function index()
    {
        return view('dashboards.studio');
    }

        public function dashboard() {
        return view('studio.dashboard');
    }

    public function klienSurvei() {
        return view('studio.kliensurvei');
    }

    public function denahMoodboard() {
        return view('studio.denah_moodboard');
    }

    public function interior3D() {
        return view('studio.interior3d');
    }

    public function mepSpek() {
        return view('studio.mep_spek');
    }

    public function tahapAkhir() {
        return view('studio.tahap_akhir');
    }
}
