<?php

declare(strict_types=1);
require_once __DIR__ . '/../keepalive.php';

$host = '127.0.0.1';
$port = 9898;


$server = stream_socket_server(
    'tcp://' . $host . ':' . $port,
    $errno,
    $errstr,
    \STREAM_SERVER_BIND | \STREAM_SERVER_LISTEN
);
if (!$server) {
    echo "$errstr ($errno)", PHP_EOL;
} else {
    error_log('Waiting for new connection...');
    while (true) {
        while ($conn = @stream_socket_accept($server, 5)) {
            $socket = socket_import_stream($conn);
            keepalive($socket, 10, 1, 2);

            error_log('New connection');
            while (!feof($conn)) {
                $data = fgets($conn);
                error_log(' => ' . $data);
            }
            fclose($conn);
            fclose($server);
            break 2;
        }
    }
}