<?php
namespace App\Models\Common;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MqLogModel extends BaseModel
{
    use HasFactory;

    protected  $connection = 'mysql';
    protected  $table = 'returned_mq_log';
}
