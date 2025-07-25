<?php

namespace App\Services\Excel\Drivers;


use App\Interfaces\ExcelInterface;


class VtifulDriver implements ExcelInterface
{

    /**
     * 导入 Excel 文件并解析为数组数据
     * @param string $file
     * @param array $requiredColumns 必需的列名（用于校验文件格式）
     * @param array $columnMappings 列名映射（如 ['姓名' => 'name']）
     * @param int $headerLine 表头所在行号（默认第1行）
     * @return array 解析后的二维数组数据（行×列）
     */
    public function import(string $file, array $requiredColumns = [], array $columnMappings = [], int $headerLine = 1): array
    {
        // TODO: Implement import() method.
    }

    /**
     * 导出数据为 Excel 文件
     * @param string $fileName 导出文件名（不含扩展名）
     * @param array $headers 表头，数组格式，每个元素包含两个属性：label和field，分别表示表头名称和对应的数据库字段名
     * @param array $data 待导出的二维数组数据（行×列）
     * @return string 导出文件的绝对路径
     */
    public function export(string $fileName, array $headers, array $data): string
    {
        // TODO: Implement export() method.
    }
}