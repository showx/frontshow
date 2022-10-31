<?php
class cosutil{
    $bucket = "";
    public function upload(){
        try {
            $bucket = "examplebucket-1250000000"; //存储桶名称 格式：BucketName-APPID
            $key = "exampleobject"; //此处的 key 为对象键，对象键是对象在存储桶中的唯一标识
            $srcPath = "path/to/localFile";//本地文件绝对路径
            $file = fopen($srcPath, "rb");
            if ($file) {
                $result = $cosClient->putObject(array(
                    'Bucket' => $bucket,
                    'Key' => $key,
                    'Body' => $file));
                print_r($result);
            }
        } catch (\Exception $e) {
            echo "$e\n";
        }
    }
}