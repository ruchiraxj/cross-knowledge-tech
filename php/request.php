<?php

require 'Redis.php';

class SimpleJsonRequest
{
    //Redis Cache expiry time in Seconds
    private static $cache_exp = 600;

    private static function queryParamBuild($url, $parameters = []){
        $url .= ($parameters ? '?' . http_build_query($parameters) : '');
        return $url;
    }
    
    private static function hashRedisKey($key){
        return hash('sha256', $key);
    }
    
    private static function makeRequest(string $method, string $url, array $parameters = null, array $data = null)
    {
        $opts = [
            'http' => [
                'method'  => $method,
                'header'  => 'Content-type: application/json',
                'content' => $data ? json_encode($data) : null
            ]
        ];

        $url = self::queryParamBuild($url, $parameters);
        return file_get_contents($url, false, stream_context_create($opts));
    }

    public static function get(string $url, array $parameters = null)
    {
        //generate hash as the redis key
        $key =  self::hashRedisKey(self::queryParamBuild($url, $parameters));
        
        //Init redis class
        $redis = new Redis();
       
        if($redis->isAvailable($key)){ //check if key is available in the redis DB
            //If KEY is available return the cached data
            $response = $redis->get($key);
        }else{
            //IF KEY is not available make the request
            $response = self::makeRequest('GET', $url, $parameters);
            
            //SET redis cache
            $redis->set($key, $response, self::$cache_exp);
        }
        
        return json_decode($response);
    }

    public static function post(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('POST', $url, $parameters, $data));
    }

    public static function put(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PUT', $url, $parameters, $data));
    }   

    public static function patch(string $url, array $parameters = null, array $data)
    {
        return json_decode(self::makeRequest('PATCH', $url, $parameters, $data));
    }

    public static function delete(string $url, array $parameters = null, array $data = null)
    {
        return json_decode(self::makeRequest('DELETE', $url, $parameters, $data));
    }
}


// $req = new SimpleJsonRequest();
// $res = $req->get("http://localhost/personal/assignment/php/test.txt", ['n' => 'letter 1', 'b' => 'letter 2']);
