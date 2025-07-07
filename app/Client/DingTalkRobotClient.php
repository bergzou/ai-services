<?php

namespace App\Client;

class DingTalkRobotClient
{
    //消息类型和数据格式参考资料：https://open.dingtalk.com/document/robots/message-types-and-data-format

    const URL = 'https://oapi.dingtalk.com/';

    /**
     * 发送文字消息
     * @param $msg //消息
     * @param $who //机器人
     * @param $atMobiles
     * @param $atUserIds
     * @param $isAtAll
     * @return bool|string
     */
    public static function sendMsg($msg, $who = 'default',$atMobiles = [],$atUserIds = [],$isAtAll = true)
    {
        $robot = self::getRobot($who);
        if(empty($robot)){
            return ['errcode'=>310000,'Robot does not exist'];
        }

        list($who,$msg) = self::getEvnParams($who,$msg);
        $data = [
            'at'      => [
                'atMobiles' => $atMobiles,
                'atUserIds' => $atUserIds,
                'isAtAll'   => $isAtAll
            ],
            'text'    => ['content' => '【'.$robot['keyword'].'】' . $msg],
            'msgtype' => 'text'
        ];

        $sendResult = self::sendData(self::URL . 'robot/send?access_token=' . $robot['token'], json_encode($data));
        return self::parseResult($sendResult);
    }

    /**
     * 发送link类型
     */
    public static function sendMsgLink($linkObj, $who = 'default')
    {
        $robot = self::getRobot($who);
        if (empty($robot)) {
            return ['errcode' => 310000, 'Robot does not exist'];
        }

        $msg = '';
        list($who, $msg) = self::getEvnParams($who, $msg);
        $linkObj['title'] = '【'.$robot['keyword'].'】' . $linkObj['title'];
        $data['msgtype']  = 'link';
        $data['link']     = $linkObj;
        //$data['at']['isAtAll'] = true;   #无效果
        $data       = json_encode($data);
        $sendResult = self::sendData(self::URL . 'robot/send?access_token=' . $robot['token'], $data);
        return self::parseResult($sendResult);
    }

    public static function sendMsgMarkdown($msg, $title = '', $who = 'default')
    {
        $robot = self::getRobot($who);
        if (empty($robot)) {
            return ['errcode' => 310000, 'Robot does not exist'];
        }

        list($who, $msg) = self::getEvnParams($who, $msg);
        $data       = sprintf('{"msgtype": "markdown", "markdown": {"title": "%s","text": "%s"}}', $title, $robot['token'] . $msg);
        $sendResult = self::sendData(self::URL . 'robot/send?access_token=' . $robot['token'], $data);
        return self::parseResult($sendResult);
    }

    protected static function getEvnParams($who, $msg): array
    {
        if (getenv('APP_ENV') !== 'production') {
            $who = 'default';
            $msg = "功能测试，无需理会。\n" . $msg;
        }
        return [$who, $msg];
    }

    /**
     * @param $url
     * @param $string
     * @return bool|string
     * 发送请求
     */
    protected static function sendData($url, $string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    //从配置文件中获取所有机器人,注：钉钉限制每个机器人每分钟最多发送20条信息
    protected static function getRobots()
    {
        // 修改配置读取路径为 services.ding_talk_robot
        $robots = config('services.ding_talk_robot');
        
        if (empty($robots)) {
            return [];
        }
        return $robots;
    }

    protected static function getRobot($who)
    {
        $robots = self::getRobots();
        $robot  = $robots[$who] ?? [];
        if (empty($robot)) {
            $robot = $robots['default'] ?? [];
        }
        return $robot;
    }

    protected static function parseResult($result)
    {
        $result = json_decode($result, true);
        if (is_null($result)) {
            return ['errcode' => 310000, 'Network failure or 404'];
        }
        return $result;//成功：['errcode'=>0,'errmsg'=>'ok']
    }

}
