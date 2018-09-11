<?php
//本机IP是10.211.55.13
//需要监听的端口是 9090


use Workerman\Connection\AsyncTcpConnection;
use Workerman\Worker;


require_once __DIR__ . '/Worker/Workerman/Autoloader.php';

$clients = []; //保存客户端信息


// 创建一个Worker监听9090端口，使用websocket协议通讯
$ws_worker = new Worker("websocket://192.168.0.218:8001");

// 启动4个进程对外提供服务
$ws_worker->count = 4;

/**
 * 同步登录用户列表
 */
function syncUsers()
{
    global $clients;

    $users = 'users:'.json_encode(array_column($clients,'name','ipp')); //准备要广播的数据
    foreach($clients as $ip=>$client){
        $client['conn']->send($users);
    }
}

// 当收到客户端发来的数据后
$ws_worker->onMessage = function($connection, $data)
{
    //这里用global的原因是:php是有作用域的,我们是在onMessage这个回调还是里操作外面的数组
    //想要改变作用域外面的数组,就global一下
    global $clients;

    //验证客户端用户名在3-20个字符
    if(preg_match('/^login:(\w{3,20})/i',$data,$result)){ //代表是客户端认证

        $ip = $connection->getRemoteIp();
        $port = $connection->getRemotePort();

        if(!array_key_exists($ip.':'.$port, $clients)){ //必须是之前没有注册过

            //存储新登录用户的数据
            $clients[$ip.':'.$port] = ['ipp'=>$ip.':'.$port,'name'=>$result[1],'conn'=>$connection];

            // 向客户端发送数据
            $connection->send('notice:success'); //验证成功消息
            $connection->send('msg:welcome '.$result[1]); //普通消息
            echo $ip .':'.$port.'==>'.$result[1] .'==>login' . PHP_EOL; //这是为了演示,控制台打印信息

            //有新用户登录
            //需要同步登录用户数据
            syncUsers();
        }

    }elseif(preg_match('/^msg:(.*?)/isU',$data,$msgset)){ //代表是客户端发送的普通消息

        if(array_key_exists($connection->getRemoteIp(),$clients)){ //必须是之前验证通过的客户端
            echo 'get msg:' . $msgset[1] .PHP_EOL; //这是为了演示,控制台打印信息
            if($msgset[1] == 'nihao'){
                //如果收到'nihao',就给客户端发送'nihao 用户名'
                //给客户端发送普通消息
                $connection->send('msg:nihao '.$clients[$connection->getRemoteIp()]);
            }
        }
    }elseif (preg_match('/^chat:\<(.*?)\>:(.*?)/isU',$data,$msgset)){
        $ipp = $msgset[1];
        $msg = $msgset[2];

        if (array_key_exists($ipp,$clients)){ //如果有这个用户
            //就发送普通消息
            $clients[$ipp]['conn']->send('msg:'.$msg);

            echo $ipp.'==>'.$msg.PHP_EOL;
        }
    }

    // 设置连接的onClose回调
    $connection->onClose = function($connection) //客户端主动关闭
    {
        global $clients;
        unset($clients[$connection->getRemoteIp().':'.$connection->getRemotePort()]);

        //客户端关闭
        //即退出登录，也需要更新用户列表数据
        syncUsers();

        echo "connection closed\n";
    };
};

// 运行worker
Worker::runAll();