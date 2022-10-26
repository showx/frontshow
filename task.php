<?php
class task{
    public static $project = [];
    public static $gitpath;
    public static function add(\Swoole\Server $server, $task_id, $worker_id, $data){
        $id = $data['id'];
        $lockfile = realpath(__DIR__)."/lock/{$id}.txt";
        $statusfile = realpath(__DIR__)."/status/{$id}.txt";
        if(isset(self::$project[$id])){
            $project = self::$project[$id];
        }else{
            echo '不存在的id'.$id;
            return true;
        }
        // 存在锁的情况下
        if(!file_exists($lockfile)){
            // 开启锁
            file_put_contents($lockfile, 'lock'.time());
            // 开始运行要重新记录一下
            file_put_contents($statusfile, '');
            // 执行处理逻辑
            $path = self::$gitpath;
            self::log2("【{$project['projectname']}】", $statusfile);
            self::log2("--------------------------", $statusfile);
            self::log('* 获取代码仓库信息', $statusfile);
            $init = 0;
            if(!file_exists($path.$project['name']))
            {
                $init = 1;
                self::exec("cd {$path} && git clone ".$project['git']);
            }else{
                self::exec("cd {$path}{$project['name']} && git pull ".$project['git']);
            }
            if($init == 1){
                self::log('* 前端依赖库安装', $statusfile);
                self::exec("cd {$path}{$project['name']} && npm install");
            }
            self::log('* 代码仓库前端构建', $statusfile);
            self::exec("cd {$path}{$project['name']} && npm run build");

            self::log('* 同步代码到服务器', $statusfile);
            // --delete比较危险，确认没问题再使用
            self::exec("rsync -av --progress --delete --bwlimit=500 {$path}{$project['name']}{$project['sourcedir']} {$project['targetuser']}@{$project['targethost']}:{$project['targetdir']}");
            // self::exec("rsync -av --progress --bwlimit=500 {$path}{$config['name']}{$config['sourcedir']} {$config['targetuser']}@{$config['targethost']}:{$config['targetdir']}");
            unlink($lockfile);
            self::log('* 项目处理运行结束', $statusfile);
            self::log2("--------------------------", $statusfile);
        }
        $server->finish(['time' => time()]);
    }

    public static function finish(\Swoole\Server $server, int $task_id, mixed $data){
        echo "task:".$task_id."运行结束".PHP_EOL;
    }

    public static function log($str = '', $logfile = 'build.txt')
    {
        echo $str.PHP_EOL.PHP_EOL;
        if(file_exists($logfile)){
            file_put_contents($logfile, str_pad($str, 50)."|".date("Ymd H:i:s")."\r\n\r\n", FILE_APPEND|LOCK_EX);
        }
        self::historylog($str);
    }

    public static function log2($str = '', $logfile = 'build.txt')
    {
        echo $str.PHP_EOL.PHP_EOL;
        if(file_exists($logfile)){
            file_put_contents($logfile, str_pad($str, 50)."\r\n\r\n", FILE_APPEND|LOCK_EX);
        }
        self::historylog($str);
    }

    public static function exec($command = ''){
        self::log('* run:->'.$command);
        exec($command);
    }

    public static function historylog($str = ''){
        $historyfile = realpath(__DIR__).'/history/'.date('Ymd').'.txt';
        if(file_exists($historyfile)){
            file_put_contents($historyfile, str_pad($str, 50)."|".date("Ymd H:i:s")."\r\n\r\n", FILE_APPEND|LOCK_EX);
        }else{
            file_put_contents($historyfile, str_pad($str, 50)."|".date("Ymd H:i:s")."\r\n\r\n");
        }
        
    }

}
?>