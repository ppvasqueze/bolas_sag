<?

class socketClient {

	private $service_port = "";
	private $address = "";
	private $socket = "";
	private $success = false;
	
	public function __construct($server, $port){
		echo "creando el cliente socket \n";
		$this->service_port = $port;
		$this->address = $server;
		$this->success = $this->connect();
	}

	public function send($msg){
		socket_write($this->socket, $msg, strlen($msg));
		/*
		while ($out = socket_read($socket, 2048)) {
		    echo $out;
		}
		*/
	}
	
	public function close(){
		socket_close($this->socket);
	}
	
	public function isConnected(){
		return $this->success;
	}
	
	public function connect(){
		$connected = true;
		/* Create a TCP/IP socket. */
		echo "iniciando connect\n";
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket === false) {
			$connected = false;
			echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
		}
		$result = socket_connect($this->socket, $this->address, $this->service_port);
		if ($result === false) {
			$connected = false;
			echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($this->socket)) . "\n";
		}
		return $connected;
	}
	
}	
	
