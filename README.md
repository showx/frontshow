# frontshow
前端页面构建系统，为了简化发布流程，前端只需要关心业务代码，每次发布，只需要构建并同步相应的服务器。

![avatar](/node/demo.png)

# 所需环境
1. php 8.0.3 (需要swoole扩展)
2. node.js (npm)
3. git

# 项目配置
config.php是web页面的登录账号
project.php是程序的项目
使用docker需把/frontshow/conf映射到本地，达到后面动态加载配置
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
        'targetdir' => '/test/web',
    ],
    
];
```

#### 安装使用
配置好config.php(主要腾讯云cos的配置)和project.php(项目git地址与目标文件夹)，使用docker构建一下
```
docker build -t frontshow:v1 .
# docker run -it -d -p 9501:9501 --name frontshow frontshow:v1
docker run -it -d -p 9501:9501 -v //d/code/conf:/frontshow/conf --name frontshow frontshow:v1
```
浏览器输入localhost:9501即可构建

#### 用到的相关命令
## rsync
rsync -avzP -e "ssh -i ~/sshkey.pem" ubuntu@xx.xxx.xx.xxx:Projects/sample.csv ~/sample.csv

## cos
https://cloud.tencent.com/document/product/436/12266

#### todolist
1. 优化后台登录体验

#### 注意事项
1. 上传到cos，前端使用vue只能使用hash模式。