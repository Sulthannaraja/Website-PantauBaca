<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('pantaubaca/css/style.css') }}" />
</head>
<body>
  <main class="container section auth-page">
    <div class="auth-card">
      <h2>Masuk</h2>
      <p class="subtitle">Akses katalog dan mulai membaca buku digital Anda.</p>
      <div class="field-group">
        <input id="email" class="input" placeholder="Email atau Username" autocomplete="username" />
      </div>
      <div class="field-group">
        <input id="password" class="input" type="password" placeholder="Password" autocomplete="current-password" />
      </div>
      <button type="button" id="loginBtn" class="btn btn-primary auth-btn">Masuk</button>
      <p class="auth-footer">Belum punya akun? <a href="register.html" class="text-link">Register</a></p>
    </div>
  </main>

  <script src="{{ asset('pantaubaca/js/app.js') }}"></script>
  <script src="{{ asset('pantaubaca/js/login.js') }}"></script>
</body>
</html>

