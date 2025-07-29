<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no" />
    <link rel="stylesheet" href="<?php echo e(asset('public/css/utama.css')); ?>" />
    <title>Order Status Tracker — Customer</title>
  </head>
  <body>
    <?php if(session('success')): ?>
      <div class="alert-success">
        <?php echo e(session('success')); ?>

      </div>
    <?php endif; ?>

    <h1>MAKLON PROGRESS TRACKER</h1>

    <div class="stages">
      <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $stepName => $gambar): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
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
        ?>

        <div class="card">
          <h2><?php echo e($step); ?>. <?php echo e(Str::title($stepName)); ?></h2>
          <div class="img">
            <img src="<?php echo e(asset('/public/gambar/' . $gambar)); ?>" alt="<?php echo e($stepName); ?>" />
          </div>

          <div class="controls">
            <input type="date" disabled value="<?php echo e(optional($bukti)->tanggal); ?>" />
            <?php if($step === 8): ?>
              <textarea name="keterangan8" id="keterangan8" rows="5" disabled><?php echo e(old('keterangan8', $buktiList->get(8)->keterangan ?? 
'')); ?></textarea>
            <?php else: ?>
              <select disabled style="background-color: <?php echo e($color); ?>">
                <option>
                  <?php echo e($bukti ? ucfirst(str_replace('_',' ',$status)) : ''); ?>

                </option>
              </select>
            <?php endif; ?>
          </div>

          
          <?php if($step >= 1 && $step <= 6): ?>
            <?php if($bukti && $bukti->path): ?>
              <div style="margin-top: 10px;">
                <button class="extra-btn"
                  onclick="showPopup(  '<?php echo e(asset("/public/storage/" . ltrim($bukti->path, "/"))); ?>',  `<?php echo e($bukti->keterangan); ?>`)">
                  Lihat Bukti
                </button>
              </div>
            <?php endif; ?>
          <?php endif; ?>

          
          <?php if($step === 7 && $bukti && $status === 'done'): ?>
            <button class="extra-btn">
              Terkirim Via WhatsApp
            </button>
          <?php endif; ?>  
        </div>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="header-actions">
        <a href="<?php echo e(url('/')); ?>" class="back-button">Kembali</a>
    </div>  

    <div id="overlay" style="
   position: fixed !important; 
   top: 0 !important; 
   left: 0 !important; 
   width: 100vw !important; 
   height: 100vh !important; 
   background: rgba(34, 153, 84, 0.8) !important;
   display: none !important; 
   align-items: center !important; 
   justify-content: center !important; 
   z-index: 9999999 !important;
 ">
   <div class="popup-content" style="
     background: white !important;
     padding: 25px !important;
     border-radius: 12px !important;
     max-width: 450px !important;
     width: 85% !important;
     max-height: 80vh !important;
     overflow-y: auto !important;
     box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
     display: flex !important;
     flex-direction: column !important;
     align-items: center !important;
     position: relative !important;
     border: 3px solid #7fcf8c !important;
   ">
    
     <!-- Header -->
     <div style="
       width: 100% !important;
       text-align: center !important;
       margin-bottom: 20px !important;
       padding-bottom: 15px !important;
       border-bottom: 2px solid #7fcf8c !important;
     ">
       <h3 style="
         margin: 0 !important;
         color: #229954 !important;
         font-size: 1.2rem !important;
         font-weight: 600 !important;
       ">Bukti</h3>
     </div>

     <!-- Image -->
     <img id="popup-img" src="" alt="Bukti Tahapan" style="
       max-width: 100% !important;
       max-height: 40vh !important;
       border-radius: 8px !important;
       object-fit: contain !important;
       margin-bottom: 15px !important;
       border: 2px solid #e9ecef !important;
     " />

     <!-- Description -->
     <div id="popup-desc" style="
       width: 100% !important;
       padding: 12px 15px !important;
       background: #f8f9fa !important;
       border-radius: 8px !important;
       text-align: center !important;
       font-size: 0.9rem !important;
       line-height: 1.5 !important;
       color: #495057 !important;
       border-left: 4px solid #7fcf8c !important;
       margin-bottom: 20px !important;
     "></div>

     <!-- Button -->
     <button onclick="closePopup()" style="
       padding: 10px 25px !important;
       background: #229954 !important;
       color: white !important;
       border: none !important;
       border-radius: 6px !important;
       cursor: pointer !important;
       font-size: 0.9rem !important;
       font-weight: 500 !important;
     ">Tutup</button>

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
<?php /**PATH /home/u857117347/domains/mymaklontracker.com/public_html/resources/views/utama.blade.php ENDPATH**/ ?>