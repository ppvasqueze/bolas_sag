<?

require_once('Conecciones/Conecciones.php');
$dbconn = new Conecciones();
$DB=$dbconn->db_local_v1;
$status=$DB->Conectar("bolas_sag");
$DB;

$sql = "update archivos_graficos set status = 1 where status = 0 and idbola = 3 and vector = 'posicion' order by fecha, hora limit 1;";
$DB->consultaSQL($sql, false);

$sql = "Select archivo from archivos_graficos where status = 1 and idbola = 3 and vector = 'posicion'";
$resultado=$DB->consultaSQL($sql, true);

$sql = "update archivos_graficos set status = 2 where status = 1 and idbola = 3 and vector = 'posicion'";
$DB->consultaSQL($sql, false);
		
echo $resultado[0][0];

?>
