<?php
    include_once("/var/www/fm949.ca/wp-load.php");
    require_once('/var/www/fm949.ca/wp-admin/includes/upgrade.php' );
    
    global $wpdb;
    
    class ChatLog{
        private $db_handle;
        private $table_name = 'rw_chatlogs';
        private $chats_per_page = 10;
        function __construct(){
            global $wpdb;
            $this->db_handle = $wpdb;
            $this->create_table();
        }

        function get_chat_history($page_number){
            $range_min = ($page_number-1)*$chats_per_page;
            $range_max = ($page_number)*$chats_per_page;
            $SQL = "SELECT wp_user_id,message,time FROM $this->table_name ORDER BY time DESC LIMIT $range_min,$range_max";
            $result = $this->db_handle->get_results($SQL);
            $last_error = $this->db_handle->last_error;
            $response = array('result'=>$result,'error'=>$last_error);
			return($response);
        }

        function create_record($chat){
            $chat = esc_sql($chat);
            $socket_id = $chat['socket_id'];
            $wp_user_id = $chat['wp_user_id'];
            $message = $chat['message'];
            $time = $chat['time'];

            $SQL = "INSERT INTO $this->table_name (
				socket_id,
				wp_user_id,
				message,
				time
				) VALUES (
				'$socket_id',
				'$wp_user_id',
				'$message',
				'$time'
				)";
            $count = $this->db_handle->query($SQL);
            $last_error = $this->db_handle->last_error;
            $response = array('count'=>$count,'error'=>$last_error);
            return $response;

        }

        function create_table():void{
            //* Create the chat table
            $SQL = "CREATE TABLE $this->table_name (
            message_id INTEGER NOT NULL AUTO_INCREMENT,
            socket_id VARCHAR(255) NOT NULL,
            ip_address VARCHAR(255) NOT NULL,
            wp_user_id BIGINT(20) UNSIGNED NOT NULL,
            message TEXT NOT NULL,
            time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (message_id),
            FOREIGN KEY (wp_user_id) REFERENCES wp_users(ID)
            );";
            // dbDelta( $SQL );
            maybe_create_table( $this->table_name,$SQL );
        }
}

$chat_log = new ChatLog();
$chat = array(
    'socket_id'=>'ksdhf9823lnos8dufosd',
    'wp_user_id'=>'1',
    'message'=>'HELLO WORLDL! this place is going to shit',
    'time'=>'hahahah',
);
$response = $chat_log->create_record($chat);
print_r($response);
;?>