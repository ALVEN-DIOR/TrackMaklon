<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="<?php echo e(asset('/public/css/login.css')); ?>">
  <title>Madu WIld Bee - Tracker</title>
  <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
</head>
<body>
  <div class="login-container">
    <h2>Silahkan Login</h2>
    
    <?php if($errors->any()): ?>
        <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px;">
            <strong>Validation Errors:</strong>
            <ul>
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px;">
            <strong>Error:</strong> <?php echo e(session('error')); ?>

        </div>
    <?php endif; ?>

    <?php if(session('success')): ?>
        <div style="color: green; margin-bottom: 20px; border: 1px solid green; padding: 10px;">
            <strong>Success:</strong> <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    
    <form method="POST" action="<?php echo e(route('login')); ?>" id="loginForm">
      <?php echo csrf_field(); ?>
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="your@email.com" 
               value="<?php echo e(old('email')); ?>" required />
      </div>
      <div class="form-group">
        <label for="no_hp">No Handphone</label>
        <input type="text" id="no_hp" name="no_hp" placeholder="0812XXXXX" 
               value="<?php echo e(old('no_hp')); ?>" required />
      </div>
      <div class="form-group button-wrapper">
        <button type="submit" class="btn-login" id="submitBtn">Masuk</button>
      </div>
    </form>
    
    <div class="login-footer">
    </div>
  </div>

  <script>
    // Auto refresh CSRF token jika halaman sudah lama dibuka
    document.addEventListener('DOMContentLoaded', function() {
      // Refresh CSRF token setiap 30 menit
      setInterval(function() {
        fetch('/login', {
          method: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          }
        }).then(response => response.text())
        .then(html => {
          // Extract CSRF token dari response
          const parser = new DOMParser();
          const doc = parser.parseFromString(html, 'text/html');
          const newToken = doc.querySelector('meta[name="csrf-token"]')?.content;
          
          if (newToken) {
            document.querySelector('meta[name="csrf-token"]').content = newToken;
            document.querySelector('input[name="_token"]').value = newToken;
            console.log('CSRF token refreshed');
          }
        }).catch(err => console.log('CSRF refresh failed:', err));
      }, 30 * 60 * 1000); // 30 menit

      // Prevent double submit
      document.getElementById('loginForm').addEventListener('submit', function() {
        const submitBtn = document.getElementById('submitBtn');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Sedang Masuk...';
        
        // Re-enable setelah 5 detik sebagai backup
        setTimeout(function() {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Masuk';
        }, 5000);
      });
    });
  </script>
</body>
</html>
<?php /**PATH /home/u857117347/domains/mymaklontracker.com/public_html/resources/views/login.blade.php ENDPATH**/ ?>