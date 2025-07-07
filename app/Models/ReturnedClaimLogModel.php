<?php

namespace App\Models;

class ReturnedClaimLogModel extends BaseModel
{
    protected $table = 'returned_claim_log';


    public function getTable()
    {
        return $this->table;
    }

}