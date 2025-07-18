<?php
/**
 * RabbitMQ 高级工具类
 * 支持所有工作模式：简单队列、工作队列、发布订阅、路由、主题、RPC、死信队列
 * 封装PhpAmqpLib库，提供单例连接管理和常用操作的简化接口
 */
namespace App\Libraries;

use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;
use PhpAmqpLib\Exception\AMQPTimeoutException;

class RabbitMQ
{
    // RabbitMQ连接实例（PhpAmqpLib连接对象）
    private $connection;

    // 当前使用的AMQP通道（用于执行具体操作）
    private $channel;

    // 连接配置参数（从环境变量读取）
    private array $config;

    // 连接状态标识（避免重复连接）
    private bool $isConnected = false;

    // 单例模式实例（确保全局唯一连接）
    private static RabbitMQ $_instance;

    /**
     * 获取单例实例（全局唯一）
     * @return RabbitMQ 工具类实例
     */
    public static function getInstance(): RabbitMQ
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 私有构造函数（防止外部实例化）
     * 初始化RabbitMQ连接配置（从.env读取或使用默认值）
     */
    private function __construct()
    {
        $this->config = [
            'host' => env('RABBITMQ_HOST', 'localhost'),          // 服务地址
            'port' => env('RABBITMQ_PORT', 5672),                  // 端口
            'user' => env('RABBITMQ_USER', 'guest'),                // 用户名
            'password' => env('RABBITMQ_PASSWORD', 'guest'),        // 密码
            'vhost' => env('RABBITMQ_VHOST', '/'),                  // 虚拟主机
            'heartbeat' => env('RABBITMQ_HEARTBEAT', 60),           // 心跳检测间隔（秒）
            'connection_timeout' => env('RABBITMQ_CONNECTION_TIMEOUT', 3), // 连接超时（秒）
            'read_write_timeout' => env('RABBITMQ_READ_WRITE_TIMEOUT', 3), // 读写超时（秒）
        ];
    }

    /**
     * 建立RabbitMQ连接（私有方法，内部调用）
     * @throws Exception 连接失败时抛出异常
     */
    private function connect()
    {
        if (!$this->isConnected) {
            try {
                // 创建AMQP流连接（支持长连接和心跳检测）
                $this->connection = new AMQPStreamConnection(
                    $this->config['host'], $this->config['port'], $this->config['user'], $this->config['password'], $this->config['vhost'],
                    false, 'AMQPLAIN', null, 'en_US', $this->config['connection_timeout'],
                    $this->config['read_write_timeout'], null, false, $this->config['heartbeat']
                );
                $this->isConnected = true; // 标记连接成功
            } catch ( Exception $e) {
                throw new Exception('RabbitMQ连接失败: ' . $e->getMessage());
            }
        }
    }

    /**
     * 获取AMQP通道（私有方法，内部调用）
     * @param bool $newChannel 是否强制创建新通道（默认复用现有通道）
     * @return AMQPChannel 通道实例
     * @throws Exception
     */
    private function getChannel(bool $newChannel = false)
    {
        $this->connect(); // 确保连接已建立

        // 通道不存在/需要新通道/通道已关闭时创建新通道
        if (!$this->channel || $newChannel || !$this->channel->is_open()) {
            $this->channel = $this->connection->channel();
        }

        return $this->channel;
    }

    /**
     * 声明交换机（支持持久化和自动删除）
     * @param string $exchange 交换机名称（空字符串表示默认交换机）
     * @param string $type 交换机类型（direct:直连, topic:主题, fanout:广播, headers:头匹配）
     * @param bool $durable 是否持久化（重启后保留配置）
     * @param bool $autoDelete 无队列绑定时是否自动删除
     * @throws Exception
     */
    public function declareExchange(string $exchange, string $type = 'direct', bool $durable = true, bool $autoDelete = false) {
        $channel = $this->getChannel();
        // 执行交换机声明（passive:不检查是否存在, internal:内部交换机）
        $channel->exchange_declare($exchange, $type, false, $durable, $autoDelete);
    }

