<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model as EloquentModel;

class Model extends EloquentModel
{
    public function scopePage($query ,$pageSize, $request){
        $page = $request->getRequestParam('page') ? $request->getRequestParam('page') : 1;
        $paginator = $query->paginate($pageSize, ['*'],'page',$page);
        $paginator->setPath($request->getServerParams()['request_uri']);
        return $paginator;
    }

    public function getQueueableRelations()
    {
        // TODO: Implement getQueueableRelations() method.
    }
}