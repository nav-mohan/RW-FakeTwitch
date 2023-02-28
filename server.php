<?php
define('HOST_NAME',"localhost"); 
define('PORT',"9001");
$null = NULL;

require_once("class.chathandler.php");
$chatHandler = new ChatHandler();

$server_socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($server_socket, SOL_SOCKET, SO_REUSEADDR, 1);

socket_bind($server_socket, 0, PORT);
socket_listen($server_socket);
echo("listening\n");
$client_sockets = array($server_socket);
while (true) {
	$client_newSockets = $client_sockets;
	socket_select($client_newSockets, $null, $null, 0, 10);//<--- this is blocking
	
	if (in_array($server_socket, $client_newSockets)) {
		$new_socket = socket_accept($server_socket);
		$client_sockets[] = $new_socket;
		
		$header = socket_read($new_socket, 1024);
		$client_ip_address=$chatHandler->doHandshake($header, $new_socket, HOST_NAME, PORT);
		
		$connectionACK = $chatHandler->newConnectionACK($client_ip_address);
		
		$chatHandler->send($connectionACK);
		
		$newSocket_index = array_search($server_socket, $client_newSockets);
		unset($client_newSockets[$newSocket_index]);
	}
	
	foreach ($client_newSockets as $client_newSocketsResource) {	
		while(socket_recv($client_newSocketsResource, $socketData, 1024, 0) >= 1){
			$socketMessage = $chatHandler->unseal($socketData);
			$messageObj = json_decode($socketMessage);
			
			$chat_box_message = @$chatHandler->createChatBoxMessage($messageObj->chat_user, $messageObj->chat_message);
			$chatHandler->send($chat_box_message);
			break 2;
		}
		
		$socketData = @socket_read($client_newSocketsResource, 1024, PHP_NORMAL_READ);
		if ($socketData === false) { 
			socket_getpeername($client_newSocketsResource, $client_ip_address);
			$connectionACK = $chatHandler->connectionDisconnectACK($client_ip_address);
			$chatHandler->send($connectionACK);
			$newSocket_index = array_search($client_newSocketsResource, $client_sockets);
			unset($client_sockets[$newSocket_index]);			
		}
	}
}
socket_close($server_socket);
?>