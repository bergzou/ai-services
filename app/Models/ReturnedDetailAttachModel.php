<?php

namespace App\Models;

class ReturnedDetailAttachModel extends BaseModel
{
    protected $table = 'returned_detail_attach';


    public function getTable()
    {
        return $this->table;
    }

}