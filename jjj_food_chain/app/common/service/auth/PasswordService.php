<?php

namespace app\common\service\auth;

class PasswordService
{
    const PASSWORD_MIN_LENGTH = 8;
    const PASSWORD_MAX_LENGTH = 20;
    const LEGACY_SALT_MD5 = 'salt_md5';
    const LEGACY_PLAIN_MD5 = 'plain_md5';
    const DEFAULT_PASSWORD = '123456';  // 默认密码

    public static function hash($password)
    {
        return password_hash((string)$password, PASSWORD_DEFAULT);
    }

    public static function validate($password)
    {
        $password = (string)$password;
        if ($password === '') {
            return ['status' => false, 'msg' => '请输入登录密码'];
        }

        $length = strlen($password);
        if ($length < self::PASSWORD_MIN_LENGTH || $length > self::PASSWORD_MAX_LENGTH) {
            return [
                'status' => false,
                'msg' => '密码长度需为' . self::PASSWORD_MIN_LENGTH . '-' . self::PASSWORD_MAX_LENGTH . '位',
            ];
        }
        if (preg_match('/\s/', $password)) {
            return ['status' => false, 'msg' => '密码不能包含空格'];
        }
        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/\d/', $password)) {
            return ['status' => false, 'msg' => '密码需同时包含字母和数字'];
        }

        return ['status' => true, 'msg' => ''];
    }

    /**
     * 兼容旧版本的 checkStrength 方法
     * @param string $password
     * @param string $error 错误信息（引用参数）
     * @return bool
     */
    public static function checkStrength($password, &$error = '')
    {
        $result = self::validate($password);
        if (!$result['status']) {
            $error = $result['msg'];
            return false;
        }
        return true;
    }

    public static function verifyAndUpgrade($user, $password, $legacyTypes = [])
    {
        $password = (string)$password;
        $storedPassword = (string)($user['password'] ?? '');
        if ($storedPassword === '') {
            return false;
        }

        if (self::isPasswordHash($storedPassword)) {
            if (!password_verify($password, $storedPassword)) {
                return false;
            }
            if (password_needs_rehash($storedPassword, PASSWORD_DEFAULT)) {
                $user->save([
                    'password' => self::hash($password),
                ]);
            }
            return true;
        }

        if (!self::verifyLegacy($storedPassword, $password, $legacyTypes)) {
            return false;
        }

        $user->save([
            'password' => self::hash($password),
        ]);
        return true;
    }

    /**
     * 验证密码（支持多种调用方式）
     * 用法1: verify($user, $password, $legacyTypes) - 新版本
     * 用法2: verify($password, $hash) - 旧版本兼容
     * @return bool
     */
    public static function verify($arg1, $arg2, $arg3 = [])
    {
        // 新版本调用: verify($user, $password, $legacyTypes)
        if (is_object($arg1) || (is_array($arg1) && isset($arg1['password']))) {
            $user = $arg1;
            $password = (string)$arg2;
            $legacyTypes = $arg3;
            $storedPassword = (string)($user['password'] ?? '');

            if ($storedPassword === '') {
                return false;
            }

            // 验证现代格式
            if (self::isPasswordHash($storedPassword)) {
                return password_verify($password, $storedPassword);
            }

            // 验证旧格式
            return self::verifyLegacy($storedPassword, $password, $legacyTypes);
        }

        // 旧版本调用: verify($password, $hash)
        $password = (string)$arg1;
        $storedPassword = (string)$arg2;

        if ($storedPassword === '') {
            return false;
        }

        // 验证现代格式
        if (self::isPasswordHash($storedPassword)) {
            return password_verify($password, $storedPassword);
        }

        // 验证旧格式（默认支持 salt_hash）
        return self::verifyLegacy($storedPassword, $password, [self::LEGACY_SALT_MD5]);
    }

    /**
     * 检查密码是否需要重新哈希
     * @param string $passwordHash
     * @return bool
     */
    public static function needsRehash($passwordHash)
    {
        if (!self::isPasswordHash($passwordHash)) {
            return true;
        }
        return password_needs_rehash($passwordHash, PASSWORD_DEFAULT);
    }

    private static function verifyLegacy($storedPassword, $password, $legacyTypes)
    {
        foreach ($legacyTypes as $legacyType) {
            if ($legacyType === self::LEGACY_SALT_MD5 && hash_equals($storedPassword, salt_hash($password))) {
                return true;
            }
            if ($legacyType === self::LEGACY_PLAIN_MD5 && hash_equals($storedPassword, md5($password))) {
                return true;
            }
        }
        return false;
    }

    private static function isPasswordHash($password)
    {
        $info = password_get_info((string)$password);
        return isset($info['algo']) && !empty($info['algo']);
    }
}
