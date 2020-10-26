# Purpose

To show how to set up and use TCP Keep-Alive with php.

Most PHP HowTo-s and examples over internet have problems:

 - only turn on TCP Keep-Alive, with letting Keep-Alive parameters by default
 - doing it wrong way
 
# How TCP Keep-Alive works

There are 4 options for Keep-Alive:

 - `SOL_SOCKET - SO_KEEPALIVE` - enables TCP Keep-Alive
 - `SOL_TCP - TCP_KEEPIDLE` - timeout between data-packets, when hit Keep-Alive probe should be sent
 - `SOL_TCP - TCP_KEEPINTVL` - timout between sending probes
 - `SOL_TCP - TCP_KEEPCNT` - number of "TCP Keep-Alive" packets without an answer ("TCP Keep-Alive ACK") to consider socket as "broken pipe" 

Most systems (OSes) have following defaults:

| Option | Default value | Description |
|-|:-:|-|
| SO_KEEPALIVE | 0 | TCP Keep-Alive is disabled |
| TCP_KEEPIDLE | 7200 | 2 hours |
| TCP_KEEPINTVL | 75 | 75 seconds |
| TCP_KEEPCNT | 8 | |


# Gotchas

### Option numbers

Unfortunately PHP doesn't have `TCP_KEEPIDLE`, `TCP_KEEPINTVL` and `TCP_KEEPCNT` constants.
Since most OSes can handle TCP Keep-Alive it is the matter of time to find correct numbers.

#### Found values
| Option | Linux | Darwin |
|-|:-:|:-:|
|TCP_KEEPIDLE| `4` | `16` |
|TCP_KEEPINTVL| `5` | `257` |
|TCP_KEEPCNT| `6` | `258` | 

Linux: `man 7 tcp` + `tcp.h` include
Darwin: `tcp.h` include


### Streams

Streams is a very powerful way to handle tcp/tcp+ssl connections, but there no way to set socket option
(setsockopt) for a stream.

`sockets` extension have function `socket_import_stream` which helps to get a socket for a given stream.

### SSL/TLS Streams

There is no way to get socket of a stream (`socket_import_stream`) for established connection.

Instead of trying to "get inside into tcp+ssl stream", we can establish TCP connection,
set up TCP Keep-Alive and enable SSL/TLS encryption.

# Examples

## General

```php
    socket_set_option($socket, SOL_SOCKET, SO_KEEPALIVE, 1);

    socket_set_option($socket, SOL_TCP, TCP_KEEPIDLE,  $timeout);
    socket_set_option($socket, SOL_TCP, TCP_KEEPINTVL, $interval);
    socket_set_option($socket, SOL_TCP, TCP_KEEPCNT,   $probes);
```

Note: this example can't work - constants are missing.

Predefined constants and some OS-detection could be found at [keepalive.php](keepalive.php). 

For all examples you can use tcpdump/wireshark to see TCP Keep-Alive packets, even for SSL/TLS connections.

All examples use one `ip:port` - `127.0.0.1:9898`.

## Plain TCP

Directory [tcp](tcp) contains:

 - [socket-client](tcp/socket-client.php) - echo client, just `socket_*` functions
 - [stream-client](tcp/stream-client.php) - echo client, `stream_socket_client` + `socket_import_stream`
 - [stream-server](tcp/stream-server.php) - echo server, `stream_socket_server` + `socket_import_stream`

As a common server you can use netcat: `nc -kl 9898`

As a common client you also can use netcat: `nc 127.0.0.1 9898`

## SSL/TLS

Directory [ssl](ssl) contains:
 - [dummy-client](ssl/dummy-client.php) - regular `stream_*` echo client
 - [dummy-server](ssl/dummy-server.php) - regular `stream_*` echo server
 - [keepalive-client](ssl/keepalive-client.php) - keep-alive `stream_*` echo client
 - [keepalive-server](ssl/keepalive-server.php) - keep-alive `stream_*` echo server
 - [ssl](ssl/ssl.php) - just generator for CA-certificate
 
As a common client (without peer validation) you also can use openssl: `openssl s_client -connect 127.0.0.1:9898`