<?php

namespace App\Services;

use App\Reposetories\RequestRepository;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class RequestService
{
    public $requestRepository;
    public function __construct(RequestRepository $repo)
    {
        $this->requestRepository = $repo;
    }

    public function getAll()
    {
        return $this->requestRepository->baseQuery()->get();
    }

    public function getById($id)
    {
        return $this->requestRepository->baseQuery([],[],['id'=>$id])->firstOrFail();
    }

    public function store($params)
    {

        if (isset($params['request_image'])) {
            $params['request_image'] = ImageUpload::uploadImage($params['request_image']);
        }


     return  $this->requestRepository->store($params);
    }

    public function update($id, $params)
    {
        if (isset($params['request_image'])) {
            $params['request_image'] = ImageUpload::uploadImage($params['request_image']);
        }


        return $this->requestRepository->update($id, $params);
    }

    public function delete($params)
    {
        $this->requestRepository->delete($params);
    }

    public function datatable()
    {
        $query = $this->requestRepository->baseQuery();
        return DataTables::of($query)
            ->addColumn('action', function ($row) {
                return $btn = '<form method="POST" action="' . Route('dashboard.requests.approve', $row->id) .'">
                '.csrf_field().'<input type="hidden" name="_method" value="PUT"><button type="submit" class="edit btn btn-success btn-sm" >Approve</button></from>
                <button type="button" id="deleteBtn"  data-id="' . $row->id . '" class="btn btn-danger mt-md-0 mt-2" data-bs-toggle="modal"
                data-original-title="test" data-bs-target="#deletemodal"><i class="fa fa-trash"></i></button>';
            })


            ->rawColumns(['action','request_image'])
            ->make(true);
        }

}
