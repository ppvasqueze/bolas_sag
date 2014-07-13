<?
class create_3d_graphs {
	public $num_datos_muestra = 6;
	public $vector_muestra = 'aceleracion'; // aceleracion o giro
	public $var_prefijo_archivo = '';
	public $ruta = '/var/www/bolas_sag/graficos/';
	//public $ruta = '/home/juca/desarrollo/matrix/bolas/version_final/graficos/';
	public $db;
	public $width = 1024;
	public $height = 768;
	private $mLimites = "";
	private $mLimiteVector = "";
	private $mHorasGrafs = "";
	
	public function __construct(){
		$PATH_BASE='/var/www/bolas_sag/Conecciones/';
		require_once('/var/www/bolas_sag/Conecciones/Conecciones.php');
		$dbconn = new Conecciones();
		$DB=$dbconn->db_local_v1;
		$status=$DB->Conectar("bolas_sag");
		$this->db = $DB;
		
                $this->params_lectura["inicio"] = 1;
                $this->params_lectura["ultima_muestra"] = 0;
                $this->params_lectura["muestra_cantidad"] = 10;
                $this->params_lectura["muestra_factor"] = 5;
		
		$this->mHorasGrafs["hora_ini"] = "";
		$this->mHorasGrafs["hora_ter"] = "";
		//$this->prefijo_archivo($this->vector_muestra);
	}
	

	private function get_pernos_bolas(){
		$sql = "Select idperno,idbola from parametros_mem where status = 2 and idbola > 0 and idbola < 10 group by idperno,idbola;";
		$resultado=$this->db->consultaSQL($sql, true);
		return $resultado;
	}

	public function prefijo_archivo($valor = null){
		if ( is_null($valor)) {
			return $this->var_prefijo_archivo;
		}else{
			$this->var_prefijo_archivo = $valor;
		}
	}
	
	public function crear_grafico(){
		$mPernosBolas = $this->get_pernos_bolas();
		if (is_array($mPernosBolas)){
			
			$fecha = date("Ymd");
			$hora = date("Hisu");
			$sufix_file = $fecha . $hora;
			for ($i=0; $i < count($mPernosBolas); $i++){
				$idperno = $mPernosBolas[$i][0];
				$idbola = $mPernosBolas[$i][1];
				$this->get_limites();


				// CREANDO GRAFICO DE ACELERACION
				$vector = "original_aceleracion";
				$data = $this->get_data($idperno, $idbola, $vector);
				if (is_array($data) && count($data) > 0){
					$this->set_vector_limits($vector);
					$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
					$cfgfile = $this->create_cfg_file($vector, $this->ruta . $filename);
					$this->create_data_file($data, $vector);
					exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
					$this->informar_archivo($filename, $fecha, $hora, $vector, $idbola);
				}	
				
				// CREANDO GRAFICO DE GIRO
				$vector = "original_giro";
				$data = $this->get_data($idperno, $idbola, $vector);
				if (is_array($data) && count($data) > 0){
					//print "graficando p \n";
					$this->set_vector_limits($vector);
					$filename = $vector . "_" . $idperno . "_" . $idbola . "_" . $sufix_file . ".png"; 
					$cfgfile = $this->create_cfg_file($vector, $this->ruta . $filename);
					$this->create_data_file($data, $vector);
					exec("/usr/bin/gnuplot " . $cfgfile, $msalida);
					$this->informar_archivo($filename, $fecha, $hora, $vector, $idbola);
				}
			
			}
		}
	
		return true;
	}
	
	private function get_limites(){
		$sql = "Select MIN(acelx), MAX(acelx), MIN(acely), MAX(acely), MIN(acelz), MAX(acelz), MIN(girox), MAX(girox), MIN(giroy), MAX(giroy), MIN(giroz), MAX(giroz) from parametros_mem";
		$resultado=$this->db->consultaSQL($sql, true);
		if (is_array($resultado)){
			$this->mLimites["minAx"] = $resultado[0][0];
			$this->mLimites["maxAx"] = $resultado[0][1];
			$this->mLimites["minAy"] = $resultado[0][2];
			$this->mLimites["maxAy"] = $resultado[0][3];
			$this->mLimites["minAz"] = $resultado[0][4];
			$this->mLimites["maxAz"] = $resultado[0][5];
			$this->mLimites["minGx"] = $resultado[0][6];
			$this->mLimites["maxGx"] = $resultado[0][7];
			$this->mLimites["minGy"] = $resultado[0][8];
			$this->mLimites["maxGy"] = $resultado[0][9];
			$this->mLimites["minGz"] = $resultado[0][10];
			$this->mLimites["maxGz"] = $resultado[0][11];
			//print_r($this->mLimites);
		}
	}

