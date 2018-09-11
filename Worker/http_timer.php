<?php
use \Workerman\Worker;
use \FactoryGernerate\CrawFactory;
use Workerman\Lib\Timer;
use MemCache\RedisCache;
use LogTool\Log;

require_once __DIR__ . '/Workerman/Autoloader.php';
require_once __DIR__ . '/Workerman/AuthCode.php';




//对外提供接口服务
$redis = RedisCache::getInstanse()->getRedis();
$SysConfigs = json_decode($redis->get('_config_SysConfig'),true);//获取平台配置
$port = $SysConfigs['CPGETPORT'];
$port = empty($port)?"8881":$port;
$scadaport = $SysConfigs['SCADAWATCHPORT'];
$scadaport = empty($scadaport)?"8882":$scadaport;
$task_newsSend = new Worker("http://0.0.0.0:".$port);
$task_newsSend->name = "newsSend";
$task_newsSend->onConnect = function($connection) use ($redis)
{
    // 设置连接的onMessage回调
    $connection->onMessage = function($connection, $data) use ($redis)
    {
        $type = empty($data['get']['type'])?"":$data['get']['type'];
        $time_plan = empty($data['get']['time_plan'])?"":$data['get']['time_plan'];
        $type = base64_decode($type);
        $time_plan = base64_decode($time_plan);
        //先到redis里面查询数据
        $rt = [];
        if (!empty($redis->get('_issue_data'.$time_plan.$type))) {
        //if (false) {
            $kjData = json_decode($redis->get('_issue_data'.$time_plan.$type),true);
            $rt =[
                'type'=>$kjData['type'],
                'data'=>$kjData['data'],
                'qh'=>$kjData['qh'],
                'time_plan'=>$kjData['time_plan'],
            ];
        }else{
            $dbconfig =json_decode($redis->get('_config_DbConfig'),true);//获取数据库配置
            $db = new \Workerman\MySQL\Connection($dbconfig['mysql']['host'], 3306, $dbconfig['mysql']['user'], $dbconfig['mysql']['password'], $dbconfig['mysql']['dbname']);
            //取单行数据
            $kjData = $db->select('data,number,time_plan,type')->from('cp_issue')
                        ->where('time_plan=:time_plan')->bindValues(array('time_plan'=>$time_plan))->row();
            if(!empty($kjData['data'])){
                $rt =[
                    'type'=>$kjData['type'],
                    'data'=>$kjData['data'],
                    'qh'=>$kjData['number'],
                    'time_plan'=>$kjData['time_plan'],
                ];
            }
        }
        $send = json_encode($rt);
        $send = base64_encode($send);
        $connection->send($send);
    };
};

$newsConfig = json_decode($redis->get('_config_news_config'),true);//获取平台配置
//获取平台
$platformConfigArr = json_decode($redis->get('_config_platform_config'),true);//获取平台配置
//创建采集数据进程
foreach ($platformConfigArr as $platform){
    $platformnewss = $newsConfig[$platform];
    $task_crawl = new Worker();
    //有几个种类就创建几个进程
    $task_crawl->count = count($platformnewss);
    $task_crawl->name = $platform;
    $task_crawl->onWorkerStart = function( $task_crawl ) use ($platform,$platformnewss)
    {
        $index = 0;
        foreach($platformnewss as $key => $value ){
            if($index === $task_crawl->id ){

                //初始化抓取数据环境
                $crawService =  CrawFactory::createCraw($platform);
                $our_news_id = $key;
                $other_news_id = $value;
                $timer_id = Timer::add(1, array($crawService, 'run'), array( $our_news_id, $other_news_id ), true);
                $task_crawl->onClose = function( ) use( $timer_id )
                {
                    // 删除定时器
                    Timer::del($timer_id);
                };
            }
            $index ++;
        }
    };
}

Worker::runAll();