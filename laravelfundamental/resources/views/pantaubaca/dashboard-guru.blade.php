<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Guru</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('pantaubaca/css/style.css') }}" />
</head>
<body>
  <nav class="nav">
    <div class="container nav-inner">
      <div class="brand">Dashboard Guru</div>
      <div class="nav-links">
        <a href="katalog.html">Katalog</a>
        <a href="#" onclick="logout()">Logout</a>
      </div>
    </div>
  </nav>

  <main class="container section">
    <div class="stat-grid">
      <div class="stat"><h4>Siswa Aktif</h4><p id="activeStudents">0</p></div>
      <div class="stat"><h4>Total Buku Dibaca</h4><p id="bookReads">0</p></div>
      <div class="stat"><h4>Rata-rata Durasi</h4><p id="avgDuration">0 menit</p></div>
    </div>

    <section class="mainpanel quiz-panel" style="margin-top:20px;">
      <div class="panel-heading">
        <div>
          <h2>Buat Kuis</h2>
          <p>Pilih buku, susun pertanyaan, lalu simpan untuk siswa yang sudah selesai membaca.</p>
        </div>
      </div>

      <div class="quiz-form-grid">
        <select id="quizBook"></select>
        <input id="quizTitle" class="input" placeholder="Judul kuis" />
        <input id="quizDescription" class="input" placeholder="Deskripsi singkat" />
      </div>

      <div class="quiz-question-box">
        <div class="quiz-form-grid">
          <select id="questionType">
            <option value="MCQ">Pilihan ganda</option>
            <option value="ESSAY">Esai</option>
          </select>
          <input id="questionPoints" class="input" type="number" min="1" value="1" placeholder="Poin" />
        </div>
        <textarea id="questionText" class="input" rows="3" placeholder="Pertanyaan"></textarea>
        <input id="questionOptions" class="input" placeholder="Opsi pilihan ganda, pisahkan dengan koma" />
        <input id="questionAnswer" class="input" placeholder="Jawaban benar" />
        <button type="button" id="addQuestionBtn" class="btn btn-outline">Tambah Pertanyaan</button>
      </div>

      <table>
        <thead><tr><th>Tipe</th><th>Pertanyaan</th><th>Poin</th><th>Aksi</th></tr></thead>
        <tbody id="quizQuestionBody"></tbody>
      </table>

      <button type="button" id="saveQuizBtn" class="btn btn-primary quiz-save-btn">Simpan Kuis</button>
    </section>

    <div class="mainpanel" style="margin-top:20px;">
      <canvas id="activityChart" height="90"></canvas>
    </div>

    <h2 style="margin-top:24px;">Data Siswa</h2>
    <table>
      <thead><tr><th>Nama</th><th>Buku</th><th>Durasi</th><th>Progress</th><th>Status</th></tr></thead>
      <tbody id="teacherTableBody"></tbody>
    </table>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="{{ asset('pantaubaca/js/app.js') }}"></script>
  <script src="{{ asset('pantaubaca/js/dashboard-guru.js') }}"></script>
</body>
</html>

