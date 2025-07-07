<?php

/**
 * 文件
 */
namespace App\Client;



use App\Exceptions\BusinessException;

class FileClient extends BaseClient
{

    public function __construct()
    {
        $this->host = env('INTRANET_URL').'/file';
    }

    /**
     * @功能:创建滚动导出
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-06-26 09:53:34
     * @param array $data
     * @return bool|string|array
     * @throws BusinessException
     */
    public  function createRangeExport(array $data = []): bool|string|array
    {
        return $this->sendClient($this->host."/inner/export/range/create",'POST',json_encode($data));
    }

    /**
     * @功能:创建分页导出
     * @版本：V1.0
     * @作者: lzl
     * @日期: 2023-06-27 17:18:32
     * @param array $data
     * @return bool|string|array
     * @throws BusinessException
     */
    public  function createPageExport(array $data = []): bool|string|array
    {
        return $this->sendClient($this->host."/inner/export/page/create",'POST',json_encode($data));
    }


    public  function commonUpload($filePath,$fileName): bool|string|array
    {
        return $this->sendFile($this->host."/inner/file/commonUpload",$filePath,$fileName);
    }




}
