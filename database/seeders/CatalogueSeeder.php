<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use Database\Seeders\Support\SamplePdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * 8 categories, 12 authors, 40 published books (+1 draft), each with a small
 * generated PDF on the private ebook disk. No cover images: the frontend
 * renders generated covers.
 */
class CatalogueSeeder extends Seeder
{
    public const FILE_DIRECTORY = 'ebooks/demo';

    public function run(): void
    {
        $data = require __DIR__.'/data/catalogue.php';
        $disk = Storage::disk(config('bookplanet.ebook_disk'));

        // Deterministic file names, so re-seeding overwrites instead of piling up.
        $disk->deleteDirectory(self::FILE_DIRECTORY);

        $categories = [];
        foreach ($data['categories'] as $slug => [$name, $description]) {
            $categories[$slug] = Category::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'description' => $description, 'deleted_at' => null],
            );
        }

        $authors = [];
        foreach ($data['authors'] as $slug => [$name, $bio]) {
            $authors[$slug] = Author::withTrashed()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'bio' => $bio, 'deleted_at' => null],
            );
        }

        $books = array_merge(
            array_map(fn ($row) => [...$row, true], $data['books']),
            array_map(fn ($row) => [...$row, false], $data['drafts']),
        );

        foreach ($books as $index => [$title, $authorSlug, $categorySlug, $price, $sale, $pages, $featured, $description, $published]) {
            $slug = Str::slug($title);
            $author = $authors[$authorSlug];
            $category = $categories[$categorySlug];

            $path = self::FILE_DIRECTORY.'/'.$slug.'.pdf';
            $pdf = SamplePdf::make($title, $author->name, $description, $category->name);
            $disk->put($path, $pdf);

            Book::withTrashed()->updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'description' => $description,
                'author_id' => $author->getKey(),
                'category_id' => $category->getKey(),
                'price_cents' => $price,
                'sale_price_cents' => $sale,
                'cover_path' => null,
                'file_path' => $path,
                'file_format' => 'pdf',
                'file_size' => strlen($pdf),
                'page_count' => $pages,
                'isbn' => $this->isbn($index + 1),
                'is_published' => $published,
                'is_featured' => $featured,
                // Spread release dates over the past year (newest first in the list is not required).
                'published_at' => $published ? now()->subDays(2 + ($index * 37) % 360)->setTime(9, 0) : null,
                'deleted_at' => null,
            ]);
        }
    }

    /**
     * A syntactically valid (checksummed) ISBN-13 in the 979-8 range.
     */
    private function isbn(int $n): string
    {
        $digits = '9798'.str_pad((string) (880000000 + $n * 7), 8, '0', STR_PAD_LEFT);
        $digits = substr($digits, 0, 12);

        $sum = 0;
        foreach (str_split($digits) as $i => $digit) {
            $sum += (int) $digit * ($i % 2 === 0 ? 1 : 3);
        }

        return $digits.((10 - $sum % 10) % 10);
    }
}
