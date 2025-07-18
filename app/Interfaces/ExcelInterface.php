<?php

namespace App\Interfaces;

/**
 * Excel 服务接口：定义 Excel 文件导入/导出功能的标准方法
 * 所有 Excel 驱动实现类（如 Vtiful 驱动、PhpSpreadsheet 驱动）需实现此接口
 */
interface ExcelInterface
{
    /**
     * 从 Excel 文件中导入数据
     * @param string $filePath 待导入的 Excel 文件绝对路径
     * @param array $options 导入配置选项（可选）
     *                       支持参数：'start_row' => int（数据起始行，从0开始计数，默认0）
     * @return array 二维数组格式的表格数据（[[行1列1, 行1列2...], [行2列1, 行2列2...]]）
     */
    public function import(string $filePath, array $options = []): array;

    /**
     * 将数据导出为 Excel 文件
     * @param array $data 待导出的二维数组数据（[[行1列1, 行1列2...], [行2列1, 行2列2...]]）
     * @param string $fileName 生成的 Excel 文件名（含扩展名，如 "report.xlsx"）
     * @param array $headers 表头信息数组（可选，如 ['姓名', '年龄']，若提供则会在首行添加表头）
     * @return string 生成的 Excel 文件完整存储路径（可通过此路径访问生成的文件）
     */
    public function export(array $data, string $fileName, array $headers = []): string;
}