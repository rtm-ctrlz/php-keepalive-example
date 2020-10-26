<?php

declare(strict_types=1);

require_once __DIR__ . '/../keepalive.php';
$host = '127.0.0.1';
$port = 9898;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);

// TCP Keep-Alive can be enabled for created and not connected socket
$keepalive_is_set = false;
if (random_int(0, 1) === 1) {
    keepalive($socket, 5, 1, 2);
    $keepalive_is_set = true;
}

if (!@socket_connect($socket, '127.0.0.1', 9898)) {
    throw new RuntimeException("Connection to tcp://{$host}:{$port} failed");
}


// TCP Keep-Alive can be enabled for connected socket
if (!$keepalive_is_set) {
    keepalive($socket, 5, 1, 2);
}

while (($data = socket_read($socket, 10)) !== false && $data !== '') {
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
socket_close($socket);