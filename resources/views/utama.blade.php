<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="{{ asset('css/utama.css') }}" />
    <title>Order Status Tracker — Customer</title>
  </head>
  <body>
    @if(session('success'))
      <div class="alert-success">
        {{ session('success') }}
      </div>
    @endif

    <h1>MAKLON PROGRESS TRACKER</h1>

    <div class="stages">
      @foreach($stages as $stepName => $gambar)
        @php
          $step   = $loop->iteration;
          $bukti  = $buktiList->get($step);
          $rawStatus = optional($bukti)->status ?? 'hold';
          $status = strtolower(str_replace(' ', '_', $rawStatus));
          $color  = match($status) {
            'done'        => '#28a745',
            'on_progress' => '#ffc107',
            'hold'        => '#ffb3b3',
            default       => '#e0e0e0',
          };
        @endphp

        <div class="card">
          <h2>{{ $step }}. {{ Str::title($stepName) }}</h2>
          <div class="img">
            <img src="{{ asset('gambar/' . $gambar) }}" alt="{{ $stepName }}" />
          </div>

          <div class="controls">
            <input type="date" disabled value="{{ optional($bukti)->tanggal }}" />
            @if($step === 8)
              <textarea name="keterangan8" id="keterangan8" rows="5" disabled>{{ old('keterangan8', $buktiAll[8]->keterangan ?? '') }}</textarea>
            @else
              <select disabled style="background-color: {{ $color }}">
                <option>
                  {{ $bukti ? ucfirst(str_replace('_',' ',$status)) : '' }}
                </option>
              </select>
            @endif
          </div>

          {{-- Tombol Lihat Bukti khusus tahap 1–6 --}}
          @if($step >= 1 && $step <= 6)
            @if($bukti && $bukti->path)
              <div style="margin-top: 10px;">
                <button class="extra-btn"
                  onclick="showPopup('{{ asset('storage/' . $bukti->path) }}', `{{ $bukti->keterangan }}`)">
                  Lihat Bukti
                </button>
              </div>
            @endif
          @endif

          {{-- Tombol WhatsApp khusus tahap 7 --}}
          @if($step === 7 && $bukti && $status === 'done')
            <button class="extra-btn">
              Terkirim Via WhatsApp
            </button>
          @endif  
        </div>
      @endforeach
    </div>

    <div class="header-actions">
        <a href="{{ url('/login') }}" class="back-button">Kembali</a>
    </div>  

    <div id="overlay" class="popup-overlay">
      <div class="popup-content">
        <img id="popup-img" src="" alt="Popup Image" />
        <p id="popup-desc"></p>
        <button onclick="closePopup()">Tutup</button>
      </div>
    </div>

    <script>
      const overlay = document.getElementById('overlay');
      const popupImg = document.getElementById('popup-img');
      const popupDesc = document.getElementById('popup-desc');

      function showPopup(imageSrc, description) {
        popupImg.src = imageSrc;
        popupDesc.textContent = description || ''; // tambahkan deskripsi
        overlay.classList.add('active');
      }

      function closePopup() {
        overlay.classList.remove('active');
        setTimeout(() => {
          overlay.style.display = 'none';
        }, 300);
      }

      // ESC key support
      document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
          closePopup();
        }
      });

      // Observer untuk atur display flex jika class active ditambahkan
      const observer = new MutationObserver(() => {
        if (overlay.classList.contains('active')) {
          overlay.style.display = 'flex';
        }
      });
      observer.observe(overlay, { attributes: true });
    </script>
  </body>
</html>