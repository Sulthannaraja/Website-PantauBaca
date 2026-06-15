<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Reader</title>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('pantaubaca/css/style.css') }}" />
</head>
<body>
  <nav class="nav">
    <div class="container nav-inner">
      <div class="brand" id="bookTitle">Reader</div>
      <div class="nav-links">
        <a href="katalog.html">Kembali</a>
        <a href="#" onclick="logout()">Logout</a>
      </div>
    </div>
  </nav>

  <main class="container section layout">
    <aside class="sidebar">
      <h3>Progress</h3>
      <p id="progressText">0%</p>
      <div class="progress-wrap"><div id="progressBar" class="progress-bar" style="width:0%"></div></div>
      <p id="pageInfo">Halaman 1</p>
    </aside>
    <section class="mainpanel">
      <div class="reader-toolbar">
        <button type="button" id="prev" class="btn btn-outline icon-btn" aria-label="Halaman sebelumnya">
          <span class="icon">‹</span>
        </button>
        <button type="button" id="next" class="btn btn-outline icon-btn" aria-label="Halaman berikutnya">
          <span class="icon">›</span>
        </button>
        <button type="button" id="zoomOut" class="btn btn-outline icon-btn" aria-label="Perkecil tampilan">
          <span class="icon">−</span>
        </button>
        <button type="button" id="zoomIn" class="btn btn-outline icon-btn" aria-label="Perbesar tampilan">
          <span class="icon">+</span>
        </button>
      </div>
      <div id="readerStage" class="reader-stage">
        <img id="pageImage" class="page-image hidden" alt="Halaman buku" />
        <canvas id="pdf-canvas" class="hidden"></canvas>
        <iframe id="pdf-frame" class="hidden"></iframe>
      </div>
    </section>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.min.js"></script>
  <script src="{{ asset('pantaubaca/js/app.js') }}"></script>
  <script src="{{ asset('pantaubaca/js/reader.js') }}"></script>
</body>
</html>

