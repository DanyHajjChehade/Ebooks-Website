<?php

namespace App\Services;

use App\Reposetories\BookRepository;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class BookService
{
    public $bookRepository;
    public function __construct(BookRepository $repo)
    {
        $this->bookRepository = $repo;
    }

    public function getAll()
    {
        return $this->bookRepository->baseQuery();
    }

    public function getById($id)
    {
        return $this->bookRepository->baseQuery([],[],['id'=>$id])->firstOrFail();
    }

    public function store($params)
    {

        if (isset($params['book_image'])) {
            $params['book_image'] = ImageUpload::uploadImage($params['book_image']);
        }


     return  $this->bookRepository->store($params);
    }

    public function update($id, $params)
    {
        if (isset($params['book_image'])) {
            $params['book_image'] = ImageUpload::uploadImage($params['book_image']);
        }


        return $this->bookRepository->update($id, $params);
    }

    public function delete($params)
    {
        $this->bookRepository->delete($params);
    }

    public function datatable()
    {
        $query = $this->bookRepository->baseQuery(relations:['category']);
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return $btn = '
                <a href="' . Route('dashboard.books.edit', $row->id) . '"  class="edit btn btn-success btn-sm" ><i class="fa fa-edit"></i></a>

                <button type="button" id="deleteBtn"  data-id="' . $row->id . '" class="btn btn-danger mt-md-0 mt-2" data-bs-toggle="modal"
                data-original-title="test" data-bs-target="#deletemodal"><i class="fa fa-trash"></i></button>';
            })

            ->addColumn('category', function ($row) {
                return $row->category->name;
            })

            ->addColumn('book_image', function ($row) {
                return '<img src="' . asset($row->book_image) . '" width="100px" height="100px">';
            })

            ->rawColumns(['action','book_image'])
            ->make(true);
        }

}
