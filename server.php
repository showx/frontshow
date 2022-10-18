#!/usr/local/bin/php
<?php
include_once "task.php";
// 初始化配置
$config = include_once "config.php";
task::$project = include_once "project.php";
task::$gitpath = realpath(__DIR__).'/git/';
$server = new \Swoole\Http\Server("0.0.0.0", 9501, \SWOOLE_BASE);
$server->set([
    'daemonize' => 0,
    // 一般是cpu两倍，但很少会同时构建两个程序
    'worker_num' => 2,
    'task_worker_num' => 2,
    'document_root' => realpath(__DIR__),
    // 'enable_static_handler' => true, 
    'http_autoindex' => false,
    // 'http_index_files' => ['index.html'],
    // 'user' => 'www-data',
    // 设置前端文件
]);
$server->on('Task', 'task::add');
$server->on('Finish', 'task::finish');
$server->on('Request', function ($request, $response) use ($server, $config) {
    $uri = $request->server['request_uri'];
    $uri = trim($uri, '/');
    if($uri == 'favicon.ico')
    {
        $response->write('ico'); 
        return true;
    }
    if(!isset($request->header['frontbuild_authorization']))
    {
        $response->status('401', 'Unauthorized');
        $response->header('WWW-Authenticate', 'Basic realm="My passport"');
        return true;
    }else{
        $authorization = $request->header['frontbuild_authorization'];
        $authorization = str_replace("Basic ", "" , $authorization);
        $authorization = base64_decode($authorization);
        $passport = explode(":", $authorization);
        if($passport['0'] != $config['account']['user'] && $passport['1'] != $config['account']['pass'])
        {
            $response->status('401', 'Unauthorized');
            $response->header('WWW-Authenticate', 'Basic realm="My passport"');
            $response->end('认证失败');
            return true;
        }
    }
    
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $uridata = explode('/', $uri);
    $action = $uridata['0'];
    $id = $uridata['1'] ?? '';
    if($action == 'build')
    {
        $tasks = [
            'action' => $action,
            'id' => $id,
        ];
        $status = $server->task($tasks);
        if($status)
        {
            $response->write('任务正在运行中'); 
        }
    }elseif($action == 'status'){
        $statusfile = realpath(__DIR__)."/status/{$id}.txt";
        $content = file_get_contents($statusfile);
        $response->write("<pre>");
        $response->write($content);  
    }elseif($action == ''){
        $content = file_get_contents(realpath(__DIR__).'/index.html');
        $response->write($content);
        return true;
    }
    $response->write(time()); 
    $response->write("<br/><a href='javascript:history.go(-1);'>返回</a><br/>");
    // $response->end('<pre>Task Result: '.var_export($result, true)); }); 
});
echo "前端构建系统 is started at http://0.0.0.0:9501\n"; 
$server->start();
