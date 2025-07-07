<?php

namespace App\Models;

class ReturnedLogModel extends BaseModel
{
    protected $table = 'returned_log';


    public function getTable()
    {
        return $this->table;
    }

}