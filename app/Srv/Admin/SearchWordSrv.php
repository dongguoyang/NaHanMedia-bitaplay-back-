<?php


namespace App\Srv\Admin;

use App\Models\SearchWord;
use App\Srv\Srv;

class SearchWordSrv extends Srv
{
    public function list($name)
    {
        $query = SearchWord::orderByDesc('count');
        if ($name) {
            $query->where('word', 'like', "%{$name}%");
        }
        $data = $query->paginate(20);
        return $this->returnData(ERR_SUCCESS, '', $this->pageList($data));
    }

}
