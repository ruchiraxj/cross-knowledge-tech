<?php

require 'vendor/predis/predis/src/Autoloader.php';

class Redis{
    private $client;
    private $con = [
        'scheme' => 'tcp',
        'host'   => '127.0.0.1',
        'port'   => 6379,
    ];

    public function __construct()
    {
        Predis\Autoloader::register();
        $this->client = new Predis\Client($this->con);
    }

    public function set($key, $value, $expire = 0){
        $this->client->set($key, $value);
        if($expire > 0){
            $this->client->expire($key, $expire);
        }
    }
    
    public function get($key){
        return $this->client->get($key);
    }

    public function isAvailable($key){
        return $this->client->exists($key);
    }
}
