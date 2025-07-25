<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="{{ asset('../public/css/login.css') }}">
  <title>Madu WIld Bee - Tracker</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
  <div class="login-container">
    <h2>Silahkan Login</h2>
    
    @if ($errors->any())
        <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px;">
            <strong>Validation Errors:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('error'))
        <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px;">
            <strong>Error:</strong> {{ session('error') }}
        </div>
    @endif

    @if (session('success'))
        <div style="color: green; margin-bottom: 20px; border: 1px solid green; padding: 10px;">
            <strong>Success:</strong> {{ session('success') }}
        </div>
    @endif
    
    <form method="POST" action="{{ route('login') }}" id="loginForm">
      @csrf
      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="your@email.com" 
               value="{{ old('email') }}" required />
      </div>
      <div class="form-group">
        <label for="no_hp">No Handphone</label>
        <input type="text" id="no_hp" name="no_hp" placeholder="0812XXXXX" 
               value="{{ old('no_hp') }}" required />
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