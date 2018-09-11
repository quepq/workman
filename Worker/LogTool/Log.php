<?php
namespace LogTool;

class Log
{

    public static  function  writeLog($content)
    {
        $datetime = date('Y-m-d',time());
        $file_name = __DIR__.'/../Storage'.'/Log/Log_'.$datetime.'.txt';
        $tmCotent = date('Y-m-d H:i:s',time()).' :'.$content."\r\n";
        try{
            if(!($fp=fopen($file_name,'a'))) //a追加文件，w重写文本
            {
                echo('日志文件不能打开');
            }
            //2.其次确认文件没有锁定
            $bBlock = true;
            if(!flock($fp,LOCK_EX,$bBlock))
            {
                echo('日志文件已被锁定');
            }
            //3.然后开始写入文本
            if(!fwrite($fp,$tmCotent))
            {
                echo('日志文件写入失败');
            }
            //4.释放锁定
            flock($fp,LOCK_UN);

        }catch (Exception $ex)
        {
            echo '写入文件异常,异常原因:'.$ex;
        }
        finally
        {
            fclose($fp);
        }
    }
    public static  function  writeDetailLog($content,$arr)
    {
        $datetime = date('Y-m-d',time());
        $file_name = __DIR__.'/../Storage'.'/Log/Log_'.$datetime.'.txt';
        $keys = array_keys($arr);
        $str ="";
        if(count($keys))
        {
            $val = $arr[$keys[0]];
            if(is_object($val)||is_array($val))
            {
                $val = json_encode($val);
            }
            $key = $keys[0];
            $str = "[$key:$val]";
        }

        $tmCotent = date('Y-m-d H:i:s',time()).' :'.$content."  ".$str."\r\n";
        try{
            if(!($fp=fopen($file_name,'a'))) //a追加文件，w重写文本
            {
                echo('日志文件不能打开');
            }
            //2.其次确认文件没有锁定
            $bBlock = true;
            if(!flock($fp,LOCK_EX,$bBlock))
            {
                echo('日志文件已被锁定');
            }
            //3.然后开始写入文本
            if(!fwrite($fp,$tmCotent))
            {
                echo('日志文件写入失败');
            }
            //4.释放锁定
            flock($fp,LOCK_UN);

        }catch (Exception $ex)
        {
            echo '写入文件异常,异常原因:'.$ex;
        }
        finally
        {
            fclose($fp);
        }
    }

}