<?php
//创建websocket服务器对象，监听0.0.0.0:9502端口
$ws = new swoole_websocket_server("0.0.0.0", 9502);
$ws->set(array(
    'worker_num' => 4,
    'dispatch_mode' => 2,
    'daemonize' => 0,
    'log_file' => '/data/logs/xmw/logs/swoole.log'
));

//监听WebSocket连接打开事件
$ws->on('open', function ($ws, $request) {
    //var_dump($request);
    set_time_limit(0);
    $msg['user'] ='系统消息：';
    $msg['content'] = '欢迎用户 '.$request->fd." 来到C++课堂\n";
    //$redis->set("user_" . $user_id, $request->fd);
    //$redis->expire("user_" . $user_id, 86400);
    sendAllClient($ws, $msg);
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
        $redis = new Redis();
        $redis->connect("127.0.0.1", "6379");
        $redis->select(0);
        $datas = json_decode($frame->data, true);
        foreach ($datas as $k => $v) {
            $data[$v['key']] = $v['value'];
        }
        $redis->set("user_" . $data['user_id'], $frame->fd);
        $redis->expire("user_" . $data['user_id'], 86400);
        print_r($frame);
        if ($data['role'] == 1) {
            $redis->set("teacher_id", $data['user_id']);
            $redis->expire("teacher_id", 86400);
        }
        $msg['user'] = '学生:'.$data['username'];
        $msg['content'] = "{$data['message']}\n";
        if ($data['role'] == 1){
            sendAllClient($ws, $msg, $frame);
        } else {
            $teacher_id = $redis->get("teacher_id");
            $fd = $redis->get("user_" . $teacher_id);
            sendToTeacher($ws, $fd, $msg, $frame);
        }

});

//监听WebSocket连接关闭事件
$ws->on('close', function ($ws, $fd) {
    //删除已断开的客户端
    unset($ws->user_c[$fd-1]);
    $msg['user'] = '系统消息';
    $msg['content'] = "用户 {$fd} 退出了C++课堂\n";
    sendAllClient($ws, $msg);
});

//发送消息到所有在线的客户端
function sendAllClient($ws, $msg, $frame = null){
    //var_dump($ws->connections);
    $msg = implode(',', $msg);
    foreach($ws->connections as $fd){
        $ws->push($fd, $msg);
}
}

//推送消息给老师
function sendToTeacher($ws, $fd, $msg, $frame = null){
    //var_dump($ws->connections);
    $msg = implode(',', $msg);
    $ws->push($fd, $msg);
}
//测试112
$ws->start();

