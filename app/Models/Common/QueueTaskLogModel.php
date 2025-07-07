<?php
namespace App\Models\Common;
use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
class QueueTaskLogModel extends BaseModel
{
    use HasFactory;
    /** 表名
     * @var string
     */
    protected $table = 'returned_mq_run_log';

    /**
     * Notes: 获取表名
     * Date: 2024/3/28 13:43
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }


}
