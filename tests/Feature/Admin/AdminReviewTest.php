<?php

namespace Tests\Feature\Admin;

use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_lists_and_filters_reviews(): void
    {
        $five = Review::factory()->create(['rating' => 5, 'body' => 'Loved the lighthouse chapter']);
        $one = Review::factory()->create(['rating' => 1, 'body' => 'Not for me']);
        $admin = $this->admin();

        $this->actingAs($admin)->get(route('admin.reviews.index'))
            ->assertOk()->assertViewIs('admin.reviews.index')
            ->assertViewHas('reviews', fn ($r) => $r->total() === 2);
        $this->actingAs($admin)->get(route('admin.reviews.index', ['rating' => '1']))
            ->assertViewHas('reviews', fn ($r) => $r->pluck('id')->all() === [$one->id]);
        $this->actingAs($admin)->get(route('admin.reviews.index', ['q' => 'lighthouse']))
            ->assertViewHas('reviews', fn ($r) => $r->pluck('id')->all() === [$five->id]);
    }

    public function test_admin_can_delete_a_review(): void
    {
        $review = Review::factory()->create();

        $this->actingAs($this->admin())->from(route('admin.reviews.index'))
            ->delete(route('admin.reviews.destroy', $review))
            ->assertRedirect(route('admin.reviews.index'))
            ->assertSessionHas('status');

        $this->assertModelMissing($review);
    }
}
