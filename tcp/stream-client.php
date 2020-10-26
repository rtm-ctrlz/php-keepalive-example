<?php

declare(strict_types=1);

require_once __DIR__ . '/../keepalive.php';
$host = '127.0.0.1';
$port = 9898;

$stream = @stream_socket_client('tcp://' . $host . ':' . $port);
if ($stream === false) {
    throw new RuntimeException("Connection to tcp://{$host}:{$port} failed");
}
// getting socket for a stream
$socket = socket_import_stream($stream);

// setting TCP Keep-Alive after connection
keepalive($socket, 5, 1, 2);

while (!feof($stream)) {
    $data = fgets($stream);
    if ($data === false || $data === '') {
        break;
    }
    $data = trim($data);
    switch ($data) {
        case 'on':
            error_log(' !! Enabling TCP Keep-Alive');
            keepalive($socket, 5, 1, 2);
            break;
        case 'off':
            error_log(' !! Disabling TCP Keep-Alive');
            keepalive_disable($socket);
            break;
        default:
            error_log(' => ' . $data);
    }
}

fclose($stream);
