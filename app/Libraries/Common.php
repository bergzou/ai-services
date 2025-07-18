<?php

namespace App\Libraries;

/**
 * 通用工具类
 */
class Common {

    /**
     * 生成雪花算法所需的工作节点ID（Worker ID）
     *
     * 用于分布式系统中标识不同工作节点，确保雪花ID的全局唯一性
     * 实现逻辑：
     * 1. 定义工作节点ID占用的二进制位数（10位，支持最大1024个节点）
     * 2. 计算最大合法工作节点ID值（通过位运算获取2^10 - 1 = 1023）
     * 3. 使用当前时间戳（微秒级）生成初始ID值
     * 4. 通过取模运算确保结果落在[0, maxWorkerId]合法范围内
     *
     * @return int 0-1023范围内的工作节点ID
     */
    public static function getWorkerId(): int
    {
        // 定义工作节点ID占用的二进制位数（10位可支持最多1024个不同节点）
        $numWorkerBits  = 10;
        // 计算最大合法工作节点ID值（通过位运算获取2^numWorkerBits - 1）
        $maxWorkerId = (-1 ^ (-1 << $numWorkerBits));
        // 使用当前时间戳（微秒级）生成初始ID值（转换为整数避免浮点精度问题）
        $workerId = intval(microtime(true) * 1000);
        // 限制在合法范围内（确保结果在0到maxWorkerId之间）
        return ($workerId % ($maxWorkerId + 1));
    }

}