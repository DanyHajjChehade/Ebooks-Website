<?php

namespace Tests\Feature\Admin;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Database\Seeders\Support\SamplePdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class AdminBookTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');
        $this->admin = $this->admin();
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'A Brand New Book',
            'description' => 'A description of the new book.',
            'author_id' => Author::factory()->create()->id,
            'category_id' => Category::factory()->create()->id,
            'price' => '12.99',
            'sale_price' => '',
            'page_count' => 240,
            'isbn' => '979-8-88-000000-5',
            'is_published' => '1',
            'is_featured' => '0',
            'ebook' => UploadedFile::fake()->create('my book.pdf', 300, 'application/pdf'),
            'cover' => UploadedFile::fake()->image('cover.jpg', 600, 900),
        ], $overrides);
    }

    public function test_index_lists_books_with_search_and_status_filters(): void
    {
        $published = Book::factory()->create(['title' => 'Published Thing']);
        $draft = Book::factory()->draft()->create(['title' => 'Draft Thing']);
        $featured = Book::factory()->featured()->create(['title' => 'Other']);

        $this->actingAs($this->admin)->get(route('admin.books.index'))
            ->assertOk()
            ->assertViewIs('admin.books.index')
            ->assertViewHas('books', fn ($b) => $b->total() === 3)
            ->assertViewHas('filters', ['q' => null, 'status' => null]);

        $this->actingAs($this->admin)->get(route('admin.books.index', ['q' => 'thing']))
            ->assertViewHas('books', fn ($b) => $b->pluck('id')->sort()->values()->all() === [$published->id, $draft->id]);
        $this->actingAs($this->admin)->get(route('admin.books.index', ['status' => 'draft']))
            ->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$draft->id]);
        $this->actingAs($this->admin)->get(route('admin.books.index', ['status' => 'featured']))
            ->assertViewHas('books', fn ($b) => $b->pluck('id')->all() === [$featured->id]);
    }

    public function test_create_form_has_select_options(): void
    {
        Author::factory()->count(2)->create();

        $this->actingAs($this->admin)->get(route('admin.books.create'))
            ->assertOk()
            ->assertViewIs('admin.books.create')
            ->assertViewHas('book', fn (Book $b) => ! $b->exists)
            ->assertViewHas('authors', fn ($a) => $a->count() === 2)
            ->assertViewHas('categories');
    }

    public function test_admin_can_create_a_book_with_files(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData())
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('admin.books.index'))
            ->assertSessionHas('status');

        $book = Book::sole();
        $this->assertSame('a-brand-new-book', $book->slug);
        $this->assertSame(1299, $book->price_cents);
        $this->assertNull($book->sale_price_cents);
        $this->assertTrue($book->is_published);
        $this->assertFalse($book->is_featured);
        $this->assertNotNull($book->published_at);
        $this->assertSame('pdf', $book->file_format);
        $this->assertSame(300 * 1024, $book->file_size);

        // Random names, on the right disks, never the client file name.
        $this->assertMatchesRegularExpression('#^ebooks/[A-Za-z0-9]{40}\.pdf$#', $book->file_path);
        $this->assertStringNotContainsString('my book', $book->file_path);
        Storage::disk('local')->assertExists($book->file_path);
        Storage::disk('public')->assertMissing($book->file_path);
        $this->assertMatchesRegularExpression('#^covers/[A-Za-z0-9]{40}\.(jpg|jpeg)$#', $book->cover_path);
        Storage::disk('public')->assertExists($book->cover_path);
    }

    public function test_a_real_pdf_is_accepted_by_content_sniffing(): void
    {
        $pdf = SamplePdf::make('Real', 'Author', 'A real PDF file.');

        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData([
            'ebook' => $this->realUpload('real.pdf', $pdf),
            'cover' => null,
        ]))->assertSessionHasNoErrors();

        $this->assertSame(strlen($pdf), Book::sole()->file_size);
    }

    public function test_epub_files_are_accepted(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData([
            'ebook' => UploadedFile::fake()->create('novel.epub', 100, 'application/epub+zip'),
            'cover' => null,
        ]))->assertSessionHasNoErrors();

        $book = Book::sole();
        $this->assertSame('epub', $book->file_format);
        $this->assertStringEndsWith('.epub', $book->file_path);
    }

    public function test_an_epub_detected_as_a_plain_zip_is_accepted_when_it_is_a_real_epub(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData([
            'ebook' => $this->zipUpload('real.epub', withEpubMimetype: true),
            'cover' => null,
        ]))->assertSessionHasNoErrors();

        $this->assertSame('epub', Book::sole()->file_format);
    }

    public function test_a_zip_renamed_to_epub_is_rejected(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData([
            'ebook' => $this->zipUpload('fake.epub', withEpubMimetype: false),
        ]))->assertSessionHasErrors('ebook');

        $this->assertDatabaseCount('books', 0);
    }

    public function test_non_ebook_uploads_are_rejected(): void
    {
        $cases = [
            $this->realUpload('evil.pdf', "<?php echo 'just text pretending to be a pdf';"), // content is sniffed, not the name
            UploadedFile::fake()->create('script.php', 10, 'application/x-php'),
            UploadedFile::fake()->create('book.pdf', 10, 'application/epub+zip'), // extension/content mismatch
            UploadedFile::fake()->image('picture.jpg'),
        ];

        foreach ($cases as $file) {
            $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['ebook' => $file]))
                ->assertSessionHasErrors('ebook');
        }

        $this->assertDatabaseCount('books', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_ebook_size_limit_comes_from_config(): void
    {
        config(['bookplanet.max_ebook_kb' => 100]);

        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData([
            'ebook' => UploadedFile::fake()->create('big.pdf', 101, 'application/pdf'),
        ]))->assertSessionHasErrors('ebook');
    }

    public function test_cover_must_be_a_safe_image(): void
    {
        foreach ([
            UploadedFile::fake()->create('cover.svg', 5, 'image/svg+xml'),
            UploadedFile::fake()->create('cover.pdf', 5, 'application/pdf'),
            UploadedFile::fake()->image('huge.jpg')->size(5000),
        ] as $cover) {
            $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['cover' => $cover]))
                ->assertSessionHasErrors('cover');
        }
    }

    public function test_book_input_is_validated(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), [
            'title' => '',
            'description' => '',
            'author_id' => 999,
            'category_id' => 'x',
            'price' => '-1',
            'sale_price' => '5',
            'isbn' => '<script>',
        ])->assertSessionHasErrors(['title', 'description', 'author_id', 'category_id', 'price', 'isbn', 'ebook']);
    }

    public function test_slugs_are_normalised_before_the_unique_check(): void
    {
        Book::factory()->create(['slug' => 'my-book']);

        // "My_Book" normalises to the existing "my-book": a validation error, not a silent "my-book-2".
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['slug' => 'My_Book']))
            ->assertSessionHasErrors('slug');

        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['slug' => '  Brand New: Book! ', 'cover' => null]))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('books', ['slug' => 'brand-new-book']);
    }

    public function test_free_text_must_be_valid_utf8(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData([
            'title' => "Bad \xFF title",
            'description' => "Broken \xC3\x28 bytes",
        ]))->assertSessionHasErrors(['title', 'description']);

        $this->assertDatabaseCount('books', 0);
    }

    public function test_prices_must_be_free_or_at_least_fifty_cents(): void
    {
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['price' => '0.49']))
            ->assertSessionHasErrors('price');
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['price' => '0.01']))
            ->assertSessionHasErrors('price');
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['price' => '5.00', 'sale_price' => '0.30']))
            ->assertSessionHasErrors('sale_price');
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['price' => '5.00', 'sale_price' => '5.00']))
            ->assertSessionHasErrors('sale_price');
        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['price' => '1.234']))
            ->assertSessionHasErrors('price');

        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['price' => '0', 'cover' => null]))
            ->assertSessionHasNoErrors();
        $this->assertSame(0, Book::sole()->price_cents);
    }

    public function test_admin_can_update_a_book_and_replace_its_files(): void
    {
        $book = Book::factory()->withFile()->create(['cover_path' => 'covers/old.jpg']);
        Storage::disk('public')->put('covers/old.jpg', 'old');
        $oldFile = $book->file_path;

        $this->actingAs($this->admin)->get(route('admin.books.edit', $book))->assertOk()->assertViewHas('book', fn ($b) => $b->is($book));

        $this->actingAs($this->admin)->put(route('admin.books.update', $book), $this->validData([
            'title' => 'Renamed',
            'slug' => $book->slug,
            'price' => '20',
            'sale_price' => '15.50',
            'is_featured' => '1',
        ]))->assertSessionHasNoErrors()->assertRedirect(route('admin.books.edit', $book));

        $book->refresh();
        $this->assertSame('Renamed', $book->title);
        $this->assertSame(2000, $book->price_cents);
        $this->assertSame(1550, $book->sale_price_cents);
        $this->assertTrue($book->is_featured);
        $this->assertNotSame($oldFile, $book->file_path);
        Storage::disk('local')->assertMissing($oldFile);
        Storage::disk('local')->assertExists($book->file_path);
        Storage::disk('public')->assertMissing('covers/old.jpg');
    }

    public function test_updating_without_files_keeps_them(): void
    {
        $book = Book::factory()->withFile()->create();
        $file = $book->file_path;

        $this->actingAs($this->admin)->put(route('admin.books.update', $book), $this->validData([
            'ebook' => null,
            'cover' => null,
            'is_published' => '0',
        ]))->assertSessionHasNoErrors();

        $book->refresh();
        $this->assertSame($file, $book->file_path);
        $this->assertFalse($book->is_published);
        Storage::disk('local')->assertExists($file);
    }

    public function test_cover_can_be_removed(): void
    {
        $book = Book::factory()->create(['cover_path' => 'covers/old.jpg']);
        Storage::disk('public')->put('covers/old.jpg', 'old');

        $this->actingAs($this->admin)->put(route('admin.books.update', $book), $this->validData([
            'ebook' => null, 'cover' => null, 'remove_cover' => '1',
        ]))->assertSessionHasNoErrors();

        $this->assertNull($book->refresh()->cover_path);
        Storage::disk('public')->assertMissing('covers/old.jpg');
    }

    public function test_slug_must_be_unique(): void
    {
        Book::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin)->post(route('admin.books.store'), $this->validData(['slug' => 'taken']))
            ->assertSessionHasErrors('slug');
    }

    public function test_deleting_a_book_soft_deletes_it_and_keeps_the_file(): void
    {
        $book = Book::factory()->withFile()->create();

        $this->actingAs($this->admin)->delete(route('admin.books.destroy', $book))
            ->assertRedirect(route('admin.books.index'))
            ->assertSessionHas('status');

        $this->assertSoftDeleted($book);
        Storage::disk('local')->assertExists($book->file_path);
    }

    private function realUpload(string $name, string $contents): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $contents);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function zipUpload(string $name, bool $withEpubMimetype): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'epub');
        $zip = new ZipArchive;
        $zip->open($path, ZipArchive::OVERWRITE);
        if ($withEpubMimetype) {
            $zip->addFromString('mimetype', 'application/epub+zip');
            $zip->setCompressionName('mimetype', ZipArchive::CM_STORE);
        }
        $zip->addFromString('META-INF/container.xml', '<?xml version="1.0"?><container/>');
        $zip->addFromString('OEBPS/chapter.xhtml', '<html><body><p>Hello</p></body></html>');
        $zip->close();

        return new UploadedFile($path, $name, null, null, true);
    }
}
