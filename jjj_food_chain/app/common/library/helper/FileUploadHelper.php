<?php

namespace app\common\library\helper;

/**
 * 文件上传安全辅助类
 */
class FileUploadHelper
{
    /**
     * MIME 类型白名单配置
     */
    private static $mimeWhitelist = [
        'image' => [
            'image/jpeg',
            'image/pjpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/bmp',
            'image/x-ms-bmp'
        ],
        'video' => [
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/x-msvideo',
            'video/x-ms-wmv',
            'video/x-flv'
        ],
        'file' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
            'application/x-zip-compressed',
            'text/plain'
        ],
        'excel' => [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'text/comma-separated-values'
        ]
    ];

    /**
     * 文件魔术字节（文件头）白名单
     * 格式: 扩展名 => [可能的文件头十六进制]
     */
    private static $magicBytes = [
        'jpg' => ['FFD8FF'],
        'jpeg' => ['FFD8FF'],
        'png' => ['89504E47'],
        'gif' => ['474946383761', '474946383961'], // GIF87a, GIF89a
        'bmp' => ['424D'],
        'webp' => ['52494646'], // RIFF
        'pdf' => ['25504446'],
        'zip' => ['504B0304', '504B0506', '504B0708'],
        'doc' => ['D0CF11E0'],
        'docx' => ['504B0304'],
        'xls' => ['D0CF11E0'],
        'xlsx' => ['504B0304'],
        'ppt' => ['D0CF11E0'],
        'pptx' => ['504B0304'],
        'mp4' => ['00000018', '00000020', '66747970'],
        'avi' => ['52494646'],
        'wmv' => ['3026B275'],
        'flv' => ['464C56']
    ];

    /**
     * 验证 MIME 类型
     *
     * @param object $fileInfo 文件信息对象
     * @param string $scene 场景（image/video/file/excel）
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateMimeType($fileInfo, $scene = 'image')
    {
        if (!isset(self::$mimeWhitelist[$scene])) {
            return ['valid' => false, 'error' => '未知的文件场景'];
        }

        $mimeType = $fileInfo->getMime();
        $allowedMimes = self::$mimeWhitelist[$scene];

        if (!in_array($mimeType, $allowedMimes)) {
            return [
                'valid' => false,
                'error' => "不允许的文件类型: {$mimeType}，仅支持: " . implode(', ', $allowedMimes)
            ];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * 验证文件内容（魔术字节）
     *
     * @param object $fileInfo 文件信息对象
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateFileContent($fileInfo)
    {
        $extension = strtolower($fileInfo->getOriginalExtension());

        // 如果扩展名不在魔术字节列表中，跳过验证
        if (!isset(self::$magicBytes[$extension])) {
            return ['valid' => true, 'error' => ''];
        }

        $filePath = $fileInfo->getPathname();

        // 读取文件前 8 个字节
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return ['valid' => false, 'error' => '无法读取文件内容'];
        }

        $bytes = fread($handle, 8);
        fclose($handle);

        if ($bytes === false) {
            return ['valid' => false, 'error' => '读取文件内容失败'];
        }

        // 转换为十六进制
        $hex = strtoupper(bin2hex($bytes));

        // 检查是否匹配任何一个魔术字节
        $allowedMagicBytes = self::$magicBytes[$extension];
        $matched = false;

        foreach ($allowedMagicBytes as $magicByte) {
            if (strpos($hex, $magicByte) === 0) {
                $matched = true;
                break;
            }
        }

        if (!$matched) {
            return [
                'valid' => false,
                'error' => "文件内容与扩展名 .{$extension} 不匹配，可能是伪造文件"
            ];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * 验证文件名安全性
     *
     * @param object $fileInfo 文件信息对象
     * @return array ['valid' => bool, 'error' => string]
     */
    public static function validateFileName($fileInfo)
    {
        $originalName = $fileInfo->getOriginalName();
        $extension = $fileInfo->getOriginalExtension();

        // 检查文件名长度
        if (strlen($originalName) > 255) {
            return ['valid' => false, 'error' => '文件名过长，最大支持 255 字符'];
        }

        // 检查是否包含路径穿越字符
        $dangerousPatterns = ['../', '.\\', '%00', '\0'];
        foreach ($dangerousPatterns as $pattern) {
            if (strpos($originalName, $pattern) !== false) {
                return ['valid' => false, 'error' => '文件名包含非法字符'];
            }
        }

        // 检查扩展名是否包含多个点号（双扩展名攻击）
        $nameParts = explode('.', $originalName);
        if (count($nameParts) > 2) {
            // 检查倒数第二个部分是否是危险扩展名
            $dangerousExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'sh', 'py', 'pl', 'cgi'];
            $secondExt = strtolower($nameParts[count($nameParts) - 2]);

            if (in_array($secondExt, $dangerousExts)) {
                return ['valid' => false, 'error' => '检测到双扩展名攻击'];
            }
        }

        // 检查扩展名是否为危险类型
        $dangerousExts = ['php', 'php3', 'php4', 'php5', 'phtml', 'asp', 'aspx', 'jsp', 'sh', 'py', 'pl', 'cgi', 'exe', 'bat', 'cmd'];
        if (in_array(strtolower($extension), $dangerousExts)) {
            return ['valid' => false, 'error' => '不允许上传可执行文件'];
        }

        return ['valid' => true, 'error' => ''];
    }

    /**
     * 标准化扩展名（统一转小写）
     *
     * @param string $extension 扩展名
     * @return string
     */
    public static function normalizeExtension($extension)
    {
        return strtolower(trim($extension));
    }

    /**
     * 检查扩展名是否在白名单中（不区分大小写）
     *
     * @param string $extension 扩展名
     * @param string $allowedExts 允许的扩展名列表（逗号分隔）
     * @return bool
     */
    public static function isExtensionAllowed($extension, $allowedExts)
    {
        $extension = self::normalizeExtension($extension);
        $allowedList = array_map('trim', explode(',', strtolower($allowedExts)));

        return in_array($extension, $allowedList);
    }
}
