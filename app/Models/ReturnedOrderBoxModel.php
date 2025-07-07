<?php

namespace App\Models;

class ReturnedOrderBoxModel extends BaseModel
{
    protected $table = 'returned_order_box';


    public function getTable()
    {
        return $this->table;
    }

}