<?php
namespace App\Libraries;

use Exception;

/**
 * 业务编码生成器（基于Redis的递增序列）
 * 用于生成带前缀、时间戳、自增序列号的唯一业务编码（如订单号、流水号）
 * 支持单例模式，通过Redis保证分布式环境下的唯一性和递增性
 * 示例：setPrefix('ORDER')->setTime(true)->setSymbol('-')->getCode() => "ORDER-20240320-0001"
 */
class Generator
{
    // 业务场景标识（必填，区分不同业务的编码序列）
    protected string $key = '';
    // 编码前缀（可选，如"ORDER"）
    protected string $prefix = '';
    // 时间部分（可选，格式自定义，支持自动填充当前日期）
    protected string $time = '';
    // 连接符（各部分之间的分隔符号，如"-"）
    protected string $symbol = '';
    // 序列号填充长度（不足时用$padString补全，默认4位）
    protected int $fillLength = 4;
    // 填充字符（默认用"0"补全，如0001）
    protected string $padString = '0';

    // 单例实例存储数组（支持子类扩展）
    private static array $instances = [];

    /**
     * 私有构造函数（禁止外部实例化，强制使用单例）
     */
    private function __construct() {}

    /**
     * 获取单例实例（支持子类继承）
     * @return self 类实例
     */
    public static function getInstance(): self
    {
        $cls = static::class; // 获取当前类名（支持子类）
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static(); // 实例化当前类（支持子类）
        }
        return self::$instances[$cls];
    }

    /**
     * 设置业务场景标识（必填）
     * @param string $key 业务标识（如"order_code"）
     * @return self 支持链式调用
     */
    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * 设置编码前缀（可选）
     * @param string $prefix 前缀内容（如"ORDER"）
     * @return self 支持链式调用
     */
    public function setPrefix(string $prefix): self
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * 设置连接符（可选）
     * @param string $symbol 连接符（如"-"，默认空字符串）
     * @return self 支持链式调用
     */
    public function setSymbol(string $symbol = ''): self
    {
        $this->symbol = $symbol;
        return $this;
    }

    /**
     * 设置序列号填充长度（最小1位）
     * @param int $length 填充长度（如4表示生成0001格式）
     * @return self 支持链式调用
     */
    public function setFillLength(int $length = 4): self
    {
        $this->fillLength = max(1, $length); // 确保最小长度为1
        return $this;
    }

    /**
     * 设置时间部分（可选）
     * @param mixed $time 时间参数：
     *                    - true: 自动填充当前日期（格式Ymd，如20240320）
     *                    - false: 不使用时间部分
     *                    - 字符串: 自定义时间（如"202403"）
     * @return self 支持链式调用
     */
    public function setTime($time = ''): self
    {
        if ($time === true) {
            $this->time = date('Ymd'); // 自动填充当前日期
        } elseif ($time === false) {
            $this->time = ''; // 禁用时间部分
        } else {
            $this->time = (string)$time; // 自定义时间字符串
        }
        return $this;
    }

    /**
     * 重置所有配置（避免状态污染）
     */
    public function reset(): void
    {
        $this->key = '';
        $this->prefix = '';
        $this->time = '';
        $this->symbol = '';
        $this->fillLength = 4;
        $this->padString = '0';
    }

    /**
     * 生成单个唯一业务编码（核心方法）
     * @return string 格式化后的业务编码（如"ORDER-20240320-0001"）
     * @throws Exception 未设置key时抛出异常
     */
    public function getCode(): string
    {
        if (empty($this->key)) {
            throw new Exception('Key must be set before generating code');
        }

        $id = $this->getId(); // 获取Redis递增ID
        $paddedId = str_pad($id, $this->fillLength, $this->padString, STR_PAD_LEFT); // 填充序列号

        // 组合各部分（过滤空值）
        $parts = array_filter([$this->prefix, $this->time, $paddedId]);
        $code = implode($this->symbol, $parts); // 用连接符拼接

        $this->reset(); // 自动重置状态，避免下次调用污染
        return $code;
    }

    /**
     * 生成批量唯一业务编码（连续序列号）
     * @param int $count 需要生成的编码数量（如10）
     * @return array 编码数组（如["ORDER-0001", "ORDER-0002", ...]）
     */
    public function getBatchCode(int $count): array
    {
        $ids = $this->getBatchId($count); // 获取批量递增ID
        $codes = [];

        foreach ($ids as $id) {
            $paddedId = str_pad($id, $this->fillLength, $this->padString, STR_PAD_LEFT); // 填充序列号
            $parts = array_filter([$this->prefix, $this->time, $paddedId]); // 组合各部分
            $codes[] = implode($this->symbol, $parts); // 拼接编码
        }

        $this->reset(); // 自动重置状态
        return $codes;
    }

    /**
     * 获取单个递增ID（Redis实现）
     * @return int 递增的数值ID（如1,2,3...）
     */
    private function getId(): int
    {
        // 构造Redis键（格式：getNumber:服务ID:业务key[:日期]）
        $key = 'getNumber:' . getenv('SERVICE_ID') . ':' . $this->key;
        if (!empty($this->time)) {
            $key .= ':' . date('Ymd'); // 带时间部分时添加日期，确保每天独立递增
        }

        $redis = Predis::getInstance(); // 获取Redis单例
        $id = $redis->incr($key); // 原子递增操作（保证分布式唯一性）

        // 首次生成时设置过期时间（避免Redis键无限增长）
        if ($id === 1 && !empty($this->time)) {
            $expire = strtotime('tomorrow') - time() + 10; // 过期时间设置为次日+10秒（容错）
            $redis->expire($key, $expire);
        }

        return $id;
    }

    /**
     * 获取批量递增ID（连续数值）
     * @param int $count 需要获取的ID数量
     * @return array 连续ID数组（如[1,2,3]）
     */
    private function getBatchId(int $count): array
    {
        // 构造Redis键（同单个ID逻辑）
        $key = 'getNumber:' . getenv('SERVICE_ID') . ':' . $this->key;
        if (!empty($this->time)) {
            $key .= ':' . date('Ymd');
        }

        $redis = Predis::getInstance();
        $startId = $redis->incrby($key, $count); // 原子递增count次，返回最终值

        // 计算连续ID范围（如count=3，startId=3 → [1,2,3]）
        $ids = range($startId - $count + 1, $startId);

        // 首次生成时设置过期时间（同单个ID逻辑）
        if ($startId === $count && !empty($this->time)) {
            $expire = strtotime('tomorrow') - time() + 10;
            $redis->expire($key, $expire);
        }

        return $ids;
    }
}