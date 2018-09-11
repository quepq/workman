<?php
use GatewayWorker\Lib\Gateway;
use Illuminate\Support\Facades\DB;
//use GatewayWorker\Lib\Server;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 定时器 定时发送消息
     * 进程启动时设置定时器 用于开奖业务
     *
     * @return string '{game_type:XXXX, ...}'
     */
    public static function onWorkerStart()
    {
            //多线程获取北京快乐8数据并播报开奖
            $lottery = new \App\Http\Timing\CrawlerDrawService();
            $lottery->crawler();
    }

   /**
    * 当客户端发来消息时触发
    * 客户端消息格式 $data = string '{"id":"用户唯一标识","name":"用户名","type":"类型","chat_room_key":"(聊天室key)u876d8f5da7c2e7d008e3b41824675044","content":"内容","status":"消息处理状态"}'
    * @param int $client_id 连接id
    * @param string $data 客户端消息内容
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $data) {
       $message = json_decode($data, true);
       switch($message['type']) {
           //初始化
           case 'init':
               // uid
               $uid = $message['id'];
               // 设置session
               $_SESSION = [
                   'name'   => $message['name'],
                   'id'     => $uid
               ];

               // 将当前链接与uid绑定
               Gateway::bindUid($client_id, $uid);
               // 通知当前客户端初始化
               $data = $init_message = array(
                   'user_id'       => $message['id'],
                   'nickname'      => $message['name'],
                   'type'          => $message['type'],
                   'chat_room_key' => $message['chat_room_key'],
                   'content'       => $message['content'],
                   'status'        => $message['status'],
                   'client_id'     => $client_id
               );

               DB::table('users_message_log')->insert($data);

               Gateway::sendToClient($client_id, json_encode($init_message));
               return;
               break;
           case 'log':
               $user = DB::table('users')->where('nickname', $message['name'])->first();
               if (empty($user)) return;

               $data = array(
                   'user_id'       => $user->user_id,
                   'nickname'      => $message['name'],
                   'type'          => $message['type'],
                   'chat_room_key' => $message['chat_room_key'],
                   'content'       => $message['content'],
                   'status'        => $message['status']
               );
               DB::table('users_message_log')->insert($data);
               return;
               break;
           //下注
           case 'bet':
               $init_message = array(
                   'user_id'       => 1,
                   'nickname'      => 2,
                   'type'          => 3,
                   'chat_room_key' => 4,
                   'content'       => 5,
                   'status'        => 'hehe',
                   'client_id'     => $client_id
               );

               Gateway::sendToClient($client_id, json_encode($init_message));
               return;
               break;
           //查询最新期开奖结果
           case 'newLottery':
               return;
               break;
           //查询上一期开奖结果
           case 'previousRes':
               $status_message = array(
                   'message_type' => $message['type'],
                   'id'           => $_SESSION['id'],
               );
               $_SESSION['online'] = $message['type'];
               Gateway::sendToAll(json_encode($status_message));
               return;
               break;
           //查询历史开奖信息
           case 'historyLottery':
               $status_message = array(
                   'message_type' => $message['type'],
                   'id'           => $_SESSION['id'],
               );
               $_SESSION['online'] = $message['type'];
               Gateway::sendToAll(json_encode($status_message));
           //查询余额
           case 'checkBalance':
               $status_message = array(
                   'message_type' => $message['type'],
                   'id'           => $_SESSION['id'],
               );
               $_SESSION['online'] = $message['type'];
               Gateway::sendToAll(json_encode($status_message));
               return;
               break;
           //提现
           case 'cashout':
               $status_message = array(
                   'message_type' => $message['type'],
                   'id'           => $_SESSION['id'],
               );
               $_SESSION['online'] = $message['type'];
               Gateway::sendToAll(json_encode($status_message));
               return;
               break;
           //充值
           case 'recharge':
               $send_message = array(
                   'msg'       => '充值成功',
                   'client_id' => $client_id,
                   'type'      => 'recharge'
               );
               Gateway::sendToAll(json_encode($send_message));
               return;
               break;
           //注册
           case 'register':
               $send_message = array(
                   'msg'       => '注册成功',
                   'client_id' => $client_id,
                   'type'      => 'recharge'
               );
               Gateway::sendToAll(json_encode($send_message));
               return;
               break;
           default:
               echo "unknown message $data" . PHP_EOL;
       }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id) {
       $logout_message = array(
           'message_type' => 'logout',
           'id'           => $_SESSION['id']
       );
       Gateway::sendToAll(json_encode($logout_message));
   }
}