    /**
     * 声明队列（支持死信队列配置）
     * @param string $queue 队列名称（空字符串自动生成随机名称）
     * @param bool $durable 是否持久化（消息持久化需配合消息delivery_mode=2）
     * @param bool $exclusive 是否排他（仅当前连接可用，断开后自动删除）
     * @param bool $autoDelete 无消费者时是否自动删除
     * @param array $arguments 扩展参数（死信队列、消息TTL等）
     * @return array 队列声明结果（[队列名, 消息数, 消费者数]）
     * @throws Exception
     */
    public function declareQueue(string $queue = '', bool $durable = true, bool $exclusive = false, bool $autoDelete = false, array $arguments = []): array
    {
        $channel = $this->getChannel();
        // 将参数数组转换为AMQPTable对象（支持死信队列等高级配置）
        return $channel->queue_declare($queue, false, $durable, $exclusive, $autoDelete, false, new AMQPTable($arguments));
    }

    /**
     * 绑定队列到交换机（支持头匹配绑定）
     * @param string $queue 目标队列名称
     * @param string $exchange 源交换机名称
     * @param string $routingKey 路由键（direct/topic类型使用）
     * @param array $headers 头信息（headers类型交换机使用，键值对数组）
     * @throws Exception
     */
    public function bindQueue(string $queue, string $exchange, string $routingKey = '', array $headers = []) {
        $channel = $this->getChannel();

        if (!empty($headers)) {
            // 头匹配绑定（需将headers转换为AMQPTable）
            $channel->queue_bind($queue, $exchange, $routingKey, false, new AMQPTable($headers));
        } else {
            // 普通路由键绑定
            $channel->queue_bind($queue, $exchange, $routingKey);
        }
    }

    /**
     * 发送消息（支持所有消息模式）
     * @param mixed $body 消息内容（非字符串自动JSON序列化）
     * @param string $exchange 目标交换机（空字符串使用默认交换机）
     * @param string $routingKey 路由键（默认交换机使用队列名）
     * @param array $options 消息属性（delivery_mode:持久化模式, expiration:过期时间等）
     * @return bool 发送成功返回true
     * @throws Exception 发送失败时抛出异常
     */
    public function publish($body, string $exchange = '', string $routingKey = '', array $options = []) {
        try {
            $channel = $this->getChannel();

            // 非字符串消息自动序列化（方便存储复杂数据）
            if (!is_string($body)) {
                $body = json_encode($body);
            }

            // 合并默认属性（持久化模式为默认）
            $properties = array_merge([
                'content_type' => 'text/plain',          // 内容类型（默认文本）
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, // 持久化模式（2表示持久化）
            ], $options);

            $message = new AMQPMessage($body, $properties);
            $channel->basic_publish($message, $exchange, $routingKey);
            return true;
        } catch (Exception $e) {
            throw new Exception('消息发送失败: ' . $e->getMessage());
        }
    }

    /**
     * 基础消费者（短连接，单次获取消息）
     * @param string $queue 目标队列名称
     * @param callable $callback 消息处理回调（参数：消息体, 消息对象）
     * @param bool $autoAck 是否自动确认（自动确认可能导致消息丢失）
     * @return bool 成功获取消息返回true
     * @throws Exception 消费失败时抛出异常
     */
    public function get(string $queue, callable $callback, bool $autoAck = true)
    {
        try {
            $channel = $this->getChannel();
            // 从队列中获取一条消息（non_blocking:非阻塞）
            $message = $channel->basic_get($queue, $autoAck);

            if ($message) {
                $callback($message->body, $message); // 执行用户回调
                return true;
            }
            return false; // 无消息返回false
        } catch (Exception $e) {
            throw new Exception('消费消息失败: ' . $e->getMessage());
        }
    }

