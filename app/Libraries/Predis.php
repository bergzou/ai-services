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
     * 私有反序列化方法（防止通过反序列化创建新实例）
     */
    private function __wakeup()
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
     * 设置字符串类型键值对（覆盖已存在的键）
     * @param string $key 键名
     * @param mixed $value 键值（支持字符串/数字等可序列化类型）
     * @param int $expire 过期时间（秒，0表示不设置）
     * @return bool|string 成功返回"OK"，失败返回false
     */
    public function set(string $key, $value, int $expire = 0)
    {
        return $this->redis->set($key, $value,$expire);
    }

    /**
     * 仅在键不存在时设置值
     * @param string $key 键名
     * @param mixed $value 键值
     * @return bool 成功返回true，键已存在返回false
     */
    public function setnx(string $key, $value): bool
    {
        return (bool)$this->redis->setnx($key, $value);
    }

    /**
     * 判断键是否存在
     * @param string|array $keys 键名（支持单个键名或键名数组）
     * @return int 存在的键数量
     */
    public function exists($keys): int
    {
        return $this->redis->exists($keys);
    }

    /**
     * 删除指定键（支持单个或多个键）
     * @param string|array ...$keys 要删除的键名（可传入多个参数）
     * @return int 删除的键数量
     */
    public function del(...$keys): int
    {
        return $this->redis->del(...$keys);
    }

    /**
     * 查找符合模式的键（生产环境慎用，可能影响性能）
     * @param string $pattern 匹配模式（如"user:*"）
     * @return array 匹配的键名数组
     */
    public function keys(string $pattern): array
    {
        return $this->redis->keys($pattern);
    }

    /**
     * 获取字符串类型键的值
     * @param string $key 键名
     * @return mixed 键值（不存在返回null）
     */
    public function get(string $key)
    {
        return $this->redis->get($key);
    }

    /**
     * 批量获取多个键的值
     * @param array $keys 键名数组（如['key1', 'key2']）
     * @return array 值数组（顺序与输入键对应，不存在的键值为null）
     */
    public function mget(array $keys): array
    {
        return $this->redis->mget($keys);
    }

    /**
     * 对键值进行原子递增（初始值为0时递增后为1）
     * @param string $key 键名
     * @return int 递增后的值
     */
    public function incr(string $key): int
    {
        return $this->redis->incr($key);
    }

    /**
     * 对键值进行原子递减
     * @param string $key 键名
     * @return int 递减后的值
     */
    public function decr(string $key): int
    {
        return $this->redis->decr($key);
    }

    /**
     * 对键值按指定步长递增
     * @param string $key 键名
     * @param int $value 递增步长
     * @return int 递增后的值
     */
    public function incrBy(string $key, int $value): int
    {
        return $this->redis->incrby($key, $value);
    }

    /**
     * 对键值按指定步长递减
     * @param string $key 键名
     * @param int $value 递减步长
     * @return int 递减后的值
     */
    public function decrBy(string $key, int $value): int
    {
        return $this->redis->decrby($key, $value);
    }

    /**
     * 获取键的剩余过期时间
     * @param string $key 键名
     * @return int 剩余秒数（-1表示永不过期，-2表示键不存在）
     */
    public function ttl(string $key): int
    {
        return $this->redis->ttl($key);
    }

    /**
     * 移除键的过期时间
     * @param string $key 键名
     * @return bool 成功返回true，键不存在或未设置过期时间返回false
     */
    public function persist(string $key): bool
    {
        return $this->redis->persist($key);
    }

    // ======================== 哈希操作 ======================== //

    /**
     * 设置哈希表中指定字段的值（存在则覆盖）
     * @param string $key 哈希表键名
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @return int 1表示新字段，0表示覆盖已有字段
     */
    public function hSet(string $key, string $field, $value): int
    {
        return $this->redis->hSet($key, $field, $value);
    }

    /**
     * 仅当字段不存在时设置哈希值
     * @param string $key 哈希表键名
     * @param string $field 字段名
     * @param mixed $value 字段值
     * @return bool 成功设置返回true，字段已存在返回false
     */
    public function hSetNx(string $key, string $field, $value): bool
    {
        return $this->redis->hSetNx($key, $field, $value);
    }

    /**
     * 批量设置哈希表字段
     * @param string $key 哈希表键名
     * @param array $data 字段数组（键值对形式：['field1' => 'val1', 'field2' => 'val2']）
     * @param int $expire 过期时间（秒，0表示不设置）
     * @return bool 操作是否成功
     */
    public function hMSet(string $key, array $data, int $expire = 0): bool
    {
        $result = $this->redis->hMSet($key, $data);
        if ($expire > 0) {
            $this->redis->expire($key, $expire);
        }
        return $result;
    }

    /**
     * 更新键的过期时间
     * @param string $key 键名
     * @param int $expire 过期时间（秒）
     * @return bool 成功返回true，键不存在返回false
     */
    public function expire(string $key, int $expire): bool
    {
        return $this->redis->expire($key, $expire);
    }

    /**
     * 获取哈希表中指定字段的值
     * @param string $key 哈希表键名
     * @param string $field 字段名
     * @return mixed 字段值（不存在返回null）
     */
    public function hGet(string $key, string $field)
    {
        return $this->redis->hGet($key, $field);
    }

    /**
     * 批量获取哈希表中多个字段的值
     * @param string $key 哈希表键名
     * @param array $fields 字段名数组（如['field1', 'field2']）
     * @return array 值数组（顺序与输入字段对应，不存在的字段值为null）
     */
    public function hMGet(string $key, array $fields): array
    {
        return $this->redis->hMGet($key, $fields);
    }

    /**
     * 删除哈希表中指定字段
     * @param string $key 哈希表键名
     * @param string|array ...$fields 字段名（支持多个参数）
     * @return int 删除的字段数量
     */
    public function hDel(string $key, ...$fields): int
    {
        return $this->redis->hDel($key, ...$fields);
    }

    /**
     * 获取哈希表中所有字段名
     * @param string $key 哈希表键名
     * @return array 字段名数组（空数组表示哈希表不存在或无字段）
     */
    public function hKeys(string $key): array
    {
        return $this->redis->hKeys($key);
    }

    /**
     * 获取哈希表中所有字段值
     * @param string $key 哈希表键名
     * @return array 字段值数组
     */
    public function hVals(string $key): array
    {
        return $this->redis->hVals($key);
    }

    /**
     * 获取哈希表所有字段和值（键值对形式）
     * @param string $key 哈希表键名
     * @return array 哈希表数据
     */
    public function hGetAll(string $key): array
    {
        return $this->redis->hGetAll($key);
    }

    /**
     * 检查哈希表中是否存在指定字段
     * @param string $key 哈希表键名
     * @param string $field 字段名
     * @return bool 存在返回true，不存在返回false
     */
    public function hExists(string $key, string $field): bool
    {
        return $this->redis->hExists($key, $field);
    }

    /**
     * 对哈希表中指定字段的值进行原子递增
     * @param string $key 哈希表键名
     * @param string $field 字段名
     * @param int $value 递增值（支持负数实现递减）
     * @return int 递增后的值
     */
    public function hIncrBy(string $key, string $field, int $value): int
    {
        return $this->redis->hIncrBy($key, $field, $value);
    }

    /**
     * 对哈希表中指定字段的值进行浮点数递增
     * @param string $key 哈希表键名
     * @param string $field 字段名
     * @param float $value 递增值
     * @return float 递增后的值
     */
    public function hIncrByFloat(string $key, string $field, float $value): float
    {
        return $this->redis->hIncrByFloat($key, $field, $value);
    }

    /**
     * 获取哈希表中字段数量
     * @param string $key 哈希表键名
     * @return int 字段数量
     */
    public function hLen(string $key): int
    {
        return $this->redis->hLen($key);
    }

    // ======================== 列表操作 ======================== //

    /**
     * 将一个或多个值插入到列表头部
     * @param string $key 列表键名
     * @param mixed ...$values 要插入的值（支持多个参数）
     * @return int 插入后列表的长度
     */
    public function lPush(string $key, ...$values): int
    {
        return $this->redis->lPush($key, ...$values);
    }

    /**
     * 将值插入已存在的列表头部（列表不存在则不操作）
     * @param string $key 列表键名
     * @param mixed $value 要插入的值
     * @return int 插入后列表的长度（0表示列表不存在）
     */
    public function lPushx(string $key, $value): int
    {
        return $this->redis->lPushx($key, $value);
    }

    /**
     * 将一个或多个值插入到列表尾部
     * @param string $key 列表键名
     * @param mixed ...$values 要插入的值（支持多个参数）
     * @param int $expire 过期时间（秒，0表示不设置）
     * @return int 插入后列表的长度
     */
    public function rPush(string $key, $value, int $expire = 0): int
    {
        $result = $this->redis->rPush($key, ...func_get_args());
        if ($expire > 0) {
            $this->redis->expire($key, $expire);
        }
        return $result;
    }

    /**
     * 将值插入已存在的列表尾部（列表不存在则不操作）
     * @param string $key 列表键名
     * @param mixed $value 要插入的值
     * @return int 插入后列表的长度（0表示列表不存在）
     */
    public function rPushx(string $key, $value): int
    {
        return $this->redis->rPushx($key, $value);
    }

    /**
     * 移除并返回列表的第一个元素
     * @param string $key 列表键名
     * @return string|null 列表的第一个元素（列表为空返回null）
     */
    public function lPop(string $key): ?string
    {
        return $this->redis->lPop($key);
    }

    /**
     * 移除并返回列表的最后一个元素
     * @param string $key 列表键名
     * @return string|null 列表的最后一个元素（列表为空返回null）
     */
    public function rPop(string $key): ?string
    {
        return $this->redis->rPop($key);
    }

    /**
     * 阻塞式弹出列表的第一个元素（支持多个列表）
     * @param array $keys 要监听的列表键名数组
     * @param int $timeout 超时时间（秒，0表示永久阻塞）
     * @return array|null 弹出的键名和值（超时返回null）
     */
    public function blPop(array $keys, int $timeout): ?array
    {
        return $this->redis->blPop($keys, $timeout);
    }

    /**
     * 阻塞式弹出列表的最后一个元素（支持多个列表）
     * @param array $keys 要监听的列表键名数组
     * @param int $timeout 超时时间（秒，0表示永久阻塞）
     * @return array|null 弹出的键名和值（超时返回null）
     */
    public function brPop(array $keys, int $timeout): ?array
    {
        return $this->redis->brPop($keys, $timeout);
    }

    /**
     * 获取列表的长度
     * @param string $key 列表键名
     * @return int 列表长度（键不存在返回0）
     */
    public function lLen(string $key): int
    {
        return $this->redis->lLen($key);
    }

    /**
     * 获取列表指定区间的元素（支持负索引）
     * @param string $key 列表键名
     * @param int $start 起始索引（0表示第一个元素）
     * @param int $end 结束索引（-1表示最后一个元素）
     * @return array 元素数组（区间无元素返回空数组）
     */
    public function lRange(string $key, int $start, int $end): array
    {
        return $this->redis->lRange($key, $start, $end);
    }

    /**
     * 移除列表中与指定值相等的元素
     * @param string $key 列表键名
     * @param mixed $value 要移除的值
     * @param int $count 移除数量（>0从左到右，<0从右到左，0移除所有）
     * @return int 实际移除的元素数量
     */
    public function lRem(string $key, $value, int $count): int
    {
        return $this->redis->lRem($key, $value, $count);
    }

    /**
     * 通过索引设置列表元素的值
     * @param string $key 列表键名
     * @param int $index 元素索引（负索引表示从尾部开始）
     * @param mixed $value 新值
     * @return bool 操作是否成功（索引超出范围返回false）
     */
    public function lSet(string $key, int $index, $value): bool
    {
        return $this->redis->lSet($key, $index, $value);
    }

    /**
     * 修剪列表，仅保留指定区间的元素
     * @param string $key 列表键名
     * @param int $start 保留的起始索引
     * @param int $stop 保留的结束索引
     * @return bool 操作是否成功
     */
    public function lTrim(string $key, int $start, int $stop): bool
    {
        return $this->redis->lTrim($key, $start, $stop);
    }

    /**
     * 通过索引获取列表元素的值
     * @param string $key 列表键名
     * @param int $index 元素索引（负索引表示从尾部开始）
     * @return string|null 元素值（索引超出范围返回null）
     */
    public function lIndex(string $key, int $index): ?string
    {
        return $this->redis->lIndex($key, $index);
    }

    /**
     * 在列表指定元素前/后插入新元素
     * @param string $key 列表键名
     * @param string $position 插入位置（'BEFORE'或'AFTER'）
     * @param mixed $pivot 参考元素值
     * @param mixed $value 要插入的新值
     * @return int 插入后的列表长度（参考元素不存在返回-1）
     */
    public function lInsert(string $key, string $position, $pivot, $value): int
    {
        return $this->redis->lInsert($key, $position, $pivot, $value);
    }

    /**
     * 弹出列表的最后一个元素并插入到另一个列表头部
     * @param string $srcKey 源列表键名
     * @param string $dstKey 目标列表键名
     * @return string|null 弹出的元素值（源列表为空返回null）
     */
    public function rpoplpush(string $srcKey, string $dstKey): ?string
    {
        return $this->redis->rpoplpush($srcKey, $dstKey);
    }

    /**
     * 阻塞式弹出列表最后一个元素并插入到另一个列表头部
     * @param string $srcKey 源列表键名
     * @param string $dstKey 目标列表键名
     * @param int $timeout 超时时间（秒，0表示永久阻塞）
     * @return string|null 弹出的元素值（超时或源列表为空返回null）
     */
    public function brpoplpush(string $srcKey, string $dstKey, int $timeout): ?string
    {
        return $this->redis->brpoplpush($srcKey, $dstKey, $timeout);
    }

    // ======================== 集合操作 ======================== //

    /**
     * 向无序集合添加一个或多个成员（已存在的成员会被忽略）
     * @param string $key 无序集合键名
     * @param mixed ...$members 要添加的成员值（支持多个参数）
     * @return int 实际成功添加的新成员数量
     */
    public function sAdd(string $key, ...$members): int
    {
        return $this->redis->sAdd($key, ...$members);
    }

    /**
     * 移除无序集合中的一个或多个成员（不存在的成员会被忽略）
     * @param string $key 无序集合键名
     * @param mixed ...$members 要移除的成员值（支持多个参数）
     * @return int 实际成功移除的成员数量
     */
    public function sRem(string $key, ...$members): int
    {
        return $this->redis->sRem($key, ...$members);
    }

    /**
     * 获取无序集合中的所有成员
     * @param string $key 无序集合键名
     * @return array 成员值数组（集合不存在时返回空数组）
     */
    public function sMembers(string $key): array
    {
        return $this->redis->sMembers($key);
    }

    /**
     * 判断指定成员是否存在于无序集合中
     * @param string $key 无序集合键名
     * @param mixed $value 要检查的成员值
     * @return bool 存在返回true，不存在返回false
     */
    public function sIsMember(string $key, $value): bool
    {
        return $this->redis->sIsMember($key, $value);
    }

    /**
     * 获取无序集合的元素数量
     * @param string $key 无序集合键名
     * @return int 元素数量
     */
    public function sCard(string $key): int
    {
        return $this->redis->sCard($key);
    }

    /**
     * 随机移除并返回集合中的一个元素
     * @param string $key 集合键名
     * @return string|null 被移除的元素值（集合为空返回null）
     */
    public function sPop(string $key): ?string
    {
        return $this->redis->sPop($key);
    }

    /**
     * 随机返回集合中的一个或多个元素（不移除）
     * @param string $key 集合键名
     * @param int $count 返回元素数量
     * @return string|array 元素值或元素数组
     */
    public function sRandMember(string $key, int $count = 1)
    {
        return $this->redis->sRandMember($key, $count);
    }

    /**
     * 返回多个集合的交集
     * @param string ...$keys 集合键名（至少两个）
     * @return array 交集成员数组
     */
    public function sInter(...$keys): array
    {
        return $this->redis->sInter(...$keys);
    }

    /**
     * 返回多个集合的并集
     * @param string ...$keys 集合键名（至少两个）
     * @return array 并集成员数组
     */
    public function sUnion(...$keys): array
    {
        return $this->redis->sUnion(...$keys);
    }

    /**
     * 返回多个集合的差集（第一个集合与其他集合的差）
     * @param string ...$keys 集合键名（至少两个）
     * @return array 差集成员数组
     */
    public function sDiff(...$keys): array
    {
        return $this->redis->sDiff(...$keys);
    }

    // ======================== 有序集合操作 ======================== //

    /**
     * 向有序集合添加/更新成员
     * @param string $key 有序集合键名
     * @param array $members 成员数组（格式：[score => value] 或 [value => score]）
     * @return int 实际新添加的成员数量
     */
    public function zAdd(string $key, array $members): int
    {
        $params = [];
        foreach ($members as $score => $value) {
            $params[] = $score;
            $params[] = $value;
        }
        return $this->redis->zAdd($key, ...$params);
    }

    /**
     * 移除有序集合中的一个或多个成员（不存在的成员会被忽略）
     * @param string $key 有序集合键名
     * @param mixed ...$members 要移除的成员值（支持多个参数）
     * @return int 实际成功移除的成员数量
     */
    public function zRem(string $key, ...$members): int
    {
        return $this->redis->zRem($key, ...$members);
    }

    /**
     * 通过索引区间获取有序集合的成员（按分数从小到大排序）
     * @param string $key 有序集合键名
     * @param int $start 起始索引（0表示第一个元素）
     * @param int $end 结束索引（-1表示最后一个元素）
     * @param bool $withScores 是否同时返回分数
     * @return array 成员值数组（或包含分数的关联数组）
     */
    public function zRange(string $key, int $start, int $end, bool $withScores = false): array
    {
        return $this->redis->zRange($key, $start, $end, $withScores);
    }

    /**
     * 通过索引区间获取有序集合的成员（按分数从大到小排序）
     * @param string $key 有序集合键名
     * @param int $start 起始索引（0表示第一个元素）
     * @param int $end 结束索引（-1表示最后一个元素）
     * @param bool $withScores 是否同时返回分数
     * @return array 成员值数组（或包含分数的关联数组）
     */
    public function zRevRange(string $key, int $start, int $end, bool $withScores = false): array
    {
        return $this->redis->zRevRange($key, $start, $end, $withScores);
    }

    /**
     * 通过分数区间获取有序集合的成员（按分数从小到大排序）
     * @param string $key 有序集合键名
     * @param float $min 最小分数
     * @param float $max 最大分数
     * @param array $options 选项（如['withscores' => true, 'limit' => [offset, count]]）
     * @return array 成员值数组（或包含分数的关联数组）
     */
    public function zRangeByScore(string $key, float $min, float $max, array $options = []): array
    {
        return $this->redis->zRangeByScore($key, $min, $max, $options);
    }

    /**
     * 获取有序集合的成员数量
     * @param string $key 有序集合键名
     * @return int 成员数量（键不存在时返回0）
     */
    public function zCard(string $key): int
    {
        return $this->redis->zCard($key);
    }

    /**
     * 获取有序集合中指定成员的排名（按分数从小到大排序）
     * @param string $key 有序集合键名
     * @param mixed $member 要查询的成员值
     * @return int|null 排名（从0开始计数，成员不存在返回null）
     */
    public function zRank(string $key, $member): ?int
    {
        return $this->redis->zRank($key, $member);
    }

    /**
     * 获取有序集合中指定成员的排名（按分数从大到小排序）
     * @param string $key 有序集合键名
     * @param mixed $member 要查询的成员值
     * @return int|null 排名（从0开始计数，成员不存在返回null）
     */
    public function zRevRank(string $key, $member): ?int
    {
        return $this->redis->zRevRank($key, $member);
    }

    /**
     * 统计有序集合中指定分数区间的成员数量
     * @param string $key 有序集合键名
     * @param float $min 最小分数
     * @param float $max 最大分数
     * @return int 符合条件的成员数量
     */
    public function zCount(string $key, float $min, float $max): int
    {
        return $this->redis->zCount($key, $min, $max);
    }

    /**
     * 获取有序集合中指定成员的分数
     * @param string $key 有序集合键名
     * @param mixed $member 成员值
     * @return float|null 分数值（成员不存在返回null）
     */
    public function zScore(string $key, $member): ?float
    {
        return $this->redis->zScore($key, $member);
    }

    // ======================== 分布式锁 ======================== //

    /**
     * Redis分布式锁（原子操作实现）
     * @param string $key 锁的键名
     * @param int $expire 锁的自动过期时间（秒，防止死锁）
     * @param string $value 锁的值（建议使用唯一标识）
     * @return bool 加锁成功返回true，失败返回false
     */
    public function lock(string $key, int $expire = 10, string $value = '1'): bool
    {
        return $this->redis->set($key, $value, ['NX', 'EX' => $expire]);
    }

    /**
     * 释放分布式锁
     * @param string $key 锁的键名
     * @param string $value 锁的值（需与加锁时一致）
     * @return bool 释放成功返回true，失败返回false
     */
    public function unlock(string $key, string $value): bool
    {
        $lua = <<<LUA
        if redis.call("get", KEYS[1]) == ARGV[1] then
            return redis.call("del", KEYS[1])
        else
            return 0
        end
        LUA;

        return (bool)$this->redis->eval($lua, 1, $key, $value);
    }

    // ======================== 其他功能 ======================== //

    /**
     * 重命名键
     * @param string $oldKey 原键名
     * @param string $newKey 新键名
     * @return bool 操作是否成功
     */
    public function rename(string $oldKey, string $newKey): bool
    {
        return $this->redis->rename($oldKey, $newKey);
    }

    /**
     * 获取键的数据类型
     * @param string $key 键名
     * @return string 数据类型（none, string, list, set, zset, hash）
     */
    public function type(string $key): string
    {
        return $this->redis->type($key);
    }
}