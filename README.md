# frontshow
前端页面构建系统，为了简化发布流程，前端只需要关心业务代码，每次发布，只需要构建并同步相应的服务器。

# 所需环境
1. php 8.0.3 (需要swoole扩展)
2. node.js (npm)
3. git

# 项目配置
config.php是web页面的登录账号
project.php是程序的项目
```PHP
return [
    // 项目1
    1000 => [
        'projectname' => '测试',
        'description' => '测试',
        'name' => 'frontweb',
        'git' => 'git@gitee.com:test/test.git',
        'url' => 'http://test.cn',
        'sourcedir' => '/dist/',
        'targethost' => '127.0.0.1',
        'targetuser' => 'www-data',
        'targetdir' => '/test/web',
    ],
    
];
```

# 用到的相关命令
## rsync
rsync -avzP -e "ssh -i ~/sshkey.pem" ubuntu@xx.xxx.xx.xxx:Projects/sample.csv ~/sample.csv
