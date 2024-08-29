<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\books\StorebookRequest;
use App\Http\Requests\Dashboard\books\BookDeleteRequest;
use App\Reposetories\CategoryRepository;
use App\Reposetories\BookRepository;
use App\Services\CategoryService;
use App\Reposetories\AuthorRepository;
use App\Services\AuthorService;
use App\Services\BookService;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public CategoryService $categoryService;
    public BookService $bookService;
    public AuthorService $authorService;

    public function __construct(CategoryService $categoryService, BookService $bookService,AuthorService $authorService)
    {
        $this->categoryService = $categoryService;
        $this->bookService = $bookService;
        $this->authorService=$authorService;
    }

    public function index()
    {
        return view('dashboard.books.index');
    }

    public function getall(Request $request)
    {
       return $this->bookService->datatable();
    }


    public function create()
    {

        $categories = $this->categoryService->getAll();
        $authors=$this->authorService->getAll();
        return view('dashboard.books.create' , compact('categories','authors'));
    }


    public function store(StorebookRequest $request)
    {
        $book = $this->bookService->store($request->validated());
        return redirect()->route('dashboard.books.index');
    }


    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        $categories = $this->categoryService->getAll();
        $book = $this->bookService->getById($id);
        $authors=$this->authorService->getAll();
       return view('dashboard.books.edit' , compact('categories', 'book','authors'));
    }


    public function update(Request $request, $id)
    {
       $this->bookService->update($id,$request->all());
       return redirect()->route('dashboard.books.index');
    }


    public function destroy($id)
    {
        //
    }
    public function delete(BookDeleteRequest $request)
    {
        $this->bookService->delete($request->validated());
        return redirect()->route('dashboard.books.index');
    }
}
