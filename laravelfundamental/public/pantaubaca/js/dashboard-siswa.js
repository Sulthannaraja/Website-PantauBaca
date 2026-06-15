if (!requireAuth(["SISWA", "ADMIN"])) {
  throw new Error("Unauthorized");
}

const auth = getAuth();
const quizList = document.getElementById("availableQuizList");
const quizAttemptPanel = document.getElementById("quizAttemptPanel");
let availableQuizzes = [];

(async function () {
  let data = [];
  try {
    data = await apiGet("/reading/report");
  } catch (e) {
    if (!isNetworkError(e)) throw e;
    data = demoReadingReport();
    showInfo("Backend belum terhubung. Menampilkan data demo sementara.");
  }

  const totalDuration = data.reduce((acc, i) => acc + i.duration, 0);
  const uniqueBooks = new Set(data.map(i => i.bookId));

  document.getElementById("totalDuration").textContent = `${totalDuration} menit`;
  document.getElementById("totalBooks").textContent = uniqueBooks.size;
  document.getElementById("targetInfo").textContent = `${uniqueBooks.size}/10`;

  document.getElementById("historyBody").innerHTML = data.length ? data.map(i => `
    <tr>
      <td>${escapeHtml(i.bookTitle)}</td>
      <td>${i.duration} menit</td>
      <td>${i.progress}%</td>
      <td>${i.lastPage}</td>
      <td>${new Date(i.updatedAt).toLocaleString()}</td>
    </tr>
  `).join("") : '<tr><td colspan="5">Belum ada riwayat membaca.</td></tr>';

  await loadAvailableQuizzes(data);
})();

async function loadAvailableQuizzes(readingLogs) {
  if (auth.role !== "SISWA") {
    quizList.innerHTML = '<p class="empty-state">Mode admin hanya untuk memantau dashboard siswa.</p>';
    return;
  }

  const completedBooks = Array.from(
    new Map(
      readingLogs
        .filter(log => Number(log.progress) >= 100)
        .map(log => [Number(log.bookId), log])
    ).values()
  );

  if (completedBooks.length === 0) {
    quizList.innerHTML = '<p class="empty-state">Selesaikan bacaan sampai 100% untuk membuka kuis.</p>';
    return;
  }

  const quizResults = await Promise.all(
    completedBooks.map(async log => {
      try {
        return await apiGet(`/books/${log.bookId}/quiz`);
      } catch {
        return null;
      }
    })
  );

  availableQuizzes = quizResults.filter(Boolean);

  quizList.innerHTML = availableQuizzes.length
    ? availableQuizzes.map(quiz => `
      <article class="quiz-card">
        <div>
          <h3>${escapeHtml(quiz.title)}</h3>
          <p>${escapeHtml(quiz.description || "Kuis pemahaman bacaan")}</p>
        </div>
        <button type="button" class="btn btn-primary" onclick="openQuiz(${quiz.quizId})">Kerjakan</button>
      </article>
    `).join("")
    : '<p class="empty-state">Belum ada kuis dari guru untuk buku yang sudah selesai dibaca.</p>';
}

window.openQuiz = function(quizId) {
  const quiz = availableQuizzes.find(item => Number(item.quizId) === Number(quizId));
  if (!quiz) return;

  quizAttemptPanel.classList.remove("hidden");
  quizAttemptPanel.innerHTML = `
    <h3>${escapeHtml(quiz.title)}</h3>
    <form id="quizAttemptForm" class="quiz-attempt-form">
      ${quiz.questions.map((question, index) => renderQuestion(question, index)).join("")}
      <button type="submit" class="btn btn-primary">Kirim Jawaban</button>
    </form>
  `;

  document.getElementById("quizAttemptForm").addEventListener("submit", async event => {
    event.preventDefault();
    await submitQuiz(quiz);
  });

  quizAttemptPanel.scrollIntoView({ behavior: "smooth", block: "start" });
};

function renderQuestion(question, index) {
  const fieldName = `question-${question.questionId}`;
  const options = Array.isArray(question.options) ? question.options : [];

  if (question.type === "MCQ") {
    return `
      <fieldset class="quiz-question">
        <legend>${index + 1}. ${escapeHtml(question.question)}</legend>
        ${options.map(option => `
          <label class="quiz-option">
            <input type="radio" name="${fieldName}" value="${escapeHtml(option)}" required />
            <span>${escapeHtml(option)}</span>
          </label>
        `).join("")}
      </fieldset>
    `;
  }

  return `
    <label class="quiz-question">
      <span>${index + 1}. ${escapeHtml(question.question)}</span>
      <textarea class="input" name="${fieldName}" rows="3" required></textarea>
    </label>
  `;
}

async function submitQuiz(quiz) {
  const form = document.getElementById("quizAttemptForm");
  const formData = new FormData(form);
  const answers = quiz.questions.map(question => ({
    questionId: question.questionId,
    response: String(formData.get(`question-${question.questionId}`) || "")
  }));

  if (answers.some(answer => !answer.response.trim())) {
    alert("Semua jawaban wajib diisi.");
    return;
  }

  try {
    const result = await apiPost("/quizzes/submit", {
      quizId: quiz.quizId,
      answers
    });

    quizAttemptPanel.innerHTML = `
      <div class="quiz-result">
        <h3>Kuis berhasil dikirim</h3>
        <p>Skor pilihan ganda: ${result.score}</p>
      </div>
    `;
  } catch (error) {
    alert(error.message);
  }
}
