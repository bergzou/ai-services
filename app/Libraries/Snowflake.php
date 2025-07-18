<?php

namespace App\Libraries;

use Exception;

/**
 * 雪花算法ID生成器（分布式唯一ID生成）
 *
 * 基于Snowflake算法实现，生成64位唯一ID，结构包含：
 * - 41位时间戳（相对于起始时间戳的毫秒差）
 * - 5位工作节点ID（支持最多32个节点）
 * - 12位序列号（每毫秒支持4096个ID）
 * 依赖Redis实现ID唯一性校验和分布式锁
 */
class Snowflake
{
    // 起始时间戳（2018-11-26 15:56:50，用于减少时间戳位数）
    const EPOCH = 1543223810238;
    // 工作节点ID占用的二进制位数（5位支持0-31共32个节点）
    const WORKER_BITS = 5;
    // 序列号占用的二进制位数（12位支持0-4095共4096个序列号/毫秒）
    const SEQUENCE_BITS = 12;
    // 序列号最大值（4095）
    const SEQUENCE_MAX = -1 ^ (-1 << self::SEQUENCE_BITS);
    // 工作节点ID最大值（31）
    const WORKER_MAX = -1 ^ (-1 << self::WORKER_BITS);
    // 最大重复生成尝试次数（连续5次重复则抛出异常）
    const MAX_DUPLICATE_ATTEMPTS = 5;
    // 时间戳左移位数（工作节点位数+序列号位数=17位）
    const TIMESTAMP_SHIFT = self::WORKER_BITS + self::SEQUENCE_BITS;

    // Redis操作实例（用于唯一性校验和锁）
    private Predis $redis;
    // 分布式锁键（防止并发生成冲突）
    private string $lockKey = 'snowflake_lock';
    // 已生成ID的记录键（用于校验唯一性）
    private string $idKey = 'snowflake_id';
    // 工作节点ID（当前节点的唯一标识）
    private $workerId;
    // 当前连续重复生成次数（用于异常控制）
    private int $duplicateAttempts;

    /**
     * 构造函数（初始化工作节点ID和Redis连接）
     * @param mixed $workerId 工作节点ID（需在0-31范围内）
     */
    public function __construct($workerId)
    {
        // 从Predis获取单例Redis实例（复用连接）
        $this->redis = Predis::getInstance();
        // 校验工作节点ID有效性（超出范围可能导致ID冲突）
        if ($workerId < 0 || $workerId > self::WORKER_MAX) {
            throw new Exception("Worker ID must be between 0 and " . self::WORKER_MAX);
        }
        $this->workerId = $workerId;
        $this->duplicateAttempts = 0; // 初始化重复次数为0
    }

    /**
     * 生成下一个唯一ID（主方法）
     * @return int 64位唯一ID
     * @throws Exception 连续生成重复ID超过最大次数时抛出
     */
    public function next(): int
    {
        try {
            // 生成基础雪花ID（时间戳+工作节点ID+序列号）
            $id = $this->generateSnowflakeID();

            // 校验ID是否已存在（通过Redis检查）
            if ($this->isIDExists($id)) {
                // 递归重新生成（可能因时钟回拨或序列号冲突导致重复）
                $id = $this->next();
                // 记录连续重复次数
                $this->duplicateAttempts++;
                // 超过最大允许次数时抛出异常（防止无限递归）
                if ($this->duplicateAttempts >= self::MAX_DUPLICATE_ATTEMPTS) {
                    throw new Exception('Failed to generate unique ID after ' . self::MAX_DUPLICATE_ATTEMPTS . ' attempts');
                }
            } else {
                // 无重复时重置计数
                $this->duplicateAttempts = 0;
            }

            // 标记ID为已生成（防止后续重复）
            $this->markIDAsExists($id);

            return $id;
        } finally {
            // 无论成功与否都释放锁（避免死锁）
            $this->releaseLock();
        }
    }

    /**
     * 生成基础雪花ID（核心算法）
     * @return int 由时间戳、工作节点ID、序列号组合的64位整数
     * @throws Exception 随机数生成失败时抛出
     */
    private function generateSnowflakeID(): int
    {
        // 获取当前时间与起始时间的毫秒差（时间戳部分）
        $timestamp = $this->getTimestamp();
        // 生成0-4095的随机序列号（防止同一毫秒内重复）
        $sequence = random_int(0, self::SEQUENCE_MAX);

        // 组合三部分数据：
        // 时间戳左移17位（工作节点5位+序列号12位）
        // 工作节点ID左移12位（序列号位数）
        // 序列号直接拼接
        return ($timestamp << self::TIMESTAMP_SHIFT) |
            ($this->workerId << self::SEQUENCE_BITS) |
            $sequence;
    }

    /**
     * 计算当前时间与起始时间的毫秒差
     * @return int 毫秒级时间戳差值
     */
    private function getTimestamp(): int
    {
        // 获取当前时间戳（毫秒级）并减去起始时间戳
        return round(microtime(true) * 1000) - self::EPOCH;
    }

    /**
     * 检查ID是否已存在（通过Redis校验）
     * @param int $id 待检查的ID
     * @return bool 存在返回true，否则false
     */
    private function isIDExists(int $id): bool
    {
        // 构造Redis键（格式：snowflake_id:ID值）
        $key = $this->idKey . ':' . $id;
        // 检查键是否存在（存在表示该ID已生成过）
        return $this->redis->exists($key);
    }

    /**
     * 标记ID为已生成（通过Redis记录）
     * @param int $id 已生成的ID
     */
    private function markIDAsExists(int $id)
    {
        // 构造Redis键（格式：snowflake_id:ID值）
        $key = $this->idKey . ':' . $id;
        // 使用Redis锁标记（设置10秒过期，避免内存泄漏）
        $this->redis->lock($key, 10);
    }

    /**
     * 释放分布式锁（防止并发冲突）
     */
    private function releaseLock()
    {
        // 删除锁键（释放资源）
        $this->redis->del($this->lockKey);
    }

    /**
     * 解析雪花ID中的时间戳（转换为可读时间）
     * @param int $snowflakeId 待解析的雪花ID
     * @return string 格式化时间字符串（如：2024-03-20 15:30:45.123）
     */
    public function parseTimestamp(int $snowflakeId): string
    {
        // 提取时间戳部分（右移17位得到相对于起始时间的毫秒差）
        $timestamp = ($snowflakeId >> self::TIMESTAMP_SHIFT) + self::EPOCH;
        // 分离毫秒和秒部分
        $milliseconds = $timestamp % 1000;
        $seconds = (int) ($timestamp / 1000);
        // 格式化为日期时间字符串（包含毫秒）
        $date = date('Y-m-d H:i:s', $seconds);
        return $date . '.' . str_pad($milliseconds, 3, '0', STR_PAD_LEFT);
    }
}