<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Progress;
use App\Models\Bukti;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->only([
            'index', 'showBukti', 'admin', 'upload', 'updateProgress'
        ]);
    }

    public function masuk()
    {
        return view('login');
    }

    public function logika_masuk(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'no_hp' => 'required|string|regex:/^[0-9]{10,15}$/',
        ]);
        
        // Debug input
        \Log::info('Login attempt', [
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'is_admin_number' => $request->no_hp === '081233456666'
        ]);
        
        // Cari atau buat user berdasarkan email
        $user = User::firstOrCreate(
            ['email' => $request->email],
            [
                'no_hp' => $request->no_hp,
                'role'  => 'user', // Default role
            ]
        );

        \Log::info('User found/created', [
            'user_id' => $user->id,
            'email' => $user->email,
            'role_before_update' => $user->role,
            'no_hp_before_update' => $user->no_hp,
            'was_recently_created' => $user->wasRecentlyCreated,
            'input_no_hp' => $request->no_hp
        ]);

        // LOGIC BARU: Selalu update no_hp dan role berdasarkan input saat ini
        // Karena no_hp menentukan role, kita harus update setiap login
        
        $user->no_hp = $request->no_hp; // Update no_hp dengan input terbaru
        
        // Tentukan role berdasarkan no_hp
        if ($request->no_hp === '081233456666') {
            $user->role = 'admin';
            \Log::info('Setting role to admin - admin number detected');
        } else {
            $user->role = 'user';
            \Log::info('Setting role to user - regular number');
        }
        
        // Save perubahan
        $user->save();
        
        \Log::info('User updated', [
            'user_id' => $user->id,
            'role_after_update' => $user->role,
            'no_hp_after_update' => $user->no_hp,
            'will_redirect_to_admin' => $user->role === 'admin'
        ]);

        // Login user
        Auth::login($user);
        $request->session()->regenerate();

        // Debug auth user
        \Log::info('Auth user after login', [
            'auth_user_id' => Auth::id(),
            'auth_user_role' => Auth::user()->role,
            'auth_user_no_hp' => Auth::user()->no_hp
        ]);

        // Redirect berdasarkan role
        if ($user->role === 'admin') {
            \Log::info('Redirecting to admin.index');
            return redirect()->route('admin.index');
        } else {
            \Log::info('Redirecting to tracker.index');
            return redirect()->route('tracker.index');
        }
    }

    public function admin()
    {
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access');
        }

        $user      = Auth::user();
        $progress  = Progress::firstOrCreate(['user_id' => $user->id]);
        $buktiList = Bukti::where('user_id', $user->id)->get()->keyBy('step');

        return view('admin', compact('user', 'progress', 'buktiList'));
    }

    public function index()
    {
        if (Auth::user()->role === 'admin') {
            abort(403, 'Admin tidak boleh akses order-tracker.');
        }

        $user      = Auth::user();
        $progress  = Progress::firstOrCreate(['user_id' => $user->id]);
        $buktiList = Bukti::where('user_id', $user->id)->get()->keyBy('step');

        // Logging tambahan untuk memastikan data diambil
        \Log::info('BuktiList in index', [
            'buktiList' => $buktiList->toArray(),
            'step_8' => $buktiList->get(8) ? $buktiList->get(8)->toArray() : null,
        ]);

        $stages = [
            'konsultasi'     => 'gambar1.png',
            'pembayaran'     => 'gambar4.png',
            'desain label'   => 'gambar5.png',
            'produksi'       => 'gambar7.png',
            'pengemasan'     => 'gambar9.png',
            'pengiriman'     => 'gambar10.png',
            'foto dan video' => 'gambar8.png',
            'kesimpulan'     => 'gambar2.png',
        ];

        return view('utama', compact('progress', 'stages', 'buktiList'));
    }

    public function showBukti($keterangan)
    {
        $bukti = Auth::user()->bukti()->where('keterangan', $keterangan)->first();
        return view('tracker.bukti', compact('bukti', 'keterangan'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'bukti'      => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $path = $request->file('bukti')->store('bukti', 'public');

        Bukti::create([
            'user_id'    => Auth::id(),
            'path'       => $path,
            'status'     => 'selesai',
            'tanggal'    => now(),
            'step'       => 1,
            'keterangan' => $request->input('keterangan') ?? 'konsultasi',
        ]);

        return back()->with('success', 'Bukti berhasil diunggah.');
    }

    public function updateProgress(Request $request)
    {
        $user     = Auth::user();
        $progress = Progress::firstOrCreate(['user_id' => $user->id]);

        $tahapan = [
            1 => 'konsultasi',    2 => 'pembayaran',
            3 => 'desain label',  4 => 'produksi',
            5 => 'pengemasan',    6 => 'pengiriman',
            7 => 'foto dan video',8 => 'kesimpulan',
        ];

        // Validasi bisa tetap sama…

        foreach ($tahapan as $i => $defaultKet) {
            $status     = $request->input("status{$i}", 'hold');
            $tanggal    = $request->input("tanggal{$i}", now());
            $keterangan = $request->input("keterangan{$i}", $defaultKet);

            $bukti = Bukti::firstOrNew([
                'user_id' => $user->id,
                'step'    => $i,
            ]);

            if ($request->hasFile("bukti{$i}")) {
                $bukti->path = $request->file("bukti{$i}")
                                ->store('bukti', 'public');
            }

            $bukti->status     = $status;
            $bukti->tanggal    = $tanggal;
            $bukti->keterangan = $keterangan;
            $bukti->save();

            // Update progress
            $progress->{"status{$i}"}  = $status;
            $progress->{"tanggal{$i}"} = $tanggal;
        }

        $progress->save();

        return back()->with('success', 
            'Progress & Bukti berhasil diperbarui.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login.form')
            ->with('success', 'Berhasil logout');
    }
}