<?php

namespace App\Models;

class ReturnedOperateModel extends BaseModel
{
    protected $table = 'returned_operate';


    public function getTable()
    {
        return $this->table;
    }

}