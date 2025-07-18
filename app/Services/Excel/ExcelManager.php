<?php

namespace App\Services\Excel;

use App\Interfaces\ExcelInterface;
use InvalidArgumentException;

class ExcelManager implements ExcelInterface
{
    protected $drivers = [];
    protected $driverConfig = [];
    protected $defaultDriver;

    public function __construct(string $driver = null)
    {

        $driver = $driver ?: config('excel.default');
        $this->driverConfig = config('excel.drivers');
        // 初始化指定驱动
        $this->driver($driver);


    }

    /**
     * 获取驱动实例
     * @param string|null $driver 驱动名称
     * @return ExcelInterface
     */
    public function driver(string $driver = null): ExcelInterface
    {
        $driver = $driver ?: $this->defaultDriver;

        if (!isset($this->drivers[$driver])) {
            $this->drivers[$driver] = $this->createDriver($driver);
        }

        return $this->drivers[$driver];
    }

    /**
     * 创建驱动实例
     * @param string $driver
     * @return ExcelInterface
     */
    protected function createDriver(string $driver): ExcelInterface
    {

        $config = $this->driverConfig[$driver] ?? [];


        if (empty($config)) {
            throw new InvalidArgumentException("Driver [$driver] not supported.");
        }

        $driverClass = $config['class'];

        // 如果驱动需要特殊配置，传递配置参数
        if (method_exists($driverClass, '__construct') &&
            (new \ReflectionMethod($driverClass, '__construct'))->getNumberOfParameters() > 0) {
            return new $driverClass($config);
        }

        return new $driverClass();
    }

    // 实现接口方法 - 允许在方法级别指定驱动
    public function import(string $filePath, array $options = [], string $driver = null): array
    {
        return $this->driver($driver)->import($filePath, $options);
    }

    public function export(array $data, string $fileName, array $headers = [], string $driver = null): string
    {
        return $this->driver($driver)->export($data, $fileName, $headers);
    }

    /**
     * 根据文件大小自动选择最佳驱动
     * @param string $filePath
     * @return string 驱动名称
     */
    public function autoSelectDriver(string $filePath): string
    {
        $fileSize = filesize($filePath);

        // 小文件使用PhpSpreadsheet（<5MB）
        if ($fileSize < 5 * 1024 * 1024) {
            return 'phpspreadsheet';
        }

        // 超大文件使用Vtiful（>50MB）
        if ($fileSize > 50 * 1024 * 1024) {
            return 'vtiful';
        }

        // 中等文件使用Spout
        return 'spout';
    }

    /**
     * 智能导入 - 自动选择驱动
     */
    public function smartImport(string $filePath, array $options = []): array
    {
        $driver = $this->autoSelectDriver($filePath);
        return $this->driver($driver)->import($filePath, $options);
    }

    /**
     * 智能导出 - 根据数据量自动选择驱动
     */
    public function smartExport(array $data, string $fileName, array $headers = []): string
    {
        // 小数据量（<1000行）使用PhpSpreadsheet
        if (count($data) <= 1000) {
            return $this->driver('phpspreadsheet')->export($data, $fileName, $headers);
        }

        // 超大数据量（>10万行）使用Vtiful
        if (count($data) > 100000) {
            return $this->driver('vtiful')->export($data, $fileName, $headers);
        }

        // 中等数据量使用Spout
        return $this->driver('spout')->export($data, $fileName, $headers);
    }
}