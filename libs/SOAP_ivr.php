<?php
require_once "../../../nusoap/lib/nusoap.php";
include_once "../../../libs/misc.lib.php";
include_once "../../../libs/paloSantoDB.class.php";

include_once "../configs/default.conf.php";
include_once "paloSantoAdvance_Ivr_List.class.php";

require_once "../../../../../../opt/elastix/messageplus/ivrmanager/model/llamadaModelClass.php";
include_once "JSON.php";




function obtenerListadoIVR($parametros) {

	try{
		//global variables
		// global $arrConf;
		 // throw new Exception('Error deprueba');
		 global $arrConfModule;
		// global $arrLang;
		// global $arrLangModule;
		// $arrConf = array_merge($arrConf,$arrConfModule);
		// $arrLang = array_merge($arrLang,$arrLangModule);

		$arrConf = $arrConfModule;
		$oJson = new Services_JSON();


		
		$pDB = new paloDB($arrConf['cadena_dsn']);
	    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
	        throw new Exception($pDB->errMsg);
	    }

	    $saliente = $parametros['todos'] == 1? -1 : 1;

		$pAdvance_Ivr_List = new paloSantoAdvance_Ivr_List($pDB);
		$resultado = $pAdvance_Ivr_List->setSaliente($saliente)->setActivo("1")->getAdvance_Ivr_List();
		
		// writeLOG('advance_ivr', $oJson->encode($resultado));

		$ivrs = array();

		foreach($resultado as $fila){
			// writeLOG('advance_ivr', $fila["id"]);
		    $codigo = $fila["id"];
		    settype($codigo, "integer");
		    $nombre = $fila["nombre"];
		 	
			array_push($ivrs, array(codigo => $codigo, nombre => $nombre));   
		}
		

		return $ivrs;

		// print "<pre>";
		// print_r($ivrs);
		// print "</pre>";

		
	}catch(Exception $ex){
		throw new SoapFault("400", $ex->getMessage());
		// return 'Error: '.$ex->getMessage();
		// throw new  $ex;
		

	}
}


function realizarLlamada($id, $trunk, $destination, $ivr, $maxRetries = 0, $retryTime = 300, $account = ""){
	try{

		//global variables
		//global $arrConf;
		 
		 global $arrConfModule;
		// global $arrLang;
		// global $arrLangModule;
		// $arrConf = array_merge($arrConf,$arrConfModule);
		// $arrLang = array_merge($arrLang,$arrLangModule);

		$arrConf = $arrConfModule;

		// writeLOG('advance_ivr','x');
		$oJson = new Services_JSON();

		writeLOG('advance_ivr','id - '. $id);
		writeLOG('advance_ivr','trunk - '. $trunk);
		writeLOG('advance_ivr','destination - '. $destination);
		writeLOG('advance_ivr','ivr - '. $ivr);


		// if($id == '' || !isset($id))
		// 	throw new Exception("code not defined");
		// if($trunk == '' || !isset($trunk))
		// 	throw new Exception("trunk not defined");
		// if($destination == '' || !isset($destination))
		// 	throw new Exception("destination not defined");
		// if($ivr == '' || !isset($ivr))
		// 	throw new Exception("ivr not defined");

		$result = exec('php /opt/elastix/messageplus/dialer/dialer.php Trunk:'.$trunk.' Destination:'.$destination.' ID:'.$id.' IVR:'.$ivr.'MaxRetries:'.$maxRetries.' WaitTime:'.$retryTime.' Account:'.$account);

		return $result;

	}catch(Exception $ex){
		return "ERROR: ".$ex->getMessage();
	}
}


function obtenerListadoTrocales(){
	try{
		//global variables
		// global $arrConf;
		 // throw new Exception('Error deprueba');
		 global $arrConfModule;
		// global $arrLang;
		// global $arrLangModule;
		// $arrConf = array_merge($arrConf,$arrConfModule);
		// $arrLang = array_merge($arrLang,$arrLangModule);

		$arrConf = $arrConfModule;
		$oJson = new Services_JSON();

		// throw new Exception("Error Processing Request", 1);

		
		$pDB = new paloDB($arrConf['cadena_dsn']);
	    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
	        throw new Exception($pDB->errMsg);
	    }

		$pAdvance_Ivr_List = new paloSantoAdvance_Ivr_List($pDB);
		$resultado = $pAdvance_Ivr_List->getTrunksLsist();
		
		// writeLOG('advance_ivr', $oJson->encode($resultado));

		$troncales = array();

		foreach($resultado as $fila){
			// writeLOG('advance_ivr', $fila["id"]);
		    $codigo = $fila["id"];
		    $nombre = $fila["nombre"];
		    $maximo_canales = $fila["maximo_canales"];

		    settype($codigo, "integer");
		    settype($maximo_canales, "integer");
		 	
			array_push($troncales, array('codigo' => $codigo, 'nombre' => $nombre, 'maximo_canales' => $maximo_canales));   

			// writeLOG('advance_ivr', $fila["id"].' - '.$fila["nombre"]);
		}
		

		return $troncales;

		// print "<pre>";
		// print_r($ivrs);
		// print "</pre>";

		
	}catch(Exception $ex){
		throw new SoapFault("400", $ex->getMessage());
		// return 'Error: '.$ex->getMessage();
		// throw new  $ex;
		

	}
}

