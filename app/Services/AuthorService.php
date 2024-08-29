<?php

namespace App\Services;

use App\Reposetories\AuthorRepository;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class AuthorService
{
    public $authorRepository;
    public function __construct(AuthorRepository $repo)
    {
        $this->authorRepository = $repo;
    }

    public function getAll()
    {
        return $this->authorRepository->baseQuery()->get();
    }

    public function getById($id)
    {
        return $this->authorRepository->baseQuery([],[],['id'=>$id])->firstOrFail();
    }

    public function store($params)
    {

        if (isset($params['author_image'])) {
            $params['author_image'] = ImageUpload::uploadImage($params['author_image']);
        }


     return  $this->authorRepository->store($params);
    }

    public function update($id, $params)
    {
        if (isset($params['author_image'])) {
            $params['author_image'] = ImageUpload::uploadImage($params['author_image']);
        }


        return $this->authorRepository->update($id, $params);
    }

    public function delete($params)
    {
        $this->authorRepository->delete($params);
    }

    public function datatable()
    {
        $query = $this->authorRepository->baseQuery();
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return $btn = '
                <a href="' . Route('dashboard.authors.edit', $row->id) . '"  class="edit btn btn-success btn-sm" ><i class="fa fa-edit"></i></a>

                <button type="button" id="deleteBtn"  data-id="' . $row->id . '" class="btn btn-danger mt-md-0 mt-2" data-bs-toggle="modal"
                data-original-title="test" data-bs-target="#deletemodal"><i class="fa fa-trash"></i></button>';
            })

            ->addColumn('author_image', function ($row) {
                return '<img src="' . asset($row->author_image) . '" width="100px" height="100px">';
            })

            ->rawColumns(['action','author_image'])
            ->make(true);
        }

}
