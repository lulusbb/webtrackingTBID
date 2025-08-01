<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
        public function index()
    {
        $role = auth()->user()->role;

        return redirect()->route($role . '.dashboard');
    }

    public function admin()
    {
        return view('dashboards.admin');
    }

    public function marketing()
    {
        return view('dashboards.marketing');
    }

    public function studio()
    {
        return view('dashboards.studio');
    }

    public function project()
    {
        return view('dashboards.project');
    }
}
