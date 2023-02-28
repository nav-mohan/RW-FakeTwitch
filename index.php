<?php
	set_time_limit(0);//wait indefinitely for a client-connection
	
	define("HOST_NAME", "tcp://0.0.0.0");
	define("PORT",9001);
	define("BUFFER_READ_SIZE",2**10);
	$null = NULL;

    require_once("class-ChatMessageParser.php");
    require_once("class-ChatLog.php");

    $chat_handler = new ChatMessageParser();
    $chat_log = new ChatLog();

    $server_socket = socket_create(AF_INET,SOCK_STREAM,SOL_TCP);
    socket_set_option($server_socket,SOL_SOCKET,SO_REUSEADDR,1);

    socket_bind($server_socket,0,PORT);
    socket_listen($server_socket);
    echo("Listening\n");
    $client_sockets = array($server_socket);

    while(true){
        $client_newSockets = $client_sockets;
        socket_select($client_newSockets,$null,$null,0,10); //--> blocks until state change
        // event happened on read-ready $client_newSockets
        if(in_array($server_socket,$client_newSockets)){//what does it mean for $server_socket to be in $client_newSockets?
            $new_socket = socket_accept($server_socket);
            $client_sockets[] = $new_socket;

            $data = socket_read($new_socket,BUFFER_READ_SIZE);
            $client_header = $chat_msg_parser->parse_header($data);
            $client_ip_address = $client_header['X-Real-IP'];
            $secKey = $client_header['Sec-WebSocket-Key'];
            $secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
    
        }
    }
?>
