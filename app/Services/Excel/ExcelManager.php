<?php

namespace App\Services\Excel;


use App\Interfaces\ExcelInterface;
use App\Exceptions\BusinessException;

class ExcelManager implements ExcelInterface
{
    protected ExcelInterface $driver;       // 当前使用的翻译驱动实例
    protected array $drivers = []; // 已实例化的驱动缓存（避免重复创建）

    /**
     * 构造函数：初始化默认翻译驱动
     * @param string|null $driver 手动指定的驱动名称（可选）
     * @throws BusinessException
     */
    public function __construct(string $driver = null)
    {
        $driver = $driver ?: config('excel.default');
        $this->driver($driver);
    }

    /**
     * 设置当前使用的翻译驱动（支持动态切换）
     * @param string $driver 驱动名称（对应 config/translation.php 中的驱动键名）
     * @return void 管理器实例（支持链式调用）
     * @throws BusinessException
     */
    private function driver(string $driver): void
    {
        // 若驱动未实例化，则创建并缓存
        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        // 设置当前驱动为缓存中的实例
        $this->driver = $this->drivers[$driver];
    }


    /**
     * @throws BusinessException
     */
    private function createDriver(string $driver)
    {
        $config = config("excel.drivers.{$driver}");

        // 检查驱动配置是否存在
        if (empty($config)) {
            throw new BusinessException("Excel驱动 [{$driver}] 未在配置中定义");
        }

        // 获取驱动类名（配置中的 driver 字段）
        $driverClass = $config['driver'];

        // 检查驱动类是否存在
        if (!class_exists($driverClass)) {
            throw new BusinessException("Excel驱动类 [{$driverClass}] 不存在");
        }

        // 通过Laravel容器解析实例（支持依赖注入）
        return app($driverClass);
    }

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
        return $this->driver->import($file, $requiredColumns, $columnMappings, $headerLine);
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
        return $this->driver->export($fileName, $headers, $data);
    }
}