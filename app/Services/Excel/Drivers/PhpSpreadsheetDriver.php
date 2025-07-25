<?php

namespace App\Services\Excel\Drivers;

use App\Exceptions\BusinessException;
use App\Interfaces\ExcelInterface;
use App\Libraries\Common;
use App\Libraries\Snowflake;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * PhpSpreadsheet 驱动类（实现 Excel 导入/导出功能）
 * 基于 PhpOffice/PhpSpreadsheet 库，支持复杂格式 Excel 文件的读写操作
 * 特点：功能全面（支持样式、合并单元格），适合中小数据量（<10万行）的 Excel 处理
 */
class PhpSpreadsheetDriver implements ExcelInterface
{
    /**
     * 导入 Excel 文件并解析为结构化数据
     * @param string $file Excel 文件绝对路径
     * @param array $requiredColumns 必需校验的列名（如 ['姓名', '年龄']，用于验证文件格式）
     * @param array $columnMappings 列名映射规则（如 ['姓名' => 'name', '年龄' => 'age']）
     * @param int $headerLine 表头所在行号（从1开始计数，默认第1行）
     * @return array 解析后的二维数组（键为映射后的字段名，值为对应列数据）
     * @throws BusinessException 当文件缺少必需列时抛出异常
     */
    public function import(string $file, array $requiredColumns = [], array $columnMappings = [], int $headerLine = 1): array
    {
        // 1. 创建 Excel 读取器（自动识别文件类型：xlsx/xls/csv）
        $reader = IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(true); // 仅读取数据（忽略格式）
        $spreadsheet = $reader->load($file); // 加载文件

        // 2. 提取表头数据（指定行号的整行内容）
        $worksheet = $spreadsheet->getActiveSheet();
        // 获取表头行数据（格式：['A' => '姓名', 'B' => '年龄', ...]）
        $header = $worksheet->rangeToArray(
            'A' . $headerLine . ':' . $worksheet->getHighestColumn() . $headerLine,
            null,
            true,
            true,
            true
        )[1]; // 取第一行（索引1）

        // 3. 清理表头末尾的空列（避免因表格末尾空列导致的校验错误）
        foreach (array_reverse($header) as $i => $item) {
            if (is_null($item)) {
                unset($header[$i]); // 移除末尾空列
            } else {
                break; // 遇到非空列时停止清理
            }
        }
        $compareArray = array_values($header); // 重置数组索引

        // 4. 校验必需列是否存在（防止文件格式错误）
        $missingColumns = array_diff($requiredColumns, $compareArray);
        if (count($missingColumns) > 0) {
            foreach ($missingColumns as $missingColumn) {
                throw new BusinessException("Excel文件缺少列:{$missingColumn}");
            }
        }

        // 5. 将工作表转换为行数组（格式：[行号 => ['A' => 值, 'B' => 值, ...]]）
        $rows = $worksheet->toArray(null, true, true, true);

        // 6. 根据列映射规则转换数据格式（关键：将 Excel 列名映射为目标字段名）
        return $this->combine($columnMappings, $rows);
    }

    /**
     * 将 Excel 原始数据按列映射规则转换为目标格式
     * @param array $columnMappings 列名映射规则（如 ['姓名' => 'name']）
     * @param array $data Excel 原始数据（包含表头和数据行）
     * @return array 转换后的二维数组（键为映射后的字段名）
     */
    private function combine(array $columnMappings, array $data): array
    {
        // 提取表头行（第一行数据，格式：['A' => '姓名', 'B' => '年龄']）
        $headerRow = reset($data);

        // 创建列字母到目标字段名的映射（如 ['A' => 'name', 'B' => 'age']）
        $columnMapping = [];
        foreach ($headerRow as $columnLetter => $columnName) {
            if (isset($columnMappings[$columnName])) {
                $columnMapping[$columnLetter] = $columnMappings[$columnName];
            }
        }

        // 处理数据行（跳过表头行）
        $result = [];
        foreach ($data as $rowIndex => $rowData) {
            if ($rowIndex === 1) { // 跳过表头行（索引1）
                continue;
            }

            $mappedRow = [];
            foreach ($columnMapping as $columnLetter => $fieldName) {
                // 仅映射存在的列（避免因空列导致的键不存在错误）
                if (isset($rowData[$columnLetter])) {
                    $mappedRow[$fieldName] = $rowData[$columnLetter];
                }
            }
            $result[] = $mappedRow;
        }

        return $result;
    }

    /**
     * 导出数据为 Excel 文件（XLSX 格式）
     * @param string $fileName 导出文件名（不含扩展名，最终文件名会添加雪花ID和时间戳）
     * @param array $headers 表头配置（格式：[['label' => '表头名称', 'field' => '数据字段名'], ...]）
     * @param array $data 待导出数据（格式：[['字段名' => '值'], ...]）
     * @return string 导出文件的绝对路径（如 storage/exports/12345_20240101120000.xlsx）
     * @throws \Exception 雪花ID生成或文件保存失败时抛出异常
     */
    public function export(string $fileName, array $headers, array $data): string
    {
        // 1. 初始化 Excel 工作簿和活动工作表
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 2. 写入表头（第一行）
        $col = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($col . '1', $header['label']); // 写入表头名称（如 A1 单元格）
            $col++; // 列字母递增（A→B→C...）
        }

        // 3. 填充数据（从第二行开始）
        $row = 2;
        foreach ($data as $item) {
            $col = 'A';
            foreach ($headers as $header) {
                $fieldValue = $item[$header['field']] ?? ''; // 从数据中获取字段值（默认空字符串）
                $sheet->setCellValue($col . $row, $fieldValue); // 写入数据到对应单元格（如 A2、B2）
                $col++;
            }
            $row++; // 行号递增（处理下一条数据）
        }

        // 4. 自动调整列宽（根据内容自适应宽度）
        $this->autoSizeColumns($sheet, count($headers));

        // 5. 生成文件存储路径（确保目录存在）
        $storageDir = storage_path('exports/');
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true); // 递归创建目录（权限755）
        }

        // 6. 生成唯一文件名（雪花ID+时间戳，避免文件名重复）
        $snowflake = new Snowflake(Common::getWorkerId()); // 使用雪花算法生成唯一ID
        $filePath = $storageDir . $snowflake->next() . '_' . date('YmdHis') . '.xlsx';

        // 7. 保存 Excel 文件到本地
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return $filePath;
    }

    /**
     * 自动调整工作表列宽（根据列内容自适应宽度）
     * @param Worksheet $sheet 目标工作表
     * @param int $columnCount 列总数（用于循环处理每一列）
     */
    private function autoSizeColumns(Worksheet $sheet, int $columnCount): void
    {
        // 1. 为每一列启用自动调整宽度
        for ($i = 0; $i < $columnCount; $i++) {
            $columnLetter = Coordinate::stringFromColumnIndex($i + 1); // 列索引转字母（1→A，2→B...）
            $sheet->getColumnDimension($columnLetter)->setAutoSize(true); // 启用自动调整
        }

        // 2. 强制计算列宽（解决大量数据时自动调整不生效的问题）
        for ($col = 1; $col <= $columnCount; $col++) {
            $sheet->calculateColumnWidths(Coordinate::stringFromColumnIndex($col));
        }
    }
}