<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        return view('library.index', [
            // Purchased books stay available even if later unpublished/removed from sale.
            'books' => $request->user()->books()
                ->withTrashed()
                ->with('author')
                ->orderByPivot('created_at', 'desc')
                ->get(),
        ]);
    }

    public function download(Book $book): StreamedResponse
    {
        $this->authorize('download', $book);

        $disk = Storage::disk(config('bookplanet.ebook_disk'));

        abort_unless($book->file_path && $disk->exists($book->file_path), 404);

        return $disk->download($book->file_path, $book->downloadFilename(), [
            'Content-Type' => $book->file_format === 'epub' ? 'application/epub+zip' : 'application/pdf',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
