if (!requireAuth(["GURU", "ADMIN"])) {
  throw new Error("Unauthorized");
}

const quizQuestions = [];
const addQuestionBtn = document.getElementById("addQuestionBtn");
const saveQuizBtn = document.getElementById("saveQuizBtn");
const questionType = document.getElementById("questionType");
const questionOptions = document.getElementById("questionOptions");

addQuestionBtn.addEventListener("click", addQuizQuestion);
saveQuizBtn.addEventListener("click", saveQuiz);
questionType.addEventListener("change", syncQuestionType);
syncQuestionType();

(async function () {
  let data = [];
  try {
    await loadQuizBooks();
    data = await apiGet("/reading/report");
  } catch (e) {
    if (!isNetworkError(e)) throw e;
    data = demoReadingReport();
    renderQuizBookOptions(demoBooks());
    addQuestionBtn.disabled = true;
    saveQuizBtn.disabled = true;
    showInfo("Backend belum terhubung. Menampilkan data demo sementara.");
  }

  const byStudent = {};
  data.forEach(r => {
    const key = r.userId;
    if (!byStudent[key]) byStudent[key] = { name: r.userName, duration: 0, count: 0, progress: 0 };
    byStudent[key].duration += r.duration;
    byStudent[key].count += 1;
    byStudent[key].progress += r.progress;
  });

  const students = Object.values(byStudent);
  const avgDuration = students.length ? Math.round(students.reduce((a, s) => a + s.duration, 0) / students.length) : 0;

  document.getElementById("activeStudents").textContent = students.length;
  document.getElementById("bookReads").textContent = data.length;
  document.getElementById("avgDuration").textContent = `${avgDuration} menit`;

  document.getElementById("teacherTableBody").innerHTML = data.length ? data.map(r => `
    <tr>
      <td>${escapeHtml(r.userName)}</td>
      <td>${escapeHtml(r.bookTitle)}</td>
      <td>${r.duration} menit</td>
      <td>${r.progress}%</td>
      <td>${r.progress > 50 ? 'Aktif' : 'Perlu perhatian'}</td>
    </tr>
  `).join("") : '<tr><td colspan="5">Belum ada aktivitas membaca.</td></tr>';

  const groupedByDay = {};
  data.forEach(r => {
    const day = new Date(r.updatedAt).toLocaleDateString();
    groupedByDay[day] = (groupedByDay[day] || 0) + 1;
  });

  const labels = Object.keys(groupedByDay);
  const values = Object.values(groupedByDay);
  new Chart(document.getElementById("activityChart"), {
    type: "line",
    data: {
      labels,
      datasets: [{
        label: "Aktivitas Membaca",
        data: values,
        borderColor: "#0077B6",
        backgroundColor: "rgba(144, 224, 239, 0.3)",
        tension: 0.3,
        fill: true
      }]
    }
  });
})();

async function loadQuizBooks() {
  const books = await apiGet("/books");
  renderQuizBookOptions(books);
}

function renderQuizBookOptions(books) {
  const select = document.getElementById("quizBook");
  select.innerHTML = books.length
    ? books.map(book => `<option value="${book.id}">${escapeHtml(book.title)}</option>`).join("")
    : '<option value="">Belum ada buku</option>';
}

function syncQuestionType() {
  const isMultipleChoice = questionType.value === "MCQ";
  questionOptions.disabled = !isMultipleChoice;
  questionOptions.placeholder = isMultipleChoice
    ? "Opsi pilihan ganda, pisahkan dengan koma"
    : "Tidak dipakai untuk esai";
}

function addQuizQuestion() {
  const type = questionType.value;
  const question = document.getElementById("questionText").value.trim();
  const answer = document.getElementById("questionAnswer").value.trim();
  const points = Number(document.getElementById("questionPoints").value || 1);
  const options = document.getElementById("questionOptions").value
    .split(",")
    .map(option => option.trim())
    .filter(Boolean);

  if (!question) {
    alert("Pertanyaan wajib diisi.");
    return;
  }

  if (type === "MCQ" && (options.length < 2 || !answer)) {
    alert("Pilihan ganda butuh minimal 2 opsi dan jawaban benar.");
    return;
  }

  quizQuestions.push({
    type,
    question,
    options: type === "MCQ" ? options : undefined,
    answer: answer || undefined,
    points: Math.max(1, points)
  });

  document.getElementById("questionText").value = "";
  document.getElementById("questionOptions").value = "";
  document.getElementById("questionAnswer").value = "";
  document.getElementById("questionPoints").value = "1";
  renderQuizQuestions();
}

function renderQuizQuestions() {
  document.getElementById("quizQuestionBody").innerHTML = quizQuestions.length
    ? quizQuestions.map((question, index) => `
      <tr>
        <td>${question.type}</td>
        <td>${escapeHtml(question.question)}</td>
        <td>${question.points}</td>
        <td><button type="button" class="btn btn-outline" onclick="removeQuizQuestion(${index})">Hapus</button></td>
      </tr>
    `).join("")
    : '<tr><td colspan="4">Belum ada pertanyaan.</td></tr>';
}

window.removeQuizQuestion = function(index) {
  quizQuestions.splice(index, 1);
  renderQuizQuestions();
};

async function saveQuiz() {
  const bookId = Number(document.getElementById("quizBook").value);
  const title = document.getElementById("quizTitle").value.trim();
  const description = document.getElementById("quizDescription").value.trim();

  if (!bookId || !title || quizQuestions.length === 0) {
    alert("Pilih buku, isi judul, dan tambahkan minimal satu pertanyaan.");
    return;
  }

  try {
    await apiPost("/quizzes", {
      bookId,
      title,
      description,
      questions: quizQuestions
    });

    quizQuestions.splice(0, quizQuestions.length);
    document.getElementById("quizTitle").value = "";
    document.getElementById("quizDescription").value = "";
    renderQuizQuestions();
    alert("Kuis berhasil disimpan.");
  } catch (error) {
    alert(error.message);
  }
}

renderQuizQuestions();
