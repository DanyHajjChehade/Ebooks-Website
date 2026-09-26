<?php

namespace Tests\Feature\Admin;

use App\Models\Author;
use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminAuthorTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->admin = $this->admin();
    }

    public function test_index_supports_search(): void
    {
        Author::factory()->create(['name' => 'Agatha Lumen']);
        Author::factory()->create(['name' => 'Bruno Stark']);

        $this->actingAs($this->admin)->get(route('admin.authors.index', ['q' => 'lumen']))
            ->assertOk()
            ->assertViewIs('admin.authors.index')
            ->assertViewHas('authors', fn ($a) => $a->total() === 1 && $a->first()->name === 'Agatha Lumen')
            ->assertViewHas('filters', ['q' => 'lumen']);
    }

    public function test_admin_can_create_an_author_with_a_photo(): void
    {
        $this->actingAs($this->admin)->get(route('admin.authors.create'))->assertOk()->assertViewHas('author');

        $this->actingAs($this->admin)->post(route('admin.authors.store'), [
            'name' => 'Mira Solace',
            'bio' => 'Writes about the sea.',
            'photo' => UploadedFile::fake()->image('Mira Portrait.png', 400, 400),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.authors.index'));

        $author = Author::sole();
        $this->assertSame('mira-solace', $author->slug);
        $this->assertMatchesRegularExpression('#^authors/[A-Za-z0-9]{40}\.png$#', $author->photo_path);
        Storage::disk('public')->assertExists($author->photo_path);
    }

    public function test_author_input_is_validated(): void
    {
        $this->actingAs($this->admin)->post(route('admin.authors.store'), [
            'name' => '',
            'photo' => UploadedFile::fake()->create('photo.svg', 3, 'image/svg+xml'),
        ])->assertSessionHasErrors(['name', 'photo']);

        $this->assertDatabaseCount('authors', 0);
    }

    public function test_admin_can_update_an_author_and_replace_the_photo(): void
    {
        $author = Author::factory()->create(['photo_path' => 'authors/old.jpg']);
        Storage::disk('public')->put('authors/old.jpg', 'old');

        $this->actingAs($this->admin)->get(route('admin.authors.edit', $author))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.authors.update', $author), [
            'name' => 'New Name',
            'slug' => 'new-name',
            'bio' => 'Updated',
            'photo' => UploadedFile::fake()->image('new.jpg'),
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.authors.edit', $author));

        $author->refresh();
        $this->assertSame('New Name', $author->name);
        $this->assertSame('new-name', $author->slug);
        Storage::disk('public')->assertMissing('authors/old.jpg');
        Storage::disk('public')->assertExists($author->photo_path);
    }

    public function test_an_author_with_books_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->admin)->from(route('admin.authors.index'))
            ->delete(route('admin.authors.destroy', $book->author))
            ->assertRedirect(route('admin.authors.index'))
            ->assertSessionHasErrors('delete');

        $this->assertNotSoftDeleted($book->author);
    }

    public function test_an_author_without_books_can_be_deleted(): void
    {
        $author = Author::factory()->create();
        $removedBook = Book::factory()->for($author)->create();
        $removedBook->delete(); // soft-deleted books don't block

        $this->actingAs($this->admin)->delete(route('admin.authors.destroy', $author))
            ->assertRedirect(route('admin.authors.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($author);
        $this->assertSame($author->id, $removedBook->fresh()->author->id); // still resolves for libraries
    }
}