    /**
     * 长连接消费者（持续监听队列）
     * @param string $queue 目标队列名称
     * @param callable $callback 消息处理回调（返回true表示确认，其他表示拒绝重入队）
     * @param array $options 消费选项（no_ack:自动确认, prefetch_count:预取数量等）
     * @throws Exception 消费者异常时抛出异常
     */
    public function consume(string $queue, callable $callback, array $options = []) {
        $channel = $this->getChannel();

        // 设置QoS（预取数量控制消费者负载）
        $prefetchCount = $options['prefetch_count'] ?? 1;
        $prefetchSize = $options['prefetch_size'] ?? null;
        if ($prefetchCount > 0 || $prefetchSize > 0) {
            $channel->basic_qos($prefetchSize, $prefetchCount, false); // 全局QoS设置
        }

        // 包装回调函数（处理消息确认逻辑）
        $wrapperCallback = function ($msg) use ($callback) {
            try {
                $result = $callback($msg->body, $msg); // 执行用户业务逻辑

                // 非自动确认模式下处理确认/拒绝
                if (!$msg->has('no_ack') || !$msg->get('no_ack')) {
                    if ($result === true) {
                        $msg->ack(); // 确认消息（从队列删除）
                    } else {
                        $msg->nack(true); // 拒绝消息（重新入队）
                    }
                }
            } catch (Exception $e) {
                $msg->nack(true); // 异常时消息重新入队
                throw $e; // 向上抛出异常
            }
        };

        // 启动消费者（no_ack:自动确认, exclusive:排他消费者）
        $channel->basic_consume($queue, $options['consumer_tag'] ?? '', $options['no_ack'] ?? false,
            $options['exclusive'] ?? false, false, false, $wrapperCallback);

        // 持续监听消息（阻塞模式）
        try {
            while (count($channel->callbacks)) { // 存在未处理的回调时循环
                $channel->wait(); // 等待消息到达
            }
        } catch (AMQPTimeoutException $e) {
            // 超时后关闭连接（避免资源泄漏）
            $channel->close();
            $this->connection->close();
            $this->isConnected = false;
        } catch (Exception $e) {
            throw new Exception('消费者异常: ' . $e->getMessage());
        }
    }

    /**
     * 发送RPC请求（同步等待响应）
     * @param mixed $body 请求内容（自动序列化）
     * @param string $queue RPC服务队列名称
     * @param int $timeout 超时时间（秒）
     * @return mixed 服务端响应数据（自动反序列化）
     * @throws Exception 请求超时或异常时抛出
     */
    public function rpcRequest($body, string $queue, int $timeout = 10)
    {
        $channel = $this->getChannel();

        // 声明临时回调队列（自动删除、排他）
        list($callbackQueue) = $channel->queue_declare("", false, false, true, true);

        // 生成唯一关联ID（用于匹配请求和响应）
        $corrId = uniqid();

        // 创建请求消息（包含关联ID和回调队列）
        $msg = new AMQPMessage(
            is_string($body) ? $body : json_encode($body),
            [
                'correlation_id' => $corrId,          // 关联ID
                'reply_to' => $callbackQueue,          // 回调队列
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // 消息持久化
            ]
        );

        // 发送请求到RPC服务队列
        $channel->basic_publish($msg, '', $queue);

        // 准备接收响应（监听回调队列）
        $response = null;
        $channel->basic_consume($callbackQueue, '', false, true, false, false,
            function ($rep) use ($corrId, &$response) {
                if ($rep->get('correlation_id') == $corrId) {
                    $response = $rep->body; // 匹配关联ID后记录响应
                }
            }
        );

        // 等待响应（超时控制）
        $startTime = time();
        while ($response === null) {
            if (time() - $startTime > $timeout) {
                throw new Exception('RPC请求超时');
            }
            $channel->wait(null, false, $timeout); // 阻塞等待消息
        }

        // 关闭通道并返回响应（自动反序列化JSON）
        $channel->close();
        return json_decode($response, true) ?? $response;
    }

    /**
     * 启动RPC服务端（持续监听请求）
     * @param string $queue RPC服务队列名称
     * @param callable $callback 请求处理回调（参数：请求体，返回响应体）
     * @throws Exception
     */
    public function rpcServer(string $queue, callable $callback)
    {
        $channel = $this->getChannel();
        // 声明持久化队列（确保服务重启后消息保留）
        $channel->queue_declare($queue, false, true, false, false);
        // 设置QoS（每次只处理1条消息，避免过载）
        $channel->basic_qos(null, 1, null);

        // 定义请求处理回调
        $channel->basic_consume($queue, '', false, false, false, false,
            function ($req) use ($callback) {
                try {
                    $response = $callback($req->body); // 执行用户业务逻辑

                    // 创建响应消息（携带关联ID）
                    $msg = new AMQPMessage(
                        is_string($response) ? $response : json_encode($response),
                        [
                            'correlation_id' => $req->get('correlation_id'), // 关联ID
                            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT // 持久化
                        ]
                    );

                    // 发送响应到客户端回调队列
                    $req->delivery_info['channel']->basic_publish($msg, '', $req->get('reply_to'));
                    $req->ack(); // 确认请求消息（从队列删除）
                } catch (Exception $e) {
                    $req->nack(); // 异常时拒绝消息（重新入队）
                }
            }
        );

        // 持续监听请求（阻塞模式）
        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }

