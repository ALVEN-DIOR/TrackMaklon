<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Order Status Tracker Admin</title>
  <link rel="stylesheet" href="<?php echo e(asset('/public/css/admin.css')); ?>">
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>
  <!-- DEBUG INFO ADMIN -->
  <!-- <div style="background: #e8f5e8; padding: 15px; margin-bottom: 20px; border: 2px solid #28a745;">
    <h3>🔑 ADMIN MODE ACTIVE</h3>
    <strong>User:</strong> <?php echo e(auth()->user()->email); ?><br>
    <strong>Role:</strong> <?php echo e(auth()->user()->role); ?><br>
    <strong>No HP:</strong> <?php echo e(auth()->user()->no_hp); ?><br>
    <strong>Is Admin:</strong> <?php echo e(auth()->user()->role === 'admin' ? 'YES' : 'NO'); ?><br>
    <strong>Can Edit:</strong> <?php echo e(auth()->user()->role === 'admin' ? 'YES' : 'NO'); ?><br>
  </div> -->

  <?php if(session('success')): ?>
    <div class="alert-success">
      <?php echo e(session('success')); ?>

    </div>
  <?php endif; ?>

  <?php if(session('error')): ?>
    <div class="alert-error" style="background: #ffebee; color: #c62828; padding: 10px; margin-bottom: 20px;">
      <?php echo e(session('error')); ?>

    </div>
  <?php endif; ?>

  <?php
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

  ?>

  <h1>MAKLON PROGRESS TRACKER-ADMIN</h1>
  <div class="container">
    <input class="form-group" type="email" value="<?php echo e($user->email); ?>" readonly />

    <form action="<?php echo e(route('progress.update')); ?>" method="POST" enctype="multipart/form-data" id="adminForm">
      <?php echo csrf_field(); ?>

      <div class="stages">
        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stepName => $gambar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php
            $i          = $loop->iteration;
            $statusKey  = "status{$i}";
            $dateKey    = "tanggal{$i}";
            $bukti      = $buktiAll[$i] ?? null;
	    $current    = old($statusKey, $progress->{$statusKey});

            $deskripsiTahapan = [
              1 => 'Digital Marketing',
              2 => 'Digital Marketing',
              3 => 'Digital Marketing',
              4 => 'Produksi',
              5 => 'Produksi',
              6 => 'Produksi',
              7 => 'Digital Marketing',
              8 => 'Digital Marketing',
            ];
          ?>

          <div class="card">
            <h2><?php echo e($i); ?>. <?php echo e(Str::title($stepName)); ?></h2>
            <div class="card-img">
              <img src="<?php echo e(asset('/public/gambar/' . $gambar)); ?>" alt="<?php echo e($stepName); ?>">
            </div>

            <div class="controls">
              <input type="date"
                name="<?php echo e($dateKey); ?>"
                value="<?php echo e(old($dateKey, $progress->{$dateKey})); ?>"
                <?php echo e($isAdmin ? '' : 'readonly'); ?>>

              <?php if($i === 8 && $isAdmin): ?> <!-- Stage 8: Kesimpulan -->
                <label for="keterangan8">Keterangan</label>
                <textarea name="keterangan8" id="keterangan8" rows="5"><?php echo e(old('keterangan8', $buktiAll[8]->keterangan ?? '')); ?></textarea>
              <?php else: ?>
                <select name="<?php echo e($statusKey); ?>" 
                        id="status-select-<?php echo e($i); ?>"
                        <?php echo e($isAdmin ? '' : 'disabled'); ?>>
                  <option value="done"        
                    <?php echo e($current === 'done'        ? 'selected' : ''); ?>>Done</option>
                  <option value="on_progress" 
                    <?php echo e($current === 'on_progress' ? 'selected' : ''); ?>>On Progress</option>
                  <option value="hold"
                    <?php echo e(is_null($current) || $current==='hold' ? 'selected' : ''); ?>>
                    Hold
                  </option>
                </select>
              <?php endif; ?>
            </div>

            <?php if($isAdmin && $i !== 7 && $i !== 8): ?>
              <div class="file-upload" data-step="<?php echo e($i); ?>" style="<?php echo e($current === 'done' ? '' : 'display: none;'); ?>">
                <label for="bukti<?php echo e($i); ?>">Upload File</label>
                <input type="file" id="bukti<?php echo e($i); ?>" name="bukti<?php echo e($i); ?>" />
                
                <label for="keterangan<?php echo e($i); ?>">Keterangan</label>
                <input type="text"
                      id="keterangan<?php echo e($i); ?>"
                      name="keterangan<?php echo e($i); ?>"
                      value="<?php echo e(old("keterangan{$i}", $bukti?->keterangan)); ?>" />
                
                <?php if($bukti): ?>
                  <p>File sebelumnya: 
                    <a href="<?php echo e(asset('public/storage/' . $bukti->path)); ?>" target="_blank">Lihat</a>
                  </p>
                <?php endif; ?>
              </div>
	     <?php endif; ?>
           <p class="step-desc" style="margin-top: 10px; color: #333; font-style: italic; text-align: center;"><?php echo e($deskripsiTahapan[$i]); ?></p>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>

      <?php if($isAdmin): ?>
      <div class="header-actions">
        <a href="<?php echo e(url('/')); ?>" class="back-button">Kembali</a>
        <button type="submit" class="btn-save" id="saveBtn">Simpan Semua</button>
      </div>  
      <?php else: ?>
        <div style="color: red; text-align: center; padding: 20px;">
          <strong>Anda tidak memiliki akses untuk mengedit data!</strong>
        </div>
      <?php endif; ?>
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
<?php /**PATH /home/u857117347/domains/mymaklontracker.com/public_html/resources/views/admin.blade.php ENDPATH**/ ?>