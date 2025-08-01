<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProjectController extends Controller
{
        public function index()
    {
        return view('dashboards.project');
    }

        public function dashboard() {
        return view('project.dashboard');
    }

    public function struktur3D() {
        return view('project.struktur3d');
    }

    public function plumbing() {
        return view('project.plumbing');
    }

    public function rab() {
        return view('project.rab');
    }
        public function mou() {
        return view('project.mou');
    }
        public function proyek() {
        return view('project.proyek');
    }
}
