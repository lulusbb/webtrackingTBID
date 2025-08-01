<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class ProfilController extends Controller
{
    public function index()
    {
        return view('profil');
    }
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, auth()->user()->password)) {
            return back()->with('error', 'Password lama salah');
        }

        $user = auth()->user();
        $user->password = Hash::make($request->password);
        $user->save();

        // ✅ ini yang bikin alert muncul
        return back()->with('success', 'Password berhasil diubah!');
    }
}
