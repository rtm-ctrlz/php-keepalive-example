<?php

declare(strict_types=1);

require_once __DIR__ . '/ssl.php';

$host = '127.0.0.1';
$port = 9898;

$stream = stream_socket_client(
    'ssl://' . $host . ':' . $port,
    $errno,
    $errstr,
    1.1,
    STREAM_CLIENT_CONNECT,
    stream_context_create(
        [
            'ssl' => [
                'peer_name'         => EX_SSL_CA_CERT_CN,
                'verify_peer'       => true,
                'verify_peer_name'  => true,
                'allow_self_signed' => true,
                'cafile'            => EX_SSL_CA_CERT_PATH,
            ],
        ]
    )
);
if ($stream === false) {
    throw new Error('Failed to connect to ssl://' . $host . ':' . $port . ': ' . $errstr . ' (' . $errno . ')');
}

while (!feof($stream)) {
    $data = fgets($stream);
    if ($data === false || $data === '') {
        break;
    }
    $data = trim($data);
    error_log(' => ' . $data);
}