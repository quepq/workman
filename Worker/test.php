<?php


use IMooc\Config;
use Workerman\Lib\Timer;
use Workerman\PHPMailer\PHPMailer;
use Workerman\Server\CrawlService\CrawlBase;
use Workerman\Server\CrawlService\WatchLotteryIssue;
use \Workerman\Worker;
require_once __DIR__ . '/Workerman/Autoloader.php';
$assistLotteryWorker  = new Worker("http://0.0.0.0:"."8881");
$assistLotteryWorker->name = "assistLotteryWorker";
$assistLotteryWorker->onWorkerStart = function ($task){
//\Workerman\Lib\Timer::add(5,function(){
//    var_dump("定时开始");
//    (new CrawlBase('xlr'))->notifyNoLotteryData('1','2018-07-05 21:10:00');
//});
};
$assistLotteryWorker->onConnect = function($connection)
{
    $connection->onMessage = function($connection, $data)
    {
        var_dump('收到重新开奖通知');
        $type = empty($data['get']['type'])?"":$data['get']['type'];
        $qh = empty($data['get']['qh'])?"":$data['get']['qh'];
        $time_plan = empty($data['get']['time_plan'])?"":$data['get']['time_plan'];
        $type = base64_decode($type);
        $qh = base64_decode($qh);
        $time_plan = base64_decode($time_plan);
        $count= 360;
        $timeid = Timer::add(5,function ()use(&$timeid,&$count,$type,$qh,$time_plan){
            var_dump('type='.$type);
            $flag =  (new WatchLotteryIssue())->getCPData($type,$qh,$time_plan);
            if(($count--)<=0|| $flag){
                Timer::del($timeid);
            }
        });
    };
};

Worker::runAll();