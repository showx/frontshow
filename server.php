#!/usr/local/bin/php
<?php
require 'vendor/autoload.php';
include_once "task.php";
// 初始化配置
$config = include_once "config.php";
$project = task::$project = include_once "project.php";
task::$gitpath = realpath(__DIR__).'/git/';
task::$cos_secretId = $config['cos']['secretid'];
task::$cos_secretKey = $config['cos']['secretkey'];
task::$cos_bucket = $config['cos']['bucket'];
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
$server->on('Request', function ($request, $response) use ($server, $config, $project) {
    $uri = $request->server['request_uri'];
    $uri = trim($uri, '/');
    if($uri == 'favicon.ico')
    {
        $response->write('ico'); 
        return true;
    }
    if(!isset($request->header['authorization']))
    {
        $response->status('401', 'Unauthorized');
        $response->header('WWW-Authenticate', 'Basic realm="My passport"');
        return true;
    }else{
        $authorization = $request->header['authorization'];
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

        $content = '
<!doctype html>
<html lang="zh-CN">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="canonical" href="https://getbootstrap.com/docs/3.4/examples/justified-nav/">
    <title>前端构建系统</title>
    <link href="https://cdn.jsdelivr.net/npm/@bootcss/v3.bootcss.com@1.0.25/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@bootcss/v3.bootcss.com@1.0.25/assets/css/ie10-viewport-bug-workaround.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@bootcss/v3.bootcss.com@1.0.25/examples/justified-nav/justified-nav.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/@bootcss/v3.bootcss.com@1.0.25/assets/js/ie-emulation-modes-warning.js"></script>
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
      <div class="masthead">
        <h3 class="text-muted">前端构建系统</h3>
        <nav>
          <ul class="nav nav-justified">
            <li class="active"><a href="#">Home</a></li>
          </ul>
        </nav>
      </div>
      <div class="jumbotron">
        <h1>front CI/CD</h1>
        <p class="lead">frontshow,前端构建系统</p>
        <p><a class="btn btn-lg btn-success" href="#" role="button">Get started！</a></p>
      </div>
      ';
        //闭合开关
        $j = 0;
        $i = 0;
        foreach($project as $proid => $proj){
            if($i % 3 == 0){
                if($j == 1){
                    $content .= '</div>
            <div class="row">';
                    $j = 0;
                }else{
                    $content .= '<div class="row">';
                    $j = 1;
                }
            }
            // 内容正文
            $content .= '
            <div class="col-lg-4">
            <h2>'.$proj['projectname'].'</h2>
            <p class="text-danger">'.$proj['url'].'</p>
            <p>'.$proj['description'].'</p>
            <p>
                <a class="btn btn-primary" href="/build/'.$proid.'" role="button">构建 &raquo;</a>&nbsp;
                <a class="btn btn-success" href="/status/'.$proid.'" role="button">详情 &raquo;</a>
            </p>
            </div>';
            $i++;
        }
        if($j == 1){
            $content .= '</div>';
        }
      $content .= '
      <footer class="footer">
        <p>&copy; frontshow</p>
      </footer>
    </div> <!-- /container -->
  </body>
</html>';
        // $content = file_get_contents(realpath(__DIR__).'/index.html');
        $response->write($content);
        return true;
    }
    $response->write(time()); 
    $response->write("<br/><a href='javascript:history.go(-1);'>返回</a><br/>");
    // $response->end('<pre>Task Result: '.var_export($result, true)); }); 
});
echo "前端构建系统 is started at http://0.0.0.0:9501\n"; 
$server->start();
