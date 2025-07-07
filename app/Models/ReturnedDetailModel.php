<?php

namespace App\Models;

class ReturnedDetailModel extends BaseModel
{
    protected $table = 'returned_detail';


    public function getTable()
    {
        return $this->table;
    }

}