function obternerListadoLLamadasPorDia($fecha){
	try{

		$oJson = new Services_JSON();


		$oLlamada = new llamadaModelClass();
		$resultado = $oLlamada->listLlamadasPorFecha($fecha);

		// writeLOG('advance_ivr', $oJson->encode($resultado));

		$llamadas = array();

		// writeLOG('advance_ivr', $resultado["llamada_id"]);
		foreach($resultado as $fila){
			// writeLOG('advance_ivr', $fila["llamada_id"]);

			$llamada_id = $fila["llamada_id"];
			$troncal_id = $fila["troncal_id"];
			$troncal_nombre = $fila["troncal_nombre"];
			$perfil_amd_id = $fila["perfil_amd_id"];
			$perfil_amd_nombre = $fila["perfil_amd_nombre"];
			$llamada_direccion = $fila["llamada_direccion"];
			$llamada_exitosa = $fila["llamada_exitosa"];
			$llamada_estado = $fila["llamada_estado"];
			$llamada_destino = $fila["llamada_destino"];
			$llamada_origen = $fila["llamada_origen"];
			$llamada_fecha_inicio = $fila["llamada_fecha_inicio"];
			$llamada_fecha_fin = $fila["llamada_fecha_fin"];
			$ivr_id = $fila["ivr_id"];
			$ivr_nombre = $fila["ivr_nombre"];
			$paso_fecha_hora = $fila["paso_fecha_hora"];
			$paso_secuencia = $fila["paso_secuencia"];
			$paso_tipo = $fila["paso_tipo"];
			$paso_info = $fila["paso_info"];

			settype($llamada_id,"integer");
			settype($troncal_id,"integer");
			settype($perfil_amd_id,"integer");
			settype($llamada_exitosa,"integer");
			settype($ivr_id,"integer");
			settype($paso_secuencia,"integer"); 	
			

			array_push($llamadas, array(	'llamada_id' => $llamada_id
											,'troncal_id' => $troncal_id
											,'troncal_nombre' => $troncal_nombre
											,'perfil_amd_id' => $perfil_amd_id
											,'perfil_amd_nombre' => $perfil_amd_nombre
											,'llamada_direccion' => $llamada_direccion
											,'llamada_exitosa' => $llamada_exitosa
											,'llamada_estado' => $llamada_estado
											,'llamada_destino' => $llamada_destino
											,'llamada_origen' => $llamada_origen
											,'llamada_fecha_inicio' => $llamada_fecha_inicio
											,'llamada_fecha_fin' => $llamada_fecha_fin
											,'ivr_id' => $ivr_id
											,'ivr_nombre' => $ivr_nombre
											,'paso_fecha_hora' => $paso_fecha_hora
											,'paso_secuencia' => $paso_secuencia
											,'paso_tipo' => $paso_tipo
											,'paso_info' => $paso_info
										));   

			// writeLOG('advance_ivr', $fila["id"].' - '.$fila["nombre"]);
		}
		

		return $llamadas;
	}catch(Exception $ex){
		throw new SoapFault("400", $ex->getMessage());
		// return 'Error: '.$ex->getMessage();
		// throw new  $ex;
		

	}
}

$server = new soap_server();
$server->configureWSDL('ivr', 'urn:ivr');
// $server->handle();

########Estructura y Arreglo para IVR##############
$server->wsdl->addComplexType(
	'ivr',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'codigo' => array('name' => 'codigo', 'type' => 'xsd:int'),
		'nombre' => array('name' => 'nombre', 'type' => 'xsd:string')
	)
);

$server->wsdl->addComplexType(
	'ivrArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array(
			'ref'=>'SOAP-ENC:arrayType'
			,'wsdl:arrayType'=>'tns:ivr[]'
		)
	),
	'tns:ivr'
);

########Estructura y Arreglo para Troncales##############
$server->wsdl->addComplexType(
	'troncal',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'codigo' => array('name' => 'codigo', 'type' => 'xsd:int'),
		'nombre' => array('name' => 'nombre', 'type' => 'xsd:string'),
		'maximo_canales' => array('name' => 'maximo_canales', 'type' => 'xsd:int'),
	)
);

