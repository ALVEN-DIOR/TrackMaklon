<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Status Tracker Admin</title>
  <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <!-- DEBUG INFO ADMIN -->
  <!-- <div style="background: #e8f5e8; padding: 15px; margin-bottom: 20px; border: 2px solid #28a745;">
    <h3>🔑 ADMIN MODE ACTIVE</h3>
    <strong>User:</strong> {{ auth()->user()->email }}<br>
    <strong>Role:</strong> {{ auth()->user()->role }}<br>
    <strong>No HP:</strong> {{ auth()->user()->no_hp }}<br>
    <strong>Is Admin:</strong> {{ auth()->user()->role === 'admin' ? 'YES' : 'NO' }}<br>
    <strong>Can Edit:</strong> {{ auth()->user()->role === 'admin' ? 'YES' : 'NO' }}<br>
  </div> -->

  @if(session('success'))
    <div class="alert-success">
      {{ session('success') }}
    </div>
  @endif

  @if(session('error'))
    <div class="alert-error" style="background: #ffebee; color: #c62828; padding: 10px; margin-bottom: 20px;">
      {{ session('error') }}
    </div>
  @endif

  @php
    $user      = auth()->user();
    $progress  = \App\Models\Progress::firstOrCreate(['user_id' => $user->id]);
    $buktiAll  = \App\Models\Bukti::where('user_id', $user->id)->get()->keyBy('step');
    $isAdmin   = ($user->role === 'admin'); // FIX: Gunakan role dari user, bukan session
    
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
  @endphp

  <h1>MAKLON PROGRESS TRACKER-ADMIN</h1>
  <div class="container">
    <input class="form-group" type="email" value="{{ $user->email }}" readonly />

    <form action="{{ route('progress.update') }}" method="POST" enctype="multipart/form-data" id="adminForm">
      @csrf

      <div class="stages">
        @foreach($stages as $stepName => $gambar)
          @php
            $i          = $loop->iteration;
            $statusKey  = "status{$i}";
            $dateKey    = "tanggal{$i}";
            $bukti      = $buktiAll[$i] ?? null;
            $current    = old($statusKey, $progress->{$statusKey});
          @endphp

          <div class="card">
            <h2>{{ $i }}. {{ Str::title($stepName) }}</h2>
            <div class="card-img">
              <img src="{{ asset('gambar/' . $gambar) }}" alt="{{ $stepName }}">
            </div>

            <div class="controls">
              <input type="date"
                name="{{ $dateKey }}"
                value="{{ old($dateKey, $progress->{$dateKey}) }}"
                {{ $isAdmin ? '' : 'readonly' }}>

              @if($i === 8 && $isAdmin) <!-- Stage 8: Kesimpulan -->
                <label for="keterangan8">Keterangan</label>
                <textarea name="keterangan8" id="keterangan8" rows="5">{{ old('keterangan8', $buktiAll[8]->keterangan ?? '') }}</textarea>
              @else
                <select name="{{ $statusKey }}" 
                        id="status-select-{{ $i }}"
                        {{ $isAdmin ? '' : 'disabled' }}>
                  <option value="done"        
                    {{ $current === 'done'        ? 'selected' : '' }}>Done</option>
                  <option value="on_progress" 
                    {{ $current === 'on_progress' ? 'selected' : '' }}>On Progress</option>
                  <option value="hold"
                    {{ is_null($current) || $current==='hold' ? 'selected' : '' }}>
                    Hold
                  </option>
                </select>
              @endif
            </div>

            @if($isAdmin && $i !== 7 && $i !== 8)
              <div class="file-upload" data-step="{{ $i }}" style="{{ $current === 'done' ? '' : 'display: none;' }}">
                <label for="bukti{{ $i }}">Upload File</label>
                <input type="file" id="bukti{{ $i }}" name="bukti{{ $i }}" />
                
                <label for="keterangan{{ $i }}">Keterangan</label>
                <input type="text"
                      id="keterangan{{ $i }}"
                      name="keterangan{{ $i }}"
                      value="{{ old("keterangan{$i}", $bukti?->keterangan) }}" />
                
                @if($bukti)
                  <p>File sebelumnya: 
                    <a href="{{ Storage::url($bukti->path) }}" target="_blank">Lihat</a>
                  </p>
                @endif
              </div>
            @endif
          </div>
        @endforeach
      </div>

      @if($isAdmin)
      <div class="header-actions">
        <a href="{{ url('/login') }}" class="back-button">Kembali</a>
        <button type="submit" class="btn-save" id="saveBtn">Simpan Semua</button>
      </div>  
      @else
        <div style="color: red; text-align: center; padding: 20px;">
          <strong>Anda tidak memiliki akses untuk mengedit data!</strong>
        </div>
      @endif
    </form>
  </div>

<script>
  // Untuk setiap select status, atur warna dan tampilkan/hide file-upload
  document.querySelectorAll("select[id^='status-select-']").forEach(select => {
    const step = select.id.replace('status-select-', '');
    const uploadSection = document.querySelector(`.file-upload[data-step="${step}"]`);

    const updateUI = () => {
      const value = select.value;
      // Warna background sesuai status
      if (value === "done") {
        select.style.backgroundColor = "#b6f7c1";
        if (uploadSection) uploadSection.style.display = "none"; // Hide upload section for stage 7
        if (step !== '7') {
          if (uploadSection) uploadSection.style.display = "block"; // Show upload section for other stages
        }
      } else if (value === "on_progress") {
        select.style.backgroundColor = "#fff3b0";
        if (uploadSection) uploadSection.style.display = "none";
      } else if (value === "hold") {
        select.style.backgroundColor = "#ffb3b3";
        if (uploadSection) uploadSection.style.display = "none";
      } else {
        select.style.backgroundColor = "#ffffff";
        if (uploadSection) uploadSection.style.display = "none";
      }
    };

    // Inisialisasi saat halaman load
    updateUI();
    // Bind event change
    select.addEventListener("change", updateUI);
  });

  // Alert khusus untuk step 7
  document.addEventListener("DOMContentLoaded", () => {
    const step7 = document.querySelector('#status-select-7');
    if (step7) {
      step7.addEventListener('change', function () {
        if (this.value === 'done') {
          alert('Pastikan kirim buktinya di WhatsApp!');
        }
      });
    }

    // Prevent double submit
    document.getElementById('adminForm').addEventListener('submit', function() {
      const saveBtn = document.getElementById('saveBtn');
      if (saveBtn) {
        saveBtn.disabled = true;
        saveBtn.textContent = 'Menyimpan...';
        
        setTimeout(function() {
          saveBtn.disabled = false;
          saveBtn.textContent = 'Simpan Semua';
        }, 3000);
      }
    });
  });
</script>
</body>
</html>
