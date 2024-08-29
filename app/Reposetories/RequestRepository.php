<?php

namespace App\Reposetories;

use App\Models\Category;
use App\Models\Req;
use App\Models\User;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class RequestRepository implements RepositoryInterface
{
    public $request;
    public $user;
    public function __construct(User $user,Req $request)
    {
        $this->request=$request;
        $this->user = $user;
    }

    public function baseQuery($relations=[],$withCount=[] , $where=[])
    {
        $query = $this->user->select('*')
                        ->join('reqs', 'users.id', '=', 'reqs.user_id');
        foreach ($withCount as $key => $value) {
           $query->withCount($value);
        }
        foreach ($where as $key => $value) {
            $query->where($key, $value);
        }
       return $query;
    }

    public function getbyId($id)
    {
        return $this->request->where('id', $id)->firstOrFail();
    }


    public function store($params)
    {
        $request =  $this->request->create($params);

         return $request;

    }

    public function update($id, $params)
    {

        $request = $this->getbyId($id);
        $request =($request->update($params));
        $request = $this->getbyId($id);
        return $request;
    }

    public function delete($params)
    {
        $request = $this->getbyId($params['id']);
        return $request->delete();
    }

}
