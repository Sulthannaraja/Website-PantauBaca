<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\SubmitQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Book;
use App\Models\Quiz;
use App\Models\QuizSubmission;
use App\Models\ReadingLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class QuizController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureQuizManager($request->user());

        $quizzes = Quiz::query()
            ->with(['book:id,title', 'questions'])
            ->latest('id')
            ->get()
            ->map(fn (Quiz $quiz): array => $this->transformQuiz($quiz) + [
                'bookTitle' => $quiz->book?->title,
            ])
            ->all();

        return response()->json($quizzes);
    }

    public function store(StoreQuizRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        $quiz = Quiz::query()->create([
            'book_id' => $payload['bookId'],
            'created_by' => $user->id,
            'title' => $payload['title'],
            'description' => $payload['description'] ?? null,
            'active' => true,
        ]);

        $quiz->questions()->createMany($this->questionPayload($payload['questions']));

        $quiz->load('questions');

        return response()->json([
            'message' => 'Kuis berhasil dibuat.',
            'quiz' => $this->transformQuiz($quiz),
        ], Response::HTTP_CREATED);
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): JsonResponse
    {
        $this->ensureQuizManager($request->user());

        $payload = $request->validated();

        DB::transaction(function () use ($payload, $quiz): void {
            $quiz->update([
                'book_id' => $payload['bookId'] ?? $quiz->book_id,
                'title' => $payload['title'] ?? $quiz->title,
                'description' => $payload['description'] ?? $quiz->description,
                'active' => $payload['active'] ?? $quiz->active,
            ]);

            if (array_key_exists('questions', $payload)) {
                $quiz->questions()->delete();
                $quiz->questions()->createMany($this->questionPayload($payload['questions']));
            }
        });

        return response()->json([
            'message' => 'Kuis berhasil diperbarui.',
            'quiz' => $this->transformQuiz($quiz->fresh('questions')),
        ]);
    }

    public function destroy(Request $request, Quiz $quiz): JsonResponse
    {
        $this->ensureQuizManager($request->user());

        $quiz->delete();

        return response()->json(['message' => 'Kuis berhasil dihapus.']);
    }

    public function showBookQuiz(Request $request, Book $book): JsonResponse
    {
        $this->ensureStudentCompleteBook($request->user(), $book);

        $quiz = Quiz::query()
            ->where('book_id', $book->id)
            ->where('active', true)
            ->with('questions')
            ->first();

        abort_if(! $quiz, Response::HTTP_NOT_FOUND, 'Kuis tidak ditemukan untuk buku ini.');

        return response()->json($this->transformQuiz($quiz));
    }

    public function submit(SubmitQuizRequest $request): JsonResponse
    {
        $user = $request->user();
        $payload = $request->validated();

        $quiz = Quiz::query()
            ->with('questions')
            ->findOrFail($payload['quizId']);

        $this->ensureStudentCompleteBook($user, $quiz->book);

        $questionMap = $quiz->questions->keyBy('id');
        $score = 0;
        $answers = [];

        foreach ($payload['answers'] as $answerPayload) {
            $quizQuestionId = $answerPayload['questionId'];
            $response = $answerPayload['response'];

            abort_if(! $questionMap->has($quizQuestionId), Response::HTTP_UNPROCESSABLE_ENTITY, 'Pertanyaan kuis tidak valid.');

            $question = $questionMap->get($quizQuestionId);
            $isCorrect = null;
            $points = 0;

            if ($question->type === 'MCQ') {
                $isCorrect = strcasecmp(trim($question->answer ?? ''), trim($response)) === 0;
                $points = $isCorrect ? $question->points : 0;
                $score += $points;
            }

            $answers[] = [
                'quiz_question_id' => $quizQuestionId,
                'response' => $response,
                'is_correct' => $isCorrect,
                'points' => $points,
            ];
        }

        $submission = QuizSubmission::query()->create([
            'quiz_id' => $quiz->id,
            'user_id' => $user->id,
            'score' => $score,
            'submitted_at' => now(),
        ]);

        foreach ($answers as $answer) {
            $submission->answers()->create($answer);
        }

        return response()->json([
            'message' => 'Jawaban kuis berhasil disimpan.',
            'submissionId' => $submission->id,
            'score' => $submission->score,
            'submittedAt' => $submission->submitted_at->toIso8601String(),
        ], Response::HTTP_CREATED);
    }

    private function transformQuiz(Quiz $quiz): array
    {
        return [
            'quizId' => $quiz->id,
            'bookId' => $quiz->book_id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'active' => $quiz->active,
            'questions' => $quiz->questions->map(fn ($question): array => [
                'questionId' => $question->id,
                'type' => $question->type,
                'question' => $question->question,
                'options' => $question->options,
                'points' => $question->points,
            ])->all(),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $questions
     * @return array<int, array<string, mixed>>
     */
    private function questionPayload(array $questions): array
    {
        return collect($questions)->map(fn (array $question): array => [
            'type' => $question['type'],
            'question' => $question['question'],
            'options' => $question['options'] ?? null,
            'answer' => $question['answer'] ?? null,
            'points' => $question['points'] ?? 1,
        ])->all();
    }

    private function ensureQuizManager(?\App\Models\User $user): void
    {
        abort_if(! $user || ! in_array($user->role, ['GURU', 'ADMIN'], true), Response::HTTP_FORBIDDEN, 'Akses guru atau admin diperlukan.');
    }

    private function ensureStudentCompleteBook(?\App\Models\User $user, Book $book): void
    {
        abort_if(! $user || $user->role !== 'SISWA', Response::HTTP_FORBIDDEN, 'Akses siswa diperlukan.');

        $readingLog = ReadingLog::query()
            ->where('user_id', $user->id)
            ->where('book_id', $book->id)
            ->first();

        abort_if(! $readingLog || $readingLog->progress < 100, Response::HTTP_FORBIDDEN, 'Selesaikan bacaan terlebih dahulu untuk mengakses kuis.');
    }
}
