<?php

declare(strict_types=1);

/**
 * Enabling TCP Keep-Alive on a socket and tuning parameters
 *
 * Parameters
 *  - time:     timeout from last data-packet (ACKs are not data-packets) in socket
 *              when system hits timeout TCP Keep-Alive mechanism starts to work
 *              unit: seconds
 *              default for most systems: 7200s
 *
 *  - interval: timout between sending probes
 *              unit: seconds
 *              default for most systems: 75
 *
 *  - probes:   number of "TCP Keep-Alive" packets without an answer ("TCP Keep-Alive ACK")
 *              to consider socket as "broken pipe"
 *              default for most systems: 9
 *
 * @param resource $socket
 * @param int      $time
 * @param int      $interval
 * @param int      $probes
 *
 * @return void
 */
function keepalive($socket, int $time, int $interval, int $probes): void
{
    // couple checks
    check_php();

    // getting option ids
    $TCP_OPTIONS = get_tcp_option_ids();

    // dumping values
    error_log(
        <<<MSG
Setting TCP Keep-Alive with options:
TCP_KEEPIDLE ({$TCP_OPTIONS['TCP_KEEPIDLE']}): {$time}
TCP_KEEPINTVL ({$TCP_OPTIONS['TCP_KEEPINTVL']}): {$interval}
TCP_KEEPCNT ({$TCP_OPTIONS['TCP_KEEPCNT']}): {$probes}
MSG
    );

    // enabling Keep-Alive for a socket
    socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);

    // setting TCP Keep-Alive parameters
    socket_set_option($socket, SOL_TCP, $TCP_OPTIONS['TCP_KEEPIDLE'], $time);
    socket_set_option($socket, SOL_TCP, $TCP_OPTIONS['TCP_KEEPINTVL'], $interval);
    socket_set_option($socket, SOL_TCP, $TCP_OPTIONS['TCP_KEEPCNT'], $probes);
}

function keepalive_disable($socket): void
{
    socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 0);
}

/**
 * Check PHP version and ext-socket
 *
 * @throws Error
 */
function check_php(): void
{
    if (PHP_VERSION_ID < 70200 || !defined('PHP_OS_FAMILY')) {
        // generally php below 7.2 supports TCP Keep-Alive
        // but OS-detection will be a bit more complicate
        throw new Error('Unsupported php version.');
    }

    // we need socket_set_option function to set socket/tcp options
    if (!function_exists('socket_set_option')) {
        throw new Error('Missing socket extension.');
    }
}

/**
 * Getting correct TCP option ids for current OS
 *
 * @return array<string, int>
 * @throws Error
 */
function get_tcp_option_ids(): array
{
    // Magic numbers (socket option ids)
    // PHP doesn't have these constants =(
    $TCP_OPTION_IDS = [
        'Linux'  => [
            'TCP_KEEPIDLE'  => 4,
            'TCP_KEEPINTVL' => 5,
            'TCP_KEEPCNT'   => 6,
        ],
        'Darwin' => [
            'TCP_KEEPIDLE'  => 0x10,
            'TCP_KEEPINTVL' => 0x101,
            'TCP_KEEPCNT'   => 0x102,
        ],
    ];
    if (!array_key_exists(PHP_OS_FAMILY, $TCP_OPTION_IDS)) {
        throw new Error('Unsupported OS');
    }
    return $TCP_OPTION_IDS[PHP_OS_FAMILY];
}