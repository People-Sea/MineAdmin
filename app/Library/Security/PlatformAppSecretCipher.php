<?php

declare(strict_types=1);

namespace App\Library\Security;

use RuntimeException;

final class PlatformAppSecretCipher
{
    public function encrypt(string $value): string
    {
        $cipher = $this->cipher();
        $key = $this->key();
        $ivLength = openssl_cipher_iv_length($cipher);
        $iv = random_bytes($ivLength);
        $tag = '';

        $encrypted = openssl_encrypt(
            $value,
            $cipher,
            $key,
            OPENSSL_RAW_DATA,
            $iv,
            $tag
        );

        if ($encrypted === false) {
            throw new RuntimeException('平台应用密钥加密失败');
        }

        return base64_encode(json_encode([
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'value' => base64_encode($encrypted),
        ], JSON_THROW_ON_ERROR));
    }

    public function decrypt(string $payload): string
    {
        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            throw new RuntimeException('平台应用密钥解密失败');
        }

        /** @var array{iv:string,tag:string,value:string} $data */
        $data = json_decode($decoded, true, 512, JSON_THROW_ON_ERROR);
        $plain = openssl_decrypt(
            base64_decode($data['value'], true) ?: '',
            $this->cipher(),
            $this->key(),
            OPENSSL_RAW_DATA,
            base64_decode($data['iv'], true) ?: '',
            base64_decode($data['tag'], true) ?: ''
        );

        if ($plain === false) {
            throw new RuntimeException('平台应用密钥解密失败');
        }

        return $plain;
    }

    private function cipher(): string
    {
        return (string) config('platform_app.secret_cipher.cipher', 'aes-256-gcm');
    }

    private function key(): string
    {
        $rawKey = (string) config('platform_app.secret_cipher.key', '');
        if ($rawKey === '') {
            throw new RuntimeException('缺少平台应用密钥加密配置 PLATFORM_APP_SECRET_KEY');
        }

        return hash('sha256', $rawKey, true);
    }
}
