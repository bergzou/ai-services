<?php

namespace App\Libraries;

use Illuminate\Support\Facades\Redis;
use Illuminate\Redis\Connections\Connection;

/**
 * Redis操作工具类（单例模式）
 *
 * 封装Redis常用操作方法，支持字符串、哈希、列表、集合、有序集合等数据结构操作
 * 通过单例模式确保全局唯一Redis连接实例，避免重复创建连接
 */
class Predis
{
    /** @var Connection Redis连接实例（通过Laravel Redis门面获取） */
    private Connection $redis;

    /** @var Predis 类单例实例（确保全局唯一） */
    private static Predis $instance;

    /**
     * 私有构造函数（防止外部直接实例化）
     * 初始化时通过Laravel Redis门面获取默认连接实例
     */
    private function __construct()
    {
        $this->redis = Redis::connection();
    }

    /**
     * 私有克隆方法（防止通过克隆创建新实例）
     */
    private function __clone()
    {
    }

    /**
     * 获取单例实例（全局唯一）
     * @return Predis 类实例
     */
    public static function getInstance(): Predis
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // ======================== 字符串操作 ======================== //

    /**
     * 设置字符串值
     * @param string $key 键名
     * @param mixed $value 值
     * @return bool 是否成功
     */
    public function set(string $key, $value): bool
    {
        return (bool)$this->redis->set($key, $value);
    }

    /**
     * 获取字符串值
     * @param string $key 键名
     * @return mixed|null 值（键不存在返回null）
     */
    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    /**
     * 设置带过期时间的字符串值
     * @param string $key 键名
     * @param mixed $value 值
     * @param int $ttl 过期时间（秒）
     * @return bool 是否成功
     */
    public function setex(string $key, $value, int $ttl): bool
    {
        return (bool)$this->redis->setex($key, $ttl, $value);
    }

    /**
     * 自增操作
     * @param string $key 键名
     * @param int $increment 自增值（默认为1）
     * @return int 自增后的值
     */
    public function incr(string $key, int $increment = 1): int
    {
        return $this->redis->incrby($key, $increment);
    }

    /**
     * 自减操作
     * @param string $key 键名
     * @param int $decrement 自减值（默认为1）
     * @return int 自减后的值
     */
    public function decr(string $key, int $decrement = 1): int
    {
        return $this->redis->decrby($key, $decrement);
    }

    // ======================== 哈希操作 ======================== //

    /**
     * 设置哈希字段值
     * @param string $key 键名
     * @param string $field 字段名
     * @param mixed $value 值
     * @return int 1=新字段 0=更新字段
     */
    public function hset(string $key, string $field, $value): int
    {
        return $this->redis->hset($key, $field, $value);
    }

    /**
     * 获取哈希字段值
     * @param string $key 键名
     * @param string $field 字段名
     * @return mixed|null 字段值（字段不存在返回null）
     */
    public function hget(string $key, string $field)
    {
        return $this->redis->hget($key, $field);
    }

    /**
     * 获取哈希所有字段和值
     * @param string $key 键名
     * @return array 字段=>值 的关联数组
     */
    public function hgetall(string $key): array
    {
        return $this->redis->hgetall($key);
    }

    /**
     * 删除哈希字段
     * @param string $key 键名
     * @param mixed $fields 字段名（可多个）
     * @return int 被删除字段数量
     */
    public function hdel(string $key, ...$fields): int
    {
        return $this->redis->hdel($key, ...$fields);
    }

    // ======================== 列表操作 ======================== //

    /**
     * 左推入列表
     * @param string $key 键名
     * @param mixed $values 要推入的值（可多个）
     * @return int 推入后列表长度
     */
    public function lpush(string $key, ...$values): int
    {
        return $this->redis->lpush($key, ...$values);
    }

    /**
     * 右推入列表
     * @param string $key 键名
     * @param mixed $values 要推入的值（可多个）
     * @return int 推入后列表长度
     */
    public function rpush(string $key, ...$values): int
    {
        return $this->redis->rpush($key, ...$values);
    }

    /**
     * 左弹出列表元素
     * @param string $key 键名
     * @return mixed|null 弹出的值（列表空返回null）
     */
    public function lpop(string $key)
    {
        return $this->redis->lpop($key);
    }

    /**
     * 右弹出列表元素
     * @param string $key 键名
     * @return mixed|null 弹出的值（列表空返回null）
     */
    public function rpop(string $key)
    {
        return $this->redis->rpop($key);
    }

    /**
     * 获取列表长度
     * @param string $key 键名
     * @return int 列表长度
     */
    public function llen(string $key): int
    {
        return $this->redis->llen($key);
    }

    /**
     * 获取列表片段
     * @param string $key 键名
     * @param int $start 起始索引
     * @param int $stop 结束索引（-1表示末尾）
     * @return array 元素数组
     */
    public function lrange(string $key, int $start, int $stop): array
    {
        return $this->redis->lrange($key, $start, $stop);
    }

    // ======================== 集合操作 ======================== //

    /**
     * 添加集合成员
     * @param string $key 键名
     * @param mixed $members 成员（可多个）
     * @return int 成功添加的成员数量
     */
    public function sadd(string $key, ...$members): int
    {
        return $this->redis->sadd($key, ...$members);
    }

