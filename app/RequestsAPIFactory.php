<?php

namespace App;

class RequestsAPIFactory
{
    private $email;
    private $mainQuery;
    private $days;
    private $nowDate;
    private $requests;

    public function __construct($data, $mail)
    {
        $this->mainQuery = $data;
        $this->email = $mail;
        $this->days = $data['service']['days'];
        $this->nowDate = $data['service']['date'];
    }

    public function getRequests() : array
    {
        if (!isset($this->requests)) $this->queueConstruct();
        return $this->requests;
    }

    private function queueConstruct() : void
    {
        $queue = array();
        $queue[0] = $this->mainQuery;
        unset($queue[0]['service']);
        $queue[0]['type'] = 'summary';
        for ($i = 0; $i < $this->days; $i++) {
            $date = date("d.m.Y",($this->nowDate + $i * 24 * 3600));
            $queue[$i + 1] = $this->mainQuery;
            $queue[$i + 1]['query']['f7'] = 1;
            $queue[$i + 1]['query']['data'] = $date;
            $queue[$i + 1]['query']['d2'] = $date;
            unset($queue[$i + 1]['service']);
            $queue[$i + 1]['type'] = 'separate';
        }
        $this->requests = $queue;
    }
}
