<?php

namespace app\common\service\auth;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use think\facade\Env;
use think\Request;
use think\facade\Cache;
use think\facade\Config;

/** Token服务 */
class TokenService
{

    /** 签发Token */
    public static function issue($uid, $type)
    {
        $config = self::getTypeConfig($type);
        if ($config['driver'] === 'cache') {
            return self::issueCacheToken($uid, $type, $config);
        }
        return self::issueJwtToken($uid, $type, $config);
    }

    /** 校验Token */
    public static function validate($token, $type)
    {
        $token = trim((string)$token);
        if ($token === '') {
            return ['code' => -1, 'msg' => 'token 无效'];
        }

        $config = self::getTypeConfig($type);
        if ($config['driver'] === 'cache') {
            return self::validateCacheToken($token, $type, $config);
        }
        return self::validateJwtToken($token, $type, $config);
    }

    /** 销毁Token */
    public static function invalidate($token, $type)
    {
        $token = trim((string)$token);
        if ($token === '') {
            return false;
        }

        $config = self::getTypeConfig($type);
        if ($config['driver'] === 'cache') {
            Cache::delete(self::cacheTokenKey($token, $type, $config));
            if (!empty($config['legacy_cache'])) {
                Cache::delete($token);
            }
            return true;
        }

        $ttl = (int)$config['ttl'];
        try {
            $decoded = JWT::decode($token, new Key(self::getJwtKey($type), 'HS256'));
            $payload = json_decode(json_encode($decoded), true);
            if (!empty($payload['exp'])) {
                $ttl = max(1, (int)$payload['exp'] - time());
            }
        } catch (\Throwable $e) {
        }

        Cache::set(self::jwtBlacklistKey($token, $type), 1, $ttl);
        return true;
    }

    /** 获取请求Token */
    public static function getRequestToken($request = null)
    {
        /** @var Request $request */
        $request = $request ?: request();
        $headerName = Config::get('auth.request_header', 'authori-zation');

        $authorization = (string)$request->header($headerName);
        if ($token = self::extractBearerToken($authorization)) {
            return $token;
        }

        return '';
    }

    /** 同步响应Token */
    public static function syncResponseToken($token)
    {
        $token = trim((string)$token);
        if ($token === '') {
            return;
        }

        $responseHeader = Config::get('auth.response_header', 'Authori-Zation');
        header('Access-Control-Expose-Headers: ' . $responseHeader);
        header($responseHeader . ': ' . self::formatBearerToken($token));
    }

    /** Bearer格式 */
    public static function formatBearerToken($token)
    {
        return Config::get('auth.bearer_prefix', 'Bearer ') . trim((string)$token);
    }

    /** 提取Bearer */
    public static function extractBearerToken($authorization)
    {
        $authorization = trim((string)$authorization);
        if ($authorization === '') {
            return '';
        }
        if (stripos($authorization, 'Bearer ') === 0) {
            return trim(substr($authorization, 7));
        }
        return $authorization;
    }

    /** 签发JWT */
    private static function issueJwtToken($uid, $type, $config)
    {
        $now = time();
        $payload = [
            'iss' => trim((string)Env::get('secret.salt', '')) . ':' . $type,
            'aud' => $type,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + (int)$config['ttl'],
            'jti' => sha1(getGuidV4() . microtime(true)),
            'data' => [
                'uid' => $uid,
                'type' => $type,
            ],
        ];
        return JWT::encode($payload, self::getJwtKey($type), 'HS256');
    }

    /** 校验JWT */
    private static function validateJwtToken($token, $type, $config)
    {
        if (Cache::get(self::jwtBlacklistKey($token, $type))) {
            return ['code' => -1, 'msg' => 'token 无效'];
        }

        try {
            JWT::$leeway = (int)Config::get('auth.jwt_leeway', 60);
            $decoded = JWT::decode($token, new Key(self::getJwtKey($type), 'HS256'));
            $payload = json_decode(json_encode($decoded), true);
            $data = $payload['data'] ?? [];
            $data['type'] = $data['type'] ?? $type;

            $result = [
                'code' => 1,
                'data' => $data,
            ];

            $renewMode = $config['renew_mode'] ?? 'manual';
            $renewWindow = (int)($config['renew_window'] ?? 0);
            $remain = (int)($payload['exp'] ?? 0) - time();
            if ($renewMode === 'sliding' && $renewWindow > 0 && $remain > 0 && $remain <= $renewWindow) {
                $newToken = self::issue($data['uid'], $type);
                self::syncResponseToken($newToken);
                $result['token'] = $newToken;
            }

            return $result;
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return ['code' => -1, 'msg' => 'token 签名错误'];
        } catch (\Firebase\JWT\BeforeValidException $e) {
            return ['code' => -1, 'msg' => 'token 尚未生效'];
        } catch (\Firebase\JWT\ExpiredException $e) {
            return ['code' => -1, 'msg' => 'token 已过期'];
        } catch (\Throwable $e) {
            return ['code' => -1, 'msg' => 'token 校验失败'];
        }
    }

