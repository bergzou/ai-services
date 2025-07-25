<?php

namespace App\Services\Excel\Drivers;


use App\Exceptions\BusinessException;
use App\Interfaces\ExcelInterface;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PhpSpreadsheetDriver implements ExcelInterface
{

    /**
     * 导入 Excel 文件并解析为数组数据
     * @param string $file Excel 文件绝对路径
     * @param array $requiredColumns 必需的列名（用于校验文件格式）
     * @param array $columnMappings 列名映射（如 ['姓名' => 'name']）
     * @param int $headerLine 表头所在行号（默认第1行）
     * @return array 解析后的二维数组数据（行×列）
     * @throws BusinessException
     */
    public function import(string $file, array $requiredColumns = [], array $columnMappings = [], int $headerLine = 1): array
    {
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($file);


        $worksheet = $spreadsheet->getActiveSheet();
        $header = $worksheet->rangeToArray('A'.$headerLine.':' . $worksheet->getHighestColumn() . $headerLine, null, true, true, true)[1];


        foreach (array_reverse($header) as $i => $item) {
            if (is_null($item)) {
                unset($header[$i]);
            } else {
                break;
            }
        }
        $compareArray = array_values($header);
        $missingColumns = array_diff($requiredColumns, $compareArray);


        if (count($missingColumns) > 0) {
            foreach ($missingColumns as $missingColumn) {
                throw new BusinessException("Excel文件缺少列:{$missingColumn}");
            }
        }

        // 将工作表转换为行数组
        $rows = $worksheet->toArray(null, true, true, true);

        return $this->combine($columnMappings, $rows);
    }


    function combine(array $columnMappings, array $data): array
    {
        // 提取第一行作为标题行
        $headerRow = reset($data);

        // 创建列字母到目标字段名的映射关系
        $columnMapping = [];
        foreach ($headerRow as $columnLetter => $columnName) {
            if (isset($columnMappings[$columnName])) {
                $columnMapping[$columnLetter] = $columnMappings[$columnName];
            }
        }

        // 处理数据行
        $result = [];
        foreach ($data as $rowIndex => $rowData) {
            // 跳过标题行（索引1）
            if ($rowIndex === 1) continue;

            $mappedRow = [];
            foreach ($columnMapping as $columnLetter => $fieldName) {
                // 只映射存在的列
                if (isset($rowData[$columnLetter])) {
                    $mappedRow[$fieldName] = $rowData[$columnLetter];
                }
            }
            $result[] = $mappedRow;
        }


        return $result;
    }

    /**
     * 导出数据为 Excel 文件
     * @param string $fileName 导出文件名（不含扩展名）
     * @param array $data 待导出的二维数组数据（行×列）
     * @param array $headers 表头，数组格式，每个元素包含两个属性：label和field，分别表示表头名称和对应的数据库字段名
     * @return string 导出文件的绝对路径
     * @throws \Exception
     */
    public function export(string $fileName, array $headers, array $data): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 设置表头
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col.'1', $header['label']);
            $col++;
        }

        // 填充数据
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($headers as $header) {
                $fieldValue = $item[$header['field']] ?? '';
                $sheet->setCellValue($col.$row, $fieldValue);
                $col++;
            }
            $row++;
        }

        // 自动调整列宽
        $this->autoSizeColumns($sheet, count($headers));


        $storageDir = storage_path('exports/');
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $snowflake = new Snowflake(Common::getWorkerId());

        $filePath = $storageDir . $snowflake->next() . '_' . date('YmdHis') . '.xlsx';

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * 自动调整列宽
     */
    private function autoSizeColumns($sheet, int $columnCount): void
    {
        for ($i = 0; $i < $columnCount; $i++) {
            $columnLetter = Coordinate::stringFromColumnIndex($i + 1);
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
        }

        // 强制计算列宽（对于大量数据很重要）
        for ($col = 1; $col <= $columnCount; $col++) {
            $sheet->calculateColumnWidths(
                Coordinate::stringFromColumnIndex($col)
            );
        }
    }
}