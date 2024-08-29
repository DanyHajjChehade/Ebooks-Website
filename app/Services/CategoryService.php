<?php

namespace App\Services;

use App\Models\Category;
use App\Reposetories\CategoryRepository;
use App\Utils\DatatableService;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class CategoryService
{
    public $categoryRepository;
    public function __construct(CategoryRepository $repo)
    {
        $this->categoryRepository = $repo;
    }


    public function store($params)
    {
        if (isset($params['image'])) {
            $params['image'] = ImageUpload::uploadImage($params['image']);
        }
        return  $this->categoryRepository->store($params);
    }


    public function getById($id)
    {
        return $this->categoryRepository->baseQuery([],[],['id'=>$id])->firstOrFail();
    }

    public function update($id, $params)
    {
        $category = $this->categoryRepository->getById($id);
        if (isset($params['image'])) {
            $params['image'] = ImageUpload::uploadImage($params['image']);
        }
        return  $this->categoryRepository->update($category, $params);
    }


    public function delete($params)
    {
        $this->categoryRepository->delete($params);
    }

    public function datatable()
    {
        $query = $this->categoryRepository->baseQuery();
        return  DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('action', function ($row) {
                return $btn = '
                        <a href="' . Route('dashboard.categories.edit', $row->id) . '"  class="edit btn btn-success btn-sm" ><i class="fa fa-edit"></i></a>

                        <button type="button" id="deleteBtn"  data-id="' . $row->id . '" class="btn btn-danger mt-md-0 mt-2" data-bs-toggle="modal"
                        data-original-title="test" data-bs-target="#deletemodal"><i class="fa fa-trash"></i></button>';
            })

            ->addColumn('empty_column', function ($row) {
                return '';
            })


            ->addColumn('image', function ($row) {
                return '<img src="' . asset($row->image) . '" width="100px" height="100px">';
            })

            ->rawColumns(['action', 'image'])
            ->make(true);
    }


    public function getAll()
    {
        return $this->categoryRepository->baseQuery()->get();
    }
}
