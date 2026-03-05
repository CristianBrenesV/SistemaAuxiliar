<?php

function verificarClaveAESGCM(string $inputPassword, string $claveCifrada, string $tag, string $nonce, string $key): bool
{
    $plaintext = '';
    $ok = openssl_decrypt(
        $claveCifrada,
        'aes-256-gcm',
        $key,
        OPENSSL_RAW_DATA,
        $nonce,
        $tag,
        $inputPassword
    );

    return $ok !== false;
}