    /**
     * 配置死信队列（处理过期/被拒绝的消息）
     *
     * 死信队列用于存储：
     * - 消息过期（x-message-ttl）
     * - 队列达到最大长度（x-max-length）
     * - 消息被消费者拒绝且未重新入队（basic.nack/no-ack）
     *
     * @param string $queue 原始业务队列名称（如"order_queue"）
     * @param string $dlxExchange 死信交换机名称（如"order_dlx_exchange"）
     * @param string $dlxRoutingKey 死信路由键（默认使用原始消息的路由键）
     * @param int $ttl 消息存活时间（毫秒，默认60秒）
     * @param int $maxLength 队列最大消息数（超过时旧消息进入死信队列，默认10000条）
     * @throws Exception
     */
    public function setupDeadLetterQueue(string $queue, string $dlxExchange, string $dlxRoutingKey = '', int $ttl = 60000, int $maxLength = 10000) {
        $channel = $this->getChannel();

        // 1. 声明死信交换机（持久化，确保RabbitMQ重启后配置保留）
        $channel->exchange_declare($dlxExchange, 'direct', false, true, false);

        // 2. 声明死信队列（持久化，存储死信消息）
        $channel->queue_declare($queue . '_dead_letter', false, true, false, false, false);

        // 3. 绑定死信队列到死信交换机（通过路由键关联）
        $channel->queue_bind($queue . '_dead_letter', $dlxExchange, $dlxRoutingKey);

        // 4. 配置原始队列的死信参数（通过AMQPTable传递扩展属性）
        $args = new AMQPTable([
            'x-dead-letter-exchange' => $dlxExchange,       // 指定死信交换机
            'x-dead-letter-routing-key' => $dlxRoutingKey,  // 死信消息的路由键
            'x-message-ttl' => $ttl,                        // 消息存活时间（毫秒）
            'x-max-length' => $maxLength                     // 队列最大消息数
        ]);

        // 5. 声明原始业务队列（应用死信配置）
        $channel->queue_declare($queue, false, true, false, false, false, $args);
    }

    /**
     * 手动关闭RabbitMQ连接和通道
     *
     * 注意：析构函数会自动调用此方法，但建议在业务逻辑结束时显式调用以释放资源
     */
    public function close()
    {
        try {
            // 关闭已打开的通道（避免通道未关闭导致的资源泄漏）
            if ($this->channel && $this->channel->is_open()) {
                $this->channel->close();
            }
            // 关闭底层TCP连接（释放网络资源）
            if ($this->isConnected) {
                $this->connection->close();
            }
            // 更新连接状态为断开
            $this->isConnected = false;
        } catch (Exception $e) {
            // 静默处理关闭异常（避免因关闭失败导致主业务中断）
        }
    }

    /**
     * 获取指定队列的当前消息数量
     *
     * @param string $queue 目标队列名称（如"user_register_queue"）
     * @return int 队列中的消息数量（0表示无消息或队列不存在）
     * @throws Exception 队列声明失败时抛出异常
     */
    public function getMessageCount(string $queue): int
    {
        try {
            $channel = $this->getChannel();
            // 声明队列时设置passive=true（仅检查队列是否存在，不创建新队列）
            // 返回值格式：[队列名, 消息数, 消费者数]
            $result = $channel->queue_declare($queue, true);
            return $result[1] ?? 0; // 取第二个元素（消息数量）
        } catch (Exception $e) {
            throw new Exception('获取队列消息数失败: ' . $e->getMessage());
        }
    }

    /**
     * 析构函数（自动关闭连接）
     *
     * 当对象被销毁时（如脚本结束），自动调用close方法释放资源
     * 防止因忘记手动关闭导致的连接泄漏
     */
    public function __destruct()
    {
        $this->close();
    }
}