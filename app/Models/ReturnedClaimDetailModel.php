<?php

namespace App\Models;

class ReturnedClaimDetailModel extends BaseModel
{
    protected $table = 'returned_claim_detail';


    public function getTable()
    {
        return $this->table;
    }

}