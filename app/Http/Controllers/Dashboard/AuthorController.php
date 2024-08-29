<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\Authors\AuthorDeleteRequest;
use App\Http\Requests\Dashboard\Authors\AuthorStoreRequest;
use App\Http\Requests\Dashboard\Authors\AuthorUpdateRequest;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Http\Request;
use DataTables;
class AuthorController extends Controller
{

    private $authorService;

    public function __construct(authorService $authorService)
    {
        $this->authorService = $authorService;
    }
    public function index()
    {
        return view('dashboard.authors.index');
    }


    public function getall()
    {
       return $this->authorService->datatable();
    }


    public function store(AuthorStoreRequest $request)
    {
        $this->authorService->store($request->validated());
        return redirect()->route('dashboard.authors.index')->with('success', 'author added successfully');
    }



    public function edit($id)
    {
        $author = $this->authorService->getById($id,true);

        return view('dashboard.authors.edit' , compact('author'));
    }


    public function update( $id ,AuthorUpdateRequest $request)
    {
        $this->authorService->update($id,$request->validated());
        return redirect()->route('dashboard.authors.edit' , $id)->with('success', 'author updated successfully');
    }




    public function delete(AuthorDeleteRequest $request)
    {
        $this->authorService->delete($request->validated());
        return redirect()->route('dashboard.authors.index');
    }
}
