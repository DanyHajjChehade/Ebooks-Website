<?php

namespace Tests\Unit;

use App\Models\Book;
use PHPUnit\Framework\TestCase;

class BookPricingTest extends TestCase
{
    public function test_effective_price_uses_a_lower_sale_price(): void
    {
        $book = new Book(['price_cents' => 1500, 'sale_price_cents' => 900]);

        $this->assertTrue($book->isOnSale());
        $this->assertSame(900, $book->effective_price_cents);
        $this->assertFalse($book->isFree());
    }

    public function test_a_sale_price_that_is_not_lower_is_ignored(): void
    {
        $book = new Book(['price_cents' => 1500, 'sale_price_cents' => 1500]);

        $this->assertFalse($book->isOnSale());
        $this->assertSame(1500, $book->effective_price_cents);
    }

    public function test_free_books(): void
    {
        $this->assertTrue((new Book(['price_cents' => 0]))->isFree());
        $this->assertTrue((new Book(['price_cents' => 800, 'sale_price_cents' => 0]))->isFree());
    }
}
