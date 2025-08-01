<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
        public function index()
    {
        return view('dashboards.admin');
    }

        public function akun() {
        return view('admin.akun');
    }
}
