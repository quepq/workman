<?php
namespace FactoryGernerate;

use Workerman\Server\CrawlService\CrawCBPService;
use Workerman\Server\CrawlService\Crawl4399Service;
use Workerman\Server\CrawlService\CrawlBGService;
use Workerman\Server\CrawlService\CrawlFenghuoService;
use Workerman\Server\CrawlService\CrawlJSGJService;
use Workerman\Server\CrawlService\CrawlLSCPService;
use Workerman\Server\CrawlService\CrawlSDService;
use Workerman\Server\CrawlService\CrawlXlrService;
use Workerman\Server\CrawlService\CrawlWangyiService;
use Workerman\Server\CrawlService\Craw3DService;
use Workerman\Server\CrawlService\CrawlXTUService;
use Workerman\Server\CrawlService\CrawlXYCPService;
use Workerman\Server\CrawlService\CrawlYCService;
use Workerman\Server\CrawlService\CrawlYDNIUService;
use Workerman\Server\CrawlService\CrawlYGService;
use Workerman\Server\CrawlService\CrawTanCService;
use Workerman\Server\CrawlService\CrawXGLHCService;
use Workerman\Server\CrawlService\CrawlBoniuService;
use Workerman\Server\CrawlService\CrawCPKService;
use Workerman\Server\CrawlService\CrawCP500Service;
class CrawFactory
{
    public static function createCraw($platform)
    {
        if($platform == "4399") {
            return new Crawl4399Service($platform);
        }
        else if($platform == "cpk"){
            return new CrawCPKService($platform);
        }
        else if($platform =="cp500")
        {
            return new CrawCP500Service($platform);
        }
        else if($platform == "xlr") {
            return new CrawlXlrService($platform);
        }else if($platform == "fenghuo") {
            return new CrawlFenghuoService($platform);
        }else if($platform == "wangyi") {
            return new CrawlWangyiService($platform);
        }else if($platform == "3dcp") {
            return new Craw3DService($platform);

        }else if($platform == "xglhc") {
            return new CrawXGLHCService($platform);
        }
        else if($platform == "boniu") {
            return new CrawlBoniuService($platform);
        }
        else if($platform == "tanc") {
            return new CrawTanCService($platform);
        }
        else if($platform == "bg099") {
            return new CrawlBGService($platform);
        }
        else if($platform == "cbp") {
            return new CrawCBPService($platform);
        }
        else if($platform == "yc") {
            return new CrawlYCService($platform);
        }
        else if($platform == "yg") {
            return new CrawlYGService($platform);
        }
        else if($platform == "sd") {
            return new CrawlSDService($platform);
        }
        else if($platform == "xtu") {
            return new CrawlXTUService($platform);
        }
        else if($platform == "lscp") {
            return new CrawlLSCPService($platform);
        }
        else if($platform == "jsgj") {
            return new CrawlJSGJService($platform);
        }
        else if($platform == "xycp") {
            return new CrawlXYCPService($platform);
        }
        else if($platform =='cp500'){
            return new CrawCP500Service($platform);
        }
        else if($platform =='ydniu'){
            return new CrawlYDNIUService($platform);
        }
        return null;
    }

}