    /** 签发缓存Token */
    private static function issueCacheToken($uid, $type, $config)
    {
        try {
            $token = bin2hex(random_bytes(24));
        } catch (\Throwable $e) {
            $token = md5($type . '_' . $uid . '_' . microtime(true) . '_' . getGuidV4());
        }

        $payload = [
            'uid' => $uid,
            'type' => $type,
            'iat' => time(),
        ];
        Cache::set(self::cacheTokenKey($token, $type, $config), $payload, (int)$config['ttl']);
        return $token;
    }

    /** 校验缓存Token */
    private static function validateCacheToken($token, $type, $config)
    {
        $payload = Cache::get(self::cacheTokenKey($token, $type, $config));
        $legacyValue = null;
        if (empty($payload) && !empty($config['legacy_cache'])) {
            $legacyValue = Cache::get($token);
            $payload = $legacyValue;
        }

        if (empty($payload)) {
            return ['code' => -1, 'msg' => 'token 无效'];
        }

        if (!is_array($payload)) {
            $payload = [
                'uid' => $payload,
                'type' => $type,
            ];
        } else {
            $payload['type'] = $payload['type'] ?? $type;
        }

        if (($config['renew_mode'] ?? '') === 'touch') {
            Cache::set(self::cacheTokenKey($token, $type, $config), $payload, (int)$config['ttl']);
            if (!empty($config['legacy_cache']) && $legacyValue !== null) {
                Cache::set($token, $payload['uid'], (int)$config['ttl']);
            }
        }

        return [
            'code' => 1,
            'data' => $payload,
        ];
    }

    /** 缓存Key */
    private static function cacheTokenKey($token, $type, $config)
    {
        $prefix = $config['cache_prefix'] ?? ('auth:' . $type . ':token:');
        return $prefix . $token;
    }

    /** 黑名单Key */
    private static function jwtBlacklistKey($token, $type)
    {
        return 'auth:' . $type . ':blacklist:' . sha1($token);
    }

    /** JWT密钥 */
    private static function getJwtKey($type)
    {
        $salt = trim((string)Env::get('secret.salt', ''));
        if ($salt === '') {
            throw new \RuntimeException('Token 功能需要配置 SECRET.SALT，请在 .env 文件中添加配置项（至少32位随机字符串）');
        }
        if (strlen($salt) < 32) {
            throw new \RuntimeException('SECRET.SALT 长度不足，当前 ' . strlen($salt) . ' 位，需要至少 32 位，请在 .env 文件中修改');
        }
        return $salt . $type;
    }

    /** Token配置 */
    private static function getTypeConfig($type)
    {
        $config = Config::get('auth.tokens.' . $type);
        if (!is_array($config) || empty($config)) {
            throw new \InvalidArgumentException('Unsupported token type: ' . $type);
        }
        return $config;
    }

    // ==================== 兼容旧版本方法 ====================

    // Token 类型常量（兼容旧版本）
    const TYPE_ADMIN = 'admin';
    const TYPE_SHOP = 'shop';
    const TYPE_USER = 'user';

    /**
     * 签发 Token（兼容旧版本）
     * @param int $uid 用户ID
     * @param string $type Token类型
     * @param array $extra 额外数据（暂不支持）
     * @return string
     */
    public static function sign($uid, $type, $extra = [])
    {
        return self::issue($uid, $type);
    }

    /**
     * 验证 Token（兼容旧版本）
     * @param string $token
     * @param string $type Token类型
     * @return array ['code' => 1, 'data' => [...]] 或 ['code' => -1, 'msg' => '...']
     */
    public static function verify($token, $type)
    {
        return self::validate($token, $type);
    }

    /**
     * 销毁 Token（兼容旧版本）
     * @param string $token
     * @return bool
     */
    public static function destroy($token)
    {
        // 尝试所有类型
        $types = [self::TYPE_ADMIN, self::TYPE_SHOP, self::TYPE_USER];
        foreach ($types as $type) {
            try {
                self::invalidate($token, $type);
            } catch (\Throwable $e) {
                // 忽略错误，继续尝试其他类型
            }
        }
        return true;
    }

    /**
     * 从 Header 中获取 Token（兼容旧版本）
     * @return string|false
     */
    public static function getTokenFromHeader()
    {
        $token = self::getRequestToken();
        return $token !== '' ? $token : false;
    }
}