	private function set_vector_limits($vector){
		switch ($vector){
			case "original_aceleracion":
				$this->mLimiteVector["minX"] = $this->mLimites["minAx"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxAx"];
				$this->mLimiteVector["minY"] = $this->mLimites["minAy"];
				$this->mLimiteVector["maxY"] = $this->mLimites["maxAy"];
				$this->mLimiteVector["minZ"] = $this->mLimites["minAz"];
				$this->mLimiteVector["maxZ"] = $this->mLimites["maxAz"];
			break;	
			case "original_giro":
				$this->mLimiteVector["minX"] = $this->mLimites["minGx"];
				$this->mLimiteVector["maxX"] = $this->mLimites["maxGx"];
				$this->mLimiteVector["minY"] = $this->mLimites["minGy"];
				$this->mLimiteVector["maxY"] = $this->mLimites["maxGy"];
				$this->mLimiteVector["minZ"] = $this->mLimites["minGz"];
				$this->mLimiteVector["maxZ"] = $this->mLimites["maxGz"];				
			break;	
		}
	}


	private function informar_archivo($archivo, $fecha, $hora, $vector, $idbola){
		$sqlq = "INSERT INTO archivos_graficos (ruta, archivo, status, fecha, hora, vector, idbola) VALUES (";
		$sqlq .= "'" . $this->ruta . "'";
		$sqlq .= ",'" . $archivo . "'";
		$sqlq .= ",0";
		$sqlq .= ",'" . $fecha . "'";
		$sqlq .= ",'" . $hora . "'";
		$sqlq .= ",'" . $vector . "'";
		$sqlq .= "," . $idbola . ")";
		$this->db->consultaSQL($sqlq, false);
	}


	private function get_data($idperno, $idbola, $vector){
		$graficar = false;
		$tiene_params = false;
		$resultado="";
		switch ($vector){
			case "original_aceleracion":
				$tiene_params = $this->get_param_proceso("aoi,aon", $idbola);
				$sql = "select idregistro, acelx, acely, acelz from parametros_mem where idregistro >= " . $this->params_lectura["inicio"];
				$sql .= " and acelx <> 0 and acely <> 0 and acelz <> 0 ";
				$sql .= " and idbola = " . $idbola . " ";
				$sql .= " order by idregistro ";
				$sql .= " limit " . $this->params_lectura["muestra_cantidad"];
			break;
			case "original_giro":
				$tiene_params = $this->get_param_proceso("goi,gon", $idbola);
				$sql = "select idregistro, girox, giroy, giroz from parametros_mem where idregistro >= " . $this->params_lectura["inicio"];
				$sql .= " and girox <> 0 and giroy <> 0 and giroz <> 0 ";
				$sql .= " and idbola = " . $idbola . " ";
				$sql .= " order by idregistro ";
				$sql .= " limit " . $this->params_lectura["muestra_cantidad"];
			break;
		}
		if ($tiene_params == true){
			//echo $sql . "\n";
			$resultado=$this->db->consultaSQL($sql, true);
		}	
		
		if (is_array($resultado) && count($resultado) > 0){
			$this->set_nuevo_param_proceso($idbola, $vector, $resultado);
			if ($resultado[0][0] != $this->params_lectura["inicio"]  ||  count($resultado) != $this->params_lectura["ultima_muestra"] ){
				$graficar = $resultado;
			}	
		}
		
		return $graficar;
	}


	private function get_param_proceso($campos, $idbola){
		$tiene_data = false;
		$sql = "select " . $campos . ", mn, mf from graficados where id_bola = " . $idbola;
		$graficados=$this->db->consultaSQL($sql, true);
		if (is_array($graficados) && count($graficados) > 0){
			$this->params_lectura["inicio"] = $graficados[0][0];
			$this->params_lectura["ultima_muestra"] = $graficados[0][1];
			$this->params_lectura["muestra_cantidad"] = $graficados[0][2];
			$this->params_lectura["muestra_factor"] = $graficados[0][3];
			$tiene_data = true;
		}	
		
		return $tiene_data;
	}


	private function set_nuevo_param_proceso($idbola, $vector, $data){
		if (count($data) == $this->params_lectura["muestra_cantidad"] ){
			$nueva_posicion = $data[ $this->params_lectura["muestra_factor"] -1 ][0];
		}else{
			$nueva_posicion = $data[0][0];
		}
		$this->params_lectura["inicio"] = $nueva_posicion;
		switch ($vector){
			case "original_aceleracion":
				$sql = "update graficados set aoi =  " . $nueva_posicion . ", aon = " . count($data);
				$sql .= " Where id_bola = " . $idbola; 
			break;
			case "original_giro":
				$sql = "update graficados set goi =  " . $nueva_posicion . " ,gon = " . count($data);
				$sql .= " Where id_bola = " . $idbola; 
			break;
		}
		//echo $sql . "\n";
		$this->db->consultaSQL($sql, false);		
	}


/*
	private function get_data($idperno, $idbola, $vector){
	
		$sql = "update parametros_mem set status = 1 where status = 0 and idperno = " . $idperno;
		$sql .= " and idbola = " . $idbola  . " order by timetick limit " . $this->num_datos_muestra . ";";
		$this->db->consultaSQL($sql, false);
		
		$campos = "";
		switch ($vector){
			case  'aceleracion':
				$campos = "acelx, acely, acelz";
			break;
			case 'giro':
				$campos = "girox, giroy, giroz";
			break;
		}
		
		$campos = "acelx, acely, acelz, girox, giroy, giroz";

		$sql = "Select " . $campos . " from parametros_mem where status = 1 order by timetick";
		$resultado=$this->db->consultaSQL($sql, true);

		$sql = "update parametros_mem set status = 2 where status = 1;";
		$this->db->consultaSQL($sql, false);

		return $resultado;
	}
*/



	private function create_cfg_file($vector, $file){
		$nl = "\n";
		//$data  = "set terminal png transparent nocrop enhanced font sanz 10 size " . $this->width . "," . $this->height . $nl;
		$data  = "set terminal png nocrop enhanced font \"/usr/share/fonts/truetype/msttcorefonts/Arial.ttf\" 18 size " . $this->width . "," . $this->height . $nl;
		$data .= "set output '" . $file . "'" . $nl;
		$data .= "set dummy u,v" . $nl;
		//$data .= "set key inside right top vertical Right noreverse enhanced autotitles box linetype -1 linewidth 1.000" . $nl;
		$data .= "unset key" . $nl;
		$data .= "unset parametric" . $nl;
		$data .= "unset contour" . $nl;
		$data .= "set pointsize 0.9" . $nl;
		//$data .= "set surface" . $nl;
		//$data .= "set hidden3d offset 1 trianglepattern 3 undefined 1 altdiagonal bentover" . $nl;
		$data .= "set title \"Grafico de " . $vector . " bolas\"" . $nl;
		$data .= "set xlabel \"Vector X\"" . $nl;
		$data .= "set xrange [" . $this->mLimiteVector["minX"] . ":" . $this->mLimiteVector["maxX"] . "]" . $nl;
		$data .= "set yrange [" . $this->mLimiteVector["minY"] . ":" . $this->mLimiteVector["maxY"] . "]" . $nl;
		$data .= "set zrange [" . $this->mLimiteVector["minZ"] . ":" . $this->mLimiteVector["maxZ"] . "]" . $nl;
		/*
		$data .= "set xrange [-512:512]" . $nl;
		$data .= "set yrange [-512:512]" . $nl;
		$data .= "set zrange [-512:512]" . $nl;
		*/
		$data .= "splot \"" . $this->ruta . "graphic_" . $vector . ".dat" . "\" using 1:2:3  with linespoints" . $nl;
		
		$filename = $this->ruta . "graphic_" . $vector . ".cfg";
		$this->create_file($filename, $data);
		return $filename;
	}
	
	private function create_data_file($mdata, $vector){
		$fd = "\t";
		$rd = "\n";
		$data = "";
		if (is_array($mdata)){
			if ($vector == "original_aceleracion"){
				for ($i=0; $i < count($mdata); $i++){
					$data .= $mdata[$i][1] . $fd . $mdata[$i][2] . $fd . $mdata[$i][3] . $rd;
				}
			}
			if ($vector == "original_giro"){
                                for ($i=0; $i < count($mdata); $i++){
                                        $data .= $mdata[$i][1] . $fd . $mdata[$i][2] . $fd . $mdata[$i][3] . $rd;
                                }
			}
			$this->create_file($this->ruta . "graphic_" . $vector . ".dat", $data);	
		}
	}


	private function create_file($file, $data){
		$fp = fopen($file , "w");
		fwrite($fp , $data);
		fclose($fp);
	}
	
}

?>
