<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Progress;
use App\Models\Bukti;

class AdminController extends Controller
{
    public function simpanProgress(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'status1' => 'nullable|string',
        'tanggal1' => 'nullable|date',
        'status2' => 'nullable|string',
        'tanggal2' => 'nullable|date',
        'status3' => 'nullable|string',
        'tanggal3' => 'nullable|date',
        'status4' => 'nullable|string',
        'tanggal4' => 'nullable|date',
        'status5' => 'nullable|string',
        'tanggal5' => 'nullable|date',
        'status6' => 'nullable|string',
        'tanggal6' => 'nullable|date',
        'status7' => 'nullable|string',
        'tanggal7' => 'nullable|date',
        'status8' => 'nullable|string',
        'tanggal8' => 'nullable|date',
    ]);
    
    $user = User::where('email', $request->email)->first();
    
    $progress = Progress::updateOrCreate(
        ['user_id' => $user->id],
        [
            'tanggal1' => $request->tanggal1,
            'status1' => $request->status1,
            'tanggal2' => $request->tanggal2,
            'status2' => $request->status2,
            'tanggal3' => $request->tanggal3,
            'status3' => $request->status3,
            'tanggal4' => $request->tanggal4,
            'status4' => $request->status4,
            'tanggal5' => $request->tanggal5,
            'status5' => $request->status5,
            'tanggal6' => $request->tanggal6,
            'status6' => $request->status6,
            'tanggal7' => $request->tanggal7,
            'status7' => $request->status7,
            'tanggal8' => $request->tanggal8,
            'status8' => $request->status8,        
        ]
    );
    // dd($user, $progress);

    // Arahkan ke halaman utama dengan data user + progress
    return back()->with('success', 'Progress berhasil disimpan!');

}
}
