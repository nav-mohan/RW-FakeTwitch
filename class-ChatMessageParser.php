<?php

/**
 * this class does not deal with the sockets directly. 
 * it performs the encryption,decryption,parsing,etc 
 **/

class ChatMessageParser{
    function decrypt($socket_data) {
		$length = ord($socket_data[1]) & 127;
		if($length == 126) {
			$masks = substr($socket_data, 4, 4);
			$data = substr($socket_data, 8);
		}
		elseif($length == 127) {
			$masks = substr($socket_data, 10, 4);
			$data = substr($socket_data, 14);
		}
		else {
			$masks = substr($socket_data, 2, 4);
			$data = substr($socket_data, 6);
		}
		$socket_data = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$socket_data .= $data[$i] ^ $masks[$i%4];
		}
		return $socket_data;
	}

	function encrypt($socket_data) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($socket_data);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
		elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
		elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
		
		return $header.$socket_data;
	}

    function parse_header($received_header){
        $header = array();
		$lines = preg_split("/\r\n/", $received_header);
		foreach($lines as $line)
		{
			$line = chop($line);
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$header[$matches[1]] = $matches[2];
			}
		}
		return $header;//this contains the actual IP address of the client in the X-Real-IP
    }

    function response_header($secure_ws_key,$host_name,$port){
        $secAccept = base64_encode(pack('H*', sha1($secure_ws_key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
		$buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
		"Upgrade: websocket\r\n" .
		"Connection: Upgrade\r\n" .
		"WebSocket-Origin: $host_name\r\n" .
		"WebSocket-Location: ws://$host_name:$port/demo/shout.php\r\n".
		"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
    }


}