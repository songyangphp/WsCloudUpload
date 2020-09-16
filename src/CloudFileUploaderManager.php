<?php

namespace wslibs\cloud_upload;

use epii\ui\upload\AdminUiUpload;
use epii\ui\upload\driver\IUploader;
use wslibs\cloud_upload\driver\AzureCloudFileUploader;
use wslibs\storage\CunChuIO;

class CloudFileUploaderManager
{
    public static function init(array $cloud_chunchu_config = [] , string $uploader = null)
    {
        if(!is_array($cloud_chunchu_config) || empty($cloud_chunchu_config)){
            return;
        }else{
            if($cloud_chunchu_config && $uploader){
                if(class_exists($uploader)){
                    $uploader_object = new $uploader;
                    if(new $uploader_object instanceof IUploader){
                        $uploader_object->init($cloud_chunchu_config);
                        AdminUiUpload::setUploadHandler($uploader);
                    }else{
                        return;
                    }
                }else{
                    return;
                }
            }else{ //默认使用微软云
                $uploader_object = new AzureCloudFileUploader();
                $uploader_object->init($cloud_chunchu_config);
                AdminUiUpload::setUploadHandler(AzureCloudFileUploader::class);
            }
        }
    }
}