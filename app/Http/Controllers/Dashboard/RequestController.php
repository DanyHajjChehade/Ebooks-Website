<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\requests\requestDeleteRequest;
use App\Http\Requests\Dashboard\requests\requestStoreRequest;
use App\Http\Requests\Dashboard\requests\requestUpdateRequest;
use App\Models\Req;
use App\Models\User;
use App\Services\requestService;
use Illuminate\Http\Request;
use DataTables;
class RequestController extends Controller
{

    private $requestService;

    public function __construct(requestService $requestService)
    {
        $this->requestService = $requestService;
    }
    public function index()
    {
        return view('dashboard.requests.index');
    }


    public function getall()
    {
       return $this->requestService->datatable();
    }




    public function edit($id)
    {
        $request = $this->requestService->getById($id,true);

        return view('dashboard.requests.edit' , compact('request'));
    }


    public function approve($id)
    {
        $request = Req::where('id', $id)->first();
        $user = $request->user;
        $user->update(['usertype' => 1]);
        $this->requestService->delete($request);
        return redirect()->route('dashboard.requests.index');
    }


    public function delete(requestDeleteRequest $request)
    {
        $this->requestService->delete($request->validated());
        return redirect()->route('dashboard.requests.index');
    }
}
