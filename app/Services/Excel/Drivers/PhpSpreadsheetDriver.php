<?php

namespace App\Services\Excel\Drivers;


use App\Interfaces\ExcelInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PhpSpreadsheetDriver implements ExcelInterface
{
    public function import(string $filePath, array $options = []): array
    {
        var_dump($filePath);
        var_dump($options);die;
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $startRow = $options['start_row'] ?? 1;
        $data = [];

        foreach ($sheet->getRowIterator($startRow) as $row) {
            $rowData = [];
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);

            foreach ($cellIterator as $cell) {
                $rowData[] = $cell->getValue();
            }
            $data[] = $rowData;
        }

        return $data;
    }

    public function export(array $data, string $fileName, array $headers = []): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 添加表头
        if (!empty($headers)) {
            $sheet->fromArray($headers, null, 'A1');
        }

        // 添加数据
        $startRow = empty($headers) ? 1 : 2;
        $sheet->fromArray($data, null, 'A' . $startRow);

        $writer = new Xlsx($spreadsheet);
        $filePath = storage_path('app/' . $fileName);
        $writer->save($filePath);

        return $filePath;
    }
}