#!/usr/bin/env php
<?


class workerThread extends Thread {
public function __construct($i){
  $this->i=$i;
}

public function run(){
  while(true){
   echo $this->i;
   sleep(1);
  }
}
}

for($i=0;$i<50;$i++){
$workers[$i]=new workerThread($i);
$workers[$i]->start();
}



/*
include_once("AbstractObservable.php");
include_once("broadcastwebsocket.php");

class Test extends Observable {

	public __construct(){
		$i = 0;
		while ($i < 10){
			enviarProceso($i);
			$i++;
			sleep(2);
		}
	}

	public enviarProceso($dato){
		$this->notify($dato);
	}
}

//Lanzar el WebSocket Server
$brdcast = new broadcastServer("0.0.0.0","9000");
try {
  $brdcast->run();
}
catch (Exception $e) {
  $brdcast->stdout($e->getMessage());
}



//Ejecutar la clase
$t = new Test();
*/

?>