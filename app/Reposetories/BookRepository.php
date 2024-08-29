<?php

namespace App\Reposetories;

use App\Models\Category;
use App\Models\Book;
use App\Utils\ImageUpload;
use Yajra\DataTables\Facades\DataTables;

class BookRepository implements RepositoryInterface
{
    public $book;
    public function __construct(Book $book)
    {

        $this->book = $book;
    }

    public function baseQuery($relations=[],$withCount=[] , $where=[])
    {
        $query = $this->book->select('*')->with($relations);
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
        return $this->book->where('id', $id)->firstOrFail();
    }


    public function store($params)
    {
        $book =  $this->book->create($params);

         return $book;

    }






    public function update($id, $params)
    {

        $book = $this->getbyId($id);
        $book =($book->update($params));
        $book = $this->getbyId($id);
        return $book;
    }

    public function delete($params)
    {
        $book = $this->getbyId($params['id']);
        return $book->delete();
    }

}