$server->wsdl->addComplexType(
	'troncalesArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array(
			'ref'=>'SOAP-ENC:arrayType'
			,'wsdl:arrayType'=>'tns:troncal[]'
		)
	),
	'tns:troncal'
);

########Estructura y Arreglo para Llamadas + Detalles##############
$server->wsdl->addComplexType(
	'llamada',
	'complexType',
	'struct',
	'all',
	'',
	array(
		'llamada_id' =>array('name' => 'llamada_id', 'type' => 'xsd:int'),
		'troncal_id' =>array('name' => 'troncal_id', 'type' => 'xsd:int'),
		'troncal_nombre' =>array('name' => 'troncal_nombre', 'type' => 'xsd:string'),
		'perfil_amd_id' =>array('name' => 'perfil_amd_id', 'type' => 'xsd:int'),
		'perfil_amd_nombre' =>array('name' => 'perfil_amd_nombre', 'type' => 'xsd:string'),
		'llamada_direccion' =>array('name' => 'llamada_direccion', 'type' => 'xsd:string'),
		'llamada_exitosa' =>array('name' => 'llamada_exitosa', 'type' => 'xsd:int'),
		'llamada_estado' =>array('name' => 'llamada_estado', 'type' => 'xsd:string'),
		'llamada_destino' =>array('name' => 'llamada_destino', 'type' => 'xsd:string'),
		'llamada_origen' =>array('name' => 'llamada_origen', 'type' => 'xsd:string'),
		'llamada_fecha_inicio' =>array('name' => 'llamada_fecha_inicio', 'type' => 'xsd:string'),
		'llamada_fecha_fin' =>array('name' => 'llamada_fecha_fin', 'type' => 'xsd:string'),
		'ivr_id' =>array('name' => 'ivr_id', 'type' => 'xsd:int'),
		'ivr_nombre' =>array('name' => 'ivr_nombre', 'type' => 'xsd:string'),
		'paso_fecha_hora' =>array('name' => 'paso_fecha_hora', 'type' => 'xsd:string'),
		'paso_secuencia' =>array('name' => 'paso_secuencia', 'type' => 'xsd:int'),
		'paso_tipo' =>array('name' => 'paso_tipo', 'type' => 'xsd:string'),
		'paso_info' =>array('name' => 'paso_info', 'type' => 'xsd:string'),
	)
);

$server->wsdl->addComplexType(
	'llamadasArray',
	'complexType',
	'array',
	'',
	'SOAP-ENC:Array',
	array(),
	array(
		array(
			'ref'=>'SOAP-ENC:arrayType'
			,'wsdl:arrayType'=>'tns:llamada[]'
		)
	),
	'tns:llamada'
);

#######Registro de metodos en el WSDL##############
$server->register('obtenerListadoIVR',
	array('todos' => 'xsd:string'),
	array('return' => 'tns:ivrArray'),
	"urn:ivr",
    "urn:ivr#obtenerListadoIVR",
    "rpc",
    "encoded",
    "Obtiene lista de IVRs salientes disponibles"
);

$server->register('realizarLlamada',
	array(
		array('id'		=> 'xsd:int'),
		array('trunk' 	=> 'xsd:int'),
		array('destination' => 'xsd:string'),
		array('ivr' 		=> 'xsd:int'),
		array('maxRetries' => 'xsd:int'),
		array('retryTime' => 'xsd:int'),
		array('account' => 'xsd:string'),
	),
	array('return' => 'xsd:string'),
	"urn:ivr",
    "urn:ivr#realizarLlamada",
    "rpc",
    "encoded",
    "Realizar llamada enviando como parametro el id de la llamada, el destino, la troncal y el ivr"
);

$server->register('obtenerListadoTrocales',
	array(),
	array('return' => 'tns:troncalesArray'),
	"urn:ivr",
    "urn:ivr#obtenerListadoTrocales",
    "rpc",
    "encoded",
    "Obtiene lista de troncales habilitadas em Elastix"
);

$server->register('obternerListadoLLamadasPorDia',
	array('fecha' => 'xsd:string'),
	array('return' => 'tns:llamadasArray'),
	"urn:ivr",
    "urn:ivr#obternerListadoLLamadasPorDia",
    "rpc",
    "encoded",
    "Obtiene lista de de llamadas por dia"
);

if ( !isset( $HTTP_RAW_POST_DATA ) ) $HTTP_RAW_POST_DATA =file_get_contents( 'php://input' );
$server->service($HTTP_RAW_POST_DATA);

?>
