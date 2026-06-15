<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookRequest;
use App\Http\Requests\UpdateBookRequest;
use App\Models\Book;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class BookController extends Controller
{
    public function index(): JsonResponse
    {
        $books = Book::query()
            ->latest('id')
            ->get()
            ->map(fn (Book $book): array => $this->transformBook($book))
            ->all();

        return response()->json($books);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json($this->transformBook($book));
    }

    public function upload(StoreBookRequest $request): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $payload = $request->validated();
        $filePath = $request->file('file')->store('books', 'local');

        $book = Book::query()->create([
            'title' => $payload['title'],
            'author' => $payload['author'],
            'category' => $payload['category'] ?? null,
            'cover' => $payload['cover'] ?? null,
            'file_path' => $filePath,
        ]);

        return new JsonResponse($this->transformBook($book), Response::HTTP_CREATED);
    }

    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $this->ensureAdmin($request->user());

        $payload = $request->validated();

        if ($request->hasFile('file')) {
            Storage::disk('local')->delete($book->file_path);
            $payload['file_path'] = $request->file('file')->store('books', 'local');
            unset($payload['file']);
        }

        $book->update($payload);

        return response()->json($this->transformBook($book->fresh()));
    }

    public function destroy(Request $request, Book $book): JsonResponse
    {
        $this->ensureAdmin($request->user());

        Storage::disk('local')->delete($book->file_path);
        $book->delete();

        return response()->json(['message' => 'Buku berhasil dihapus.']);
    }

    public function pageMeta(Request $request, Book $book): JsonResponse
    {
        $this->ensureAuthenticated($request->user());

        return response()->json([
            'totalPages' => 1,
        ]);
    }

    public function pageImage(Request $request, Book $book, int $page): JsonResponse
    {
        $this->ensureAuthenticated($request->user());

        return new JsonResponse([
            'error' => 'Preview halaman belum tersedia. Reader akan memakai mode stream PDF.',
        ], Response::HTTP_NOT_FOUND);
    }

    public function stream(Request $request, Book $book): BinaryFileResponse
    {
        $this->ensureAuthenticated($request->user());

        abort_unless(Storage::disk('local')->exists($book->file_path), Response::HTTP_NOT_FOUND);

        $absolutePath = Storage::disk('local')->path($book->file_path);

        return response()->file($absolutePath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($absolutePath).'"',
        ]);
    }

    private function transformBook(Book $book): array
    {
        return [
            'id' => $book->id,
            'title' => $book->title,
            'author' => $book->author,
            'category' => $book->category,
            'cover' => $book->cover,
        ];
    }

    private function ensureAuthenticated(?User $user): void
    {
        abort_if(! $user, Response::HTTP_UNAUTHORIZED, 'Token tidak valid.');
    }

    private function ensureAdmin(?User $user): void
    {
        abort_if(! $user || $user->role !== 'ADMIN', Response::HTTP_FORBIDDEN, 'Akses admin diperlukan.');
    }
}
