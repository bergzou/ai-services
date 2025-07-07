<?php
/**
 * 任务表
 */
namespace App\Models\Common;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class QueueTaskModel extends BaseModel
{
    use HasFactory;

    protected $connection = 'mysql';
    protected $table = 'returned_queue_task';


}
