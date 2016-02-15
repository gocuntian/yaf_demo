<?php

/**
 * ------------------------------------------------------------------------------
 * Description http_server
 * @filename http_server.php
 * @author xingcuntian
 * @datetime 2016-02-15 10:26:16
 * -------------------------------------------------------------------------------
 */
class HttpServer {

    public static $instance;
    public static $get;
    public static $post;
    public static $header;
    public static $server;
    private $application;

    public function __construct() {
        $http = new swoole_http_server('0.0.0.0', 9502);
        $http->set(array(
            'worker_num' => 4,
            'daemonize' => 0,
            'dispatch_mode' => 1
        ));
        $http->on('WorkerStart', array($this, 'onWorkerStart'));
        $http->on('request',function($request,$response){
            if(isset($request->server)){
                HttpServer::$server = $request->server;
            }else{
                HttpServer::$server = [];
            }
            
            if(isset($request->header)){
                HttpServer::$header = $request->header;
            }else{
                HttpServer::$header = [];
            }
            
            if(isset($request->get)){
                HttpServer::$get = $request->get;
            }else{
                HttpServer::$get = [];
            }
            
            if(isset($request->post)){
                HttpServer::$post = $request->post;
            }else{
                HttpServer::$post = [];
            }
            
            ob_start();
            try{
                $yaf_request = new Yaf_Request_Http(HttpServer::$server['request_uri']);
                $this->application->getDispatcher()->dispatch($yaf_request);
                
            } catch (Yaf_Exception $e) {
                var_dump($e);
            }
            $result = ob_get_contents();
            ob_end_clean();
            $response->end($result);
        });
        $http->start();
    }
    
    /**
     * 载入框架配置
     */
    public function onWorkerStart() {
        define('APPLICATION_PATH', dirname(__FILE__));
        $this->application = new Yaf_Application(APPLICATION_PATH . "/conf/application.ini");
        ob_start();
        $this->application->bootstrap()->run();
        ob_end_clean();
    }
    
    /**
     * 单例入口
     */
    public static function getInstance(){
        if(!self::$instance){
            self::$instance = new HttpServer();
        }
        return self::$instance;
    }

}

HttpServer::getInstance();

