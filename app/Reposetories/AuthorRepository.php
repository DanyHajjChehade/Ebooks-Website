<?php

namespace App\Reposetories;

use App\Models\Category;
use App\Models\Author;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class AuthorRepository implements RepositoryInterface
{
    public $author;
    public function __construct(Author $author)
    {

        $this->author = $author;
    }

    public function baseQuery($relations=[],$withCount=[] , $where=[])
    {
        $query = $this->author->select('*')->with($relations);
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
        return $this->author->where('id', $id)->firstOrFail();
    }


    public function store($params)
    {
        $author =  $this->author->create($params);

         return $author;

    }






    public function update($id, $params)
    {

        $author = $this->getbyId($id);
        $author =($author->update($params));
        $author = $this->getbyId($id);
        return $author;
    }

    public function delete($params)
    {
        $author = $this->getbyId($params['id']);
        return $author->delete();
    }

}
