<?php

namespace App\Services\Excel\Drivers;


use App\Interfaces\ExcelInterface;
use Vtiful\Kernel\Excel;


class VtifulDriver implements ExcelInterface
{
    protected $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function import(string $filePath, array $options = []): array
    {
        $excel = new Excel(['path' => '']);
        $data = $excel->openFile($filePath)
            ->openSheet()
            ->getSheetData();

        $startRow = $options['start_row'] ?? 0;
        return array_slice($data, $startRow);
    }

    public function export(array $data, string $fileName, array $headers = []): string
    {
        $exportPath = $this->config['path'] ?? storage_path('app');
        $filePath = $exportPath . DIRECTORY_SEPARATOR . $fileName;

        $excel = new Excel(['path' => $exportPath]);
        $fileObject = $excel->fileName($fileName);

        // 添加表头
        if (!empty($headers)) {
            $fileObject->header($headers);
        }

        // 添加数据
        $fileObject->data($data);
        $fileObject->output();

        return $filePath;
    }
}