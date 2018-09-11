<?php

namespace TaskController;
use Workerman\Lib\Timer;
class TaskCraw
{
    private $crawSerives = array();
    public function __construct()
    {

    }
    public function addCrawSerive($service)
    {
        $this->crawSerives[] = $service;
    }

    public function runALLService()
    {
        foreach ($this->crawSerives as $ss)
        {
            Timer::add(1, array($ss, 'run'), array(), true);
        }
    }
}