<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('pantaubaca/css/style.css') }}" />
</head>
<body>
  <main class="container section auth-page">
    <div class="auth-card">
      <h2>Daftar</h2>
      <p class="subtitle">Buat akun untuk menyimpan progress membaca dan mengakses fitur penuh.</p>
      <div class="field-group">
        <input id="name" class="input" placeholder="Nama" autocomplete="name" />
      </div>
      <div class="field-group">
        <input id="email" class="input" placeholder="Email" autocomplete="email" />
      </div>
      <div class="field-group">
        <input id="password" class="input" type="password" placeholder="Password" autocomplete="new-password" />
      </div>
      <div class="field-group">
        <select id="role" class="input">
          <option value="SISWA">Siswa</option>
          <option value="GURU">Guru</option>
        </select>
      </div>
      <button type="button" id="registerBtn" class="btn btn-primary auth-btn">Daftar</button>
      <p class="auth-footer">Sudah punya akun? <a href="login.html" class="text-link">Login</a></p>
    </div>
  </main>

  <script src="{{ asset('pantaubaca/js/app.js') }}"></script>
  <script src="{{ asset('pantaubaca/js/register.js') }}"></script>
</body>
</html>