    /**
     * 移除集合成员
     * @param string $key 键名
     * @param mixed $members 成员（可多个）
     * @return int 成功移除的成员数量
     */
    public function srem(string $key, ...$members): int
    {
        return $this->redis->srem($key, ...$members);
    }

    /**
     * 获取集合所有成员
     * @param string $key 键名
     * @return array 成员数组
     */
    public function smembers(string $key): array
    {
        return $this->redis->smembers($key);
    }

    /**
     * 求多个集合的交集
     * @param array $keys 键名数组
     * @return array 交集成员数组
     */
    public function sinter(array $keys): array
    {
        return $this->redis->sinter(...$keys);
    }

    /**
     * 求多个集合的并集
     * @param array $keys 键名数组
     * @return array 并集成员数组
     */
    public function sunion(array $keys): array
    {
        return $this->redis->sunion(...$keys);
    }

    /**
     * 求多个集合的差集
     * @param array $keys 键名数组（第一个集合为基准）
     * @return array 差集成员数组
     */
    public function sdiff(array $keys): array
    {
        return $this->redis->sdiff(...$keys);
    }

    // ======================== 有序集合操作 ======================== //

    /**
     * 添加有序集合成员
     * @param string $key 键名
     * @param float $score 分数
     * @param mixed $member 成员
     * @return int 成功添加的成员数量
     */
    public function zadd(string $key, float $score, $member): int
    {
        return $this->redis->zadd($key, [$member => $score]);
    }

    /**
     * 按分数排序获取有序集合成员
     * @param string $key 键名
     * @param int $start 起始排名
     * @param int $stop 结束排名（-1表示末尾）
     * @param bool $withScores 是否返回分数
     * @return array 成员数组（带分数时为[成员=>分数]的关联数组）
     */
    public function zrange(string $key, int $start, int $stop, bool $withScores = false): array
    {
        $options = $withScores ? ['WITHSCORES' => true] : [];
        return $this->redis->zrange($key, $start, $stop, $options);
    }

    /**
     * 按分数倒序获取有序集合成员
     * @param string $key 键名
     * @param int $start 起始排名
     * @param int $stop 结束排名（-1表示末尾）
     * @param bool $withScores 是否返回分数
     * @return array 成员数组（带分数时为[成员=>分数]的关联数组）
     */
    public function zrevrange(string $key, int $start, int $stop, bool $withScores = false): array
    {
        $options = $withScores ? ['WITHSCORES' => true] : [];
        return $this->redis->zrevrange($key, $start, $stop, $options);
    }

    /**
     * 移除有序集合成员
     * @param string $key 键名
     * @param mixed $members 成员（可多个）
     * @return int 成功移除的成员数量
     */
    public function zrem(string $key, ...$members): int
    {
        return $this->redis->zrem($key, ...$members);
    }

    // ======================== 其他功能 ======================== //

    /**
     * 设置键过期时间
     * @param string $key 键名
     * @param int $ttl 过期时间（秒）
     * @return bool 是否成功
     */
    public function expire(string $key, int $ttl): bool
    {
        return (bool)$this->redis->expire($key, $ttl);
    }

    /**
     * 删除键
     * @param mixed $keys 键名（可多个）
     * @return int 成功删除的键数量
     */
    public function del(...$keys): int
    {
        return $this->redis->del(...$keys);
    }

    /**
     * 检查键是否存在
     * @param string $key 键名
     * @return bool 是否存在
     */
    public function exists(string $key): bool
    {
        return (bool)$this->redis->exists($key);
    }

    /**
     * 发布消息到频道
     * @param string $channel 频道名称
     * @param mixed $message 消息内容
     * @return int 接收到消息的订阅者数量
     */
    public function publish(string $channel, $message): int
    {
        return $this->redis->publish($channel, $message);
    }


    /**
     * Redis 分布式锁（基于 setnx + expire 实现）
     * 用于在分布式系统中保证资源的互斥访问（如防止并发重复操作）
     * @param string $key 锁的唯一标识键（如 "order_lock_123"）
     * @param int $expire 锁的自动过期时间（秒，防止进程崩溃未释放锁导致死锁，默认10秒）
     * @param string $value 锁的值（建议使用唯一标识，如请求ID，避免误删其他进程的锁）
     * @return bool 加锁成功返回 true，失败返回 false
     * @note 注意：此实现使用 setnx + expire 组合命令（非原子操作），极端情况下可能存在锁未设置过期时间的风险
     *       更推荐使用 Redis 2.6.12+ 支持的 set(key, value, 'NX', 'EX', expire) 原子命令
     */
    public function lock(string $key, int $expire = 10, string $value = '1'): bool
    {
        try {
            // 尝试通过 setnx 原子操作设置锁（仅当键不存在时设置成功）
            $result = $this->redis->setnx($key, $value);
            // 若设置成功，立即为锁设置过期时间（防止进程崩溃未释放锁）
            if ($result)  $this->redis->expire($key, $expire);
            return $result;
        } catch (\Throwable $e) {
            // 首次操作异常时，短暂等待（100微秒）后重试（提高容错性）
            usleep(100);
            try {
                // 重试设置锁
                $result = $this->redis->setnx($key, $value);
                if ($result)  $this->redis->expire($key, $expire);
                return $result;
            } catch (\Throwable $e) {
                // 两次尝试均失败，返回加锁失败
                return false;
            }
        }
    }
}