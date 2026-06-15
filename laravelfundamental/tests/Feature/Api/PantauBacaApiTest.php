<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\ReadingLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PantauBacaApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_login(): void
    {
        $this->postJson('/api/register', [
            'name' => 'budi123',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'role' => 'SISWA',
        ])->assertCreated();

        $this->postJson('/api/login', [
            'email' => 'budi@example.com',
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonStructure(['token', 'role', 'userId', 'name']);
    }

    public function test_admin_can_upload_and_list_books(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create([
            'api_token' => 'admin-token',
        ]);

        $response = $this->withHeader('Authorization', 'Bearer '.$admin->api_token)
            ->post('/api/books/upload', [
                'title' => 'Literasi Digital Dasar',
                'author' => 'Tim Edukasi',
                'category' => 'Buku Pembelajaran',
                'cover' => 'https://example.com/cover.jpg',
                'file' => UploadedFile::fake()->create('book.pdf', 100, 'application/pdf'),
            ]);

        $response->assertCreated()
            ->assertJsonFragment(['title' => 'Literasi Digital Dasar']);

        $this->getJson('/api/books')
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_student_can_start_and_update_reading_progress(): void
    {
        $student = User::factory()->student()->create([
            'api_token' => 'student-token',
        ]);
        $book = Book::factory()->create();

        $this->withHeader('Authorization', 'Bearer '.$student->api_token)
            ->postJson('/api/reading/start', [
                'bookId' => $book->id,
                'currentPage' => 3,
            ])->assertOk()
            ->assertJsonFragment(['lastPage' => 3]);

        $this->withHeader('Authorization', 'Bearer '.$student->api_token)
            ->postJson('/api/reading/progress', [
                'bookId' => $book->id,
                'duration' => 45,
                'progress' => 72,
                'lastPage' => 16,
            ])->assertOk()
            ->assertJsonFragment(['duration' => 45, 'progress' => 72, 'lastPage' => 16]);

        $this->assertDatabaseHas('reading_logs', [
            'user_id' => $student->id,
            'book_id' => $book->id,
            'duration' => 45,
            'progress' => 72,
            'last_page' => 16,
        ]);
    }

    public function test_teacher_can_view_reading_report(): void
    {
        $teacher = User::factory()->teacher()->create([
            'api_token' => 'teacher-token',
        ]);
        $student = User::factory()->student()->create();
        $book = Book::factory()->create([
            'title' => 'Belajar Internet Aman',
        ]);

        ReadingLog::factory()->create([
            'user_id' => $student->id,
            'book_id' => $book->id,
            'duration' => 20,
            'progress' => 44,
            'last_page' => 18,
        ]);

        $this->withHeader('Authorization', 'Bearer '.$teacher->api_token)
            ->getJson('/api/reading/report')
            ->assertOk()
            ->assertJsonFragment([
                'userName' => $student->name,
                'bookTitle' => 'Belajar Internet Aman',
                'duration' => 20,
                'progress' => 44,
                'lastPage' => 18,
            ]);
    }
}
