a a a a a a a a b b b 
<?php  

echo "holaaaaaaaaaaaaaa";

include "php_serial.class.php";  

$serial = new phpSerial;
$serial->deviceSet("/dev/usbmon2");
$serial->confBaudRate(19200);
$serial->confParity("none");
$serial->confCharacterLength(8);
$serial->confStopBits(1);
$serial->confFlowControl("none");
$serial->deviceOpen();  

$read = $serial->readPort();

if(isset($read))
echo "entroooooooooooooo";
{
   //while(1)
   //{
       $read = $serial->readPort();
       print_r(" (size ".strlen($read). " ) ");
       for($i = 0; $i < strlen($read); $i++)
       { 
          echo ord($read[$i])." ";
       }
       print_r("\n");
       sleep(1);
  //}
}  

?>  q q q quit
