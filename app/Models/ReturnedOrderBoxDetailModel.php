<?php

namespace App\Models;

class ReturnedOrderBoxDetailModel extends BaseModel
{
    protected $table = 'returned_order_box_detail';


    public function getTable()
    {
        return $this->table;
    }

}