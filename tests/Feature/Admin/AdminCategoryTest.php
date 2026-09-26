<?php

namespace Tests\Feature\Admin;

use App\Models\Book;
use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCategoryTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->admin();
    }

    public function test_index_supports_search(): void
    {
        Category::factory()->create(['name' => 'Poetry']);
        Category::factory()->create(['name' => 'History']);

        $this->actingAs($this->admin)->get(route('admin.categories.index', ['q' => 'poe']))
            ->assertOk()
            ->assertViewIs('admin.categories.index')
            ->assertViewHas('categories', fn ($c) => $c->total() === 1 && $c->first()->name === 'Poetry');
    }

    public function test_admin_can_create_a_category(): void
    {
        $this->actingAs($this->admin)->get(route('admin.categories.create'))->assertOk()->assertViewHas('category');

        $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Graphic Novels',
            'description' => 'Pictures and words.',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.categories.index'));

        $this->assertDatabaseHas('categories', ['name' => 'Graphic Novels', 'slug' => 'graphic-novels']);
    }

    public function test_category_input_is_validated(): void
    {
        Category::factory()->create(['slug' => 'taken']);

        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => '', 'slug' => 'taken'])
            ->assertSessionHasErrors(['name', 'slug']);
    }

    public function test_category_slugs_are_normalised_and_text_must_be_valid_utf8(): void
    {
        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Sci-Fi', 'slug' => ' Science Fiction! '])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('categories', ['slug' => 'science-fiction']);

        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Again', 'slug' => 'SCIENCE_FICTION'])
            ->assertSessionHasErrors('slug');
        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => "Bad \xFF", 'description' => "\xFE"])
            ->assertSessionHasErrors(['name', 'description']);
    }

    public function test_slugs_from_deleted_categories_are_not_reused(): void
    {
        $old = Category::factory()->create(['name' => 'Mystery']);
        $old->delete();

        $this->actingAs($this->admin)->post(route('admin.categories.store'), ['name' => 'Mystery'])->assertSessionHasNoErrors();

        $this->assertSame('mystery-2', Category::query()->latest('id')->first()->slug);
    }

    public function test_admin_can_update_a_category(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)->get(route('admin.categories.edit', $category))->assertOk();

        $this->actingAs($this->admin)->put(route('admin.categories.update', $category), [
            'name' => 'Renamed',
            'slug' => $category->slug,
            'description' => 'New description',
        ])->assertSessionHasNoErrors()->assertRedirect(route('admin.categories.edit', $category));

        $this->assertSame('Renamed', $category->refresh()->name);
    }

    public function test_a_category_with_books_cannot_be_deleted(): void
    {
        $book = Book::factory()->create();

        $this->actingAs($this->admin)->from(route('admin.categories.index'))
            ->delete(route('admin.categories.destroy', $book->category))
            ->assertSessionHasErrors('delete');

        $this->assertNotSoftDeleted($book->category);
    }

    public function test_an_empty_category_can_be_deleted(): void
    {
        $category = Category::factory()->create();

        $this->actingAs($this->admin)->delete(route('admin.categories.destroy', $category))
            ->assertRedirect(route('admin.categories.index'));

        $this->assertSoftDeleted($category);
    }
}
