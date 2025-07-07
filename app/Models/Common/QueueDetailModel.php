<?php
/**
 * 任务表
 */
namespace App\Models\Common;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class QueueDetailModel extends BaseModel
{
    use HasFactory;
    /** 表名
     * @var string
     */
    protected $connection = 'mysql';
    protected $table = 'returned_queue_detail';

}
