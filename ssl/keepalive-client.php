<?php

declare(strict_types=1);

require_once __DIR__ . '/ssl.php';
require_once __DIR__ . '/../keepalive.php';

$host = '127.0.0.1';
$port = 9898;

// Look closer - we create "tcp" stream instead of ssl
$stream = stream_socket_client(
    'tcp://' . $host . ':' . $port,
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
    throw new Error('Failed to connect to tcp://' . $host . ':' . $port . ': ' . $errstr . ' (' . $errno . ')');
}

// Enabling TCP Keep-Alive just like for plain tcp socket
$socket = socket_import_stream($stream);
keepalive($socket, 5, 1, 2);

// Here a bit of "magic" - we enabling SSL-layer for existing stream
if (!stream_socket_enable_crypto($stream, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
    throw new Error('Failed to connect to ssl://' . $host . ':' . $port);
}

while (!feof($stream)) {
    $data = fgets($stream);
    if ($data === false || $data === '') {
        break;
    }
    $data = trim($data);
    error_log(' => ' . $data);
}