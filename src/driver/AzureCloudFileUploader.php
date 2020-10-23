<?php

namespace wslibs\cloud_upload\driver;

use epii\ui\upload\driver\IUploader;
use epii\ui\upload\driver\UploaderResult;
use wslibs\storage\CunChuIO;

class AzureCloudFileUploader implements IUploader
{
    private static $azure_cloud_url;

    private static $site = null;

    public function init($config = [])
    {
        CunChuIO::setConfig($config);
        if(isset($config['azure_cloud_url']) && !empty($config['azure_cloud_url'])){
            self::$azure_cloud_url = $config['azure_cloud_url'];
        }
        if(isset($config['site']) && !empty($config['site'])){
            self::$site = $config['site'];
        }
    }

    public function handlePostFiles(array $allowedExts = ["gif", "jpeg", "jpg", "png"], $file_size = 204800, $dir = null, $url_pre = null): UploaderResult
    {
        $files = [];
        if(isset($_FILES['file']['name']) && !empty($_FILES['file']['name']) && is_array($_FILES['file']['name'])){
            foreach ($_FILES['file']['name'] as $k => $v){
                $files[$k]['name'] = $v;
                $files[$k]['type'] = $_FILES['file']['type'][$k];
                $files[$k]['tmp_name'] = $_FILES['file']['tmp_name'][$k];
                $files[$k]['size'] = $_FILES['file']['size'][$k];
            }
        }else{
            $files = $_FILES;
        }

        $ret = new UploaderResult();
        if(count($files) == 0){

        }else{
            $paths = [];
            $url = [];

            foreach ($files as $k => $file){

                $temp = explode(".", $file["name"]);

                $extension = end($temp);     // 获取文件后缀名
                if(($file["size"] < $file_size) && in_array(strtolower($extension), $allowedExts)){
                    if($file['error'] > 0){
                        $ret->error($file["error"]);
                        break;
                    }else{
                        $file_path = self::getCloudFilePath($extension);
                        CunChuIO::uploadContent($file_path , file_get_contents($file['tmp_name']));
                        $cloud_url = self::getCloudRootDir().$file_path;
                        $url[] = $cloud_url;
                        $paths[] = $file_path;
                    }
                }else{
                    $ret->error("格式或大小不符合");
                    break;
                }
            }

            if (count($paths) > 0) {
                $ret->success(implode(",", $paths), implode(",", $url));
            } else {
                $ret->error("格式或大小不符合");
            }
        }

        return $ret;
    }

    public function del(array $data): bool
    {
        // TODO: Implement del() method.
        return true;
    }

    private static function getCloudFilePath($ext)
    {
        if(!is_null(self::$site)){
            return self::$site ."/".date("His").uniqid().".".$ext;
        }else{
            return date("Ymd")."/".date("His").uniqid().".".$ext;
        }
    }

    public static function getCloudRootDir()
    {
        if(self::$azure_cloud_url){
            return self::$azure_cloud_url . "/" .CunChuIO::$rongqi."/uploads/";
        }
        return "https://".CunChuIO::$storename.".blob.core.chinacloudapi.cn/".CunChuIO::$rongqi."/uploads/";
    }
}