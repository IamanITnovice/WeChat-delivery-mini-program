<?php

namespace app\common\library\storage;

use think\Exception;
use app\common\library\helper\FileUploadHelper;

/**
 * 存储模块驱动
 */
class Driver
{
    private $config;    // upload 配置
    private $engine;    // 当前存储引擎类
    protected $error;

    /**
     * 构造方法
     */
    public function __construct($config, $storage = null)
    {
        $this->config = $config;
        // 实例化当前存储引擎
        $this->engine = $this->getEngineClass($storage);
    }

    public function validate($name, $fileInfo, $sence = 'image'){
        try {
            // 第一层：基础验证（文件大小、扩展名）
            $this->validateBasic($name, $fileInfo, $sence);

            // 第二层：MIME 类型验证
            $mimeResult = FileUploadHelper::validateMimeType($fileInfo, $sence);
            if (!$mimeResult['valid']) {
                throw new Exception($mimeResult['error']);
            }

            // 第三层：文件内容验证（魔术字节）
            $contentResult = FileUploadHelper::validateFileContent($fileInfo);
            if (!$contentResult['valid']) {
                throw new Exception($contentResult['error']);
            }

            // 第四层：文件名安全验证
            $fileNameResult = FileUploadHelper::validateFileName($fileInfo);
            if (!$fileNameResult['valid']) {
                throw new Exception($fileNameResult['error']);
            }

            return true;
        } catch (\Exception $e) {
            $this->engine->error = $e->getMessage();
            return false;
        }
    }

    /**
     * 基础验证（文件大小、扩展名）
     */
    private function validateBasic($name, $fileInfo, $sence)
    {
        $rules = [];
        $messages = [];

        if ($sence == 'image') {
            $rules = [
                $name => [
                    'fileSize' => $this->config['max_image'] * 1024 * 1024,
                    'fileExt' => 'jpg,jpeg,png,gif,bmp,webp'
                ]
            ];
            $messages = [
                $name.'.fileSize' => '最大可上传'.$this->config['max_image'].'M图片',
                $name.'.fileExt' => '只能上传jpg,jpeg,png,gif,bmp,webp格式图片'
            ];
        } elseif ($sence == 'video') {
            $rules = [
                $name => [
                    'fileSize' => $this->config['max_video'] * 1024 * 1024,
                    'fileExt' => 'mp4,avi,wmv,flv'
                ]
            ];
            $messages = [
                $name.'.fileSize' => '最大可上传'.$this->config['max_video'].'M视频',
                $name.'.fileExt' => '只能上传mp4,avi,wmv,flv格式视频'
            ];
        } elseif ($sence == 'file') {
            $rules = [
                $name => [
                    'fileSize' => isset($this->config['max_file']) ? $this->config['max_file'] * 1024 * 1024 : 10 * 1024 * 1024,
                    'fileExt' => 'pdf,doc,docx,ppt,pptx,zip,txt'
                ]
            ];
            $messages = [
                $name.'.fileSize' => '文件过大',
                $name.'.fileExt' => '只能上传pdf,doc,docx,ppt,pptx,zip,txt格式文件'
            ];
        } elseif ($sence == 'excel') {
            $rules = [
                $name => [
                    'fileSize' => isset($this->config['max_file']) ? $this->config['max_file'] * 1024 * 1024 : 10 * 1024 * 1024,
                    'fileExt' => 'xls,xlsx,csv'
                ]
            ];
            $messages = [
                $name.'.fileSize' => '文件过大',
                $name.'.fileExt' => '只能上传xls,xlsx,csv格式文件'
            ];
        } else {
            throw new Exception('未知的文件场景: ' . $sence);
        }

        validate($rules, $messages)->check([$name => $fileInfo]);
    }

    /**
     * 设置上传的文件信息
     */
    public function setUploadFile($name = 'iFile')
    {
        $directory = '';
        if ($this->config['default'] != 'local') {
            $directory = $this->config['directory'];
        }
        return $this->engine->setUploadFile($name, $directory);
    }

    /**
     * 设置上传的文件信息
     */
    public function setUploadFileByReal($filePath)
    {
        return $this->engine->setUploadFileByReal($filePath);
    }

    /**
     * 执行文件上传
     */
    public function upload()
    {
        return $this->engine->upload();
    }

    /**
     * 执行文件删除
     */
    public function delete($fileName)
    {
        return $this->engine->delete($fileName);
    }

    /**
     * 获取错误信息
     */
    public function getError()
    {
        return $this->engine->getError();
    }

    /**
     * 获取文件路径
     */
    public function getFileName()
    {
        return $this->engine->getFileName();
    }

    /**
     * 返回文件信息
     */
    public function getFileInfo()
    {
        return $this->engine->getFileInfo();
    }

    /**
     * 获取当前的存储引擎
     */
    private function getEngineClass($storage = null)
    {
        $engineName = is_null($storage) ? $this->config['default'] : $storage;
        $classSpace = __NAMESPACE__ . '\\engine\\' . ucfirst($engineName);
        if (!class_exists($classSpace)) {
            throw new Exception('未找到存储引擎类: ' . $engineName);
        }
        return new $classSpace($this->config['engine'][$engineName]);
    }

}
