<?php

namespace wslibs\cloud_upload\driver;

use epii\ui\upload\driver\IUploader;
use epii\ui\upload\driver\UploaderResult;
use wslibs\storage\CunChuIO;

class AzureCloudFileUploader implements IUploader
{
    public function init($config = [])
    {
        CunChuIO::setConfig($config);
    }

    public function handlePostFiles(array $allowedExts = ["gif", "jpeg", "jpg", "png"], $file_size = 204800, $dir = null, $url_pre = null): UploaderResult
    {
        $ret = new UploaderResult();
        if(count($_FILES) == 0){

        }else{
            $paths = [];
            $url = [];

            foreach ($_FILES as $k => $file){

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
                        $paths[] = $cloud_url;
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
        return date("Ymd")."/".date("His").uniqid().".".$ext;
    }

    private static function getCloudRootDir()
    {
        return "https://wszxstore.blob.core.chinacloudapi.cn/".CunChuIO::$rongqi."/uploads/";
    }
}