<?php
  /* vim: set expandtab tabstop=4 softtabstop=4 shiftwidth=4:
  Codificación: UTF-8
  +----------------------------------------------------------------------+
  | Elastix version 2.5.0-2                                               |
  | http://www.elastix.org                                               |
  +----------------------------------------------------------------------+
  | Copyright (c) 2006 Palosanto Solutions S. A.                         |
  +----------------------------------------------------------------------+
  | Cdla. Nueva Kennedy Calle E 222 y 9na. Este                          |
  | Telfs. 2283-268, 2294-440, 2284-356                                  |
  | Guayaquil - Ecuador                                                  |
  | http://www.palosanto.com                                             |
  +----------------------------------------------------------------------+
  | The contents of this file are subject to the General Public License  |
  | (GPL) Version 2 (the "License"); you may not use this file except in |
  | compliance with the License. You may obtain a copy of the License at |
  | http://www.opensource.org/licenses/gpl-license.php                   |
  |                                                                      |
  | Software distributed under the License is distributed on an "AS IS"  |
  | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
  | the License for the specific language governing rights and           |
  | limitations under the License.                                       |
  +----------------------------------------------------------------------+
  | The Original Code is: Elastix Open Source.                           |
  | The Initial Developer of the Original Code is PaloSanto Solutions    |
  +----------------------------------------------------------------------+
  $Id: paloSantoAdvance_Ivr_List.class.php,v 1.1 2015-02-27 11:02:31 David Revelo drevelo@uio.palosanto.com Exp $ */
class paloSantoAdvance_Ivr_Step{


  /***************ATRIBUTOS*******************/
  #{
    private $_ivr_id;
    private $_secuencia;
    private $_tipo;

    private $_condiciones;
    private $_texto;
    private $_audio;
    private $_redireccion;
    private $_exito;

    var $_DB;
    var $errMsg;
  #}


  /***************GETTERS***************/
  #{
    public function getIvrId(){
        return $this->_ivr_id;
    }
    public function getSecuencia(){
        return $this->_secuencia;
    }
    public function getTipo(){
        return $this->_tipo;
    }
    public function getCondiciones(){
        return $this->_condiciones;
    }
    public function getTexto(){
        return $this->_texto;
    }
    public function getAudio(){
        return $this->_audio;
    }
    public function getRedireccion(){
        return $this->_redireccion;
    }
    public function getExito(){
        return $this->_exito;
    }
  #}

  /***************SETTERS***************/
  #{
    public function setIvrId($ivrid){
        // if($ivrid == '') $ivrid = null;
        $this->_ivr_id = $ivrid;
    }
    public function setSecuencia($secuencia){
        // if($secuencia == '') $secuencia = null;
        $this->_secuencia = $secuencia;
    }
    public function setTipo($tipo){
        // if($tipo == '') $tipo = null;
        $this->_tipo = $tipo;
    }
    public function setCondiciones($condiciones){
        if($condiciones == '') $condiciones = null;
        $this->_condiciones = $condiciones;
    }
    public function setTexto($texto){
        if($texto == '') $texto = null;
        $this->_texto = $texto;
    }
    public function setAudio($audio){
        if($audio == '') $audio = null;
        $this->_audio = $audio;
    }
    public function setRedireccion($redireccion){
        if($redireccion == '') $redireccion = null;
        $this->_redireccion = $redireccion;
    }
    public function setExito($exito){
        if($exito == '') $exito = null;
        $this->_exito = $exito;
    }
  #}
    

  /***********CONSULTAS SQL*****************/
  #{
    private $sqlPasosPorId = "  SELECT
                                  ivr_id              AS ivr_id
                                  ,pas_secuencia      AS secuencia
                                  ,pas_tipo           AS tipo
                                  ,pas_condiciones    AS condiciones
                                  ,pas_texto          AS texto
                                  ,pas_audio          AS audio
                                  ,pas_redireccion    AS redireccion
                                  ,pas_success        AS exito
                                FROM IVR_Pasos
                                WHERE ivr_id = ?
                                ORDER BY 1,2; ";

    private $sqlInsertar = "  INSERT INTO IVR_Pasos(ivr_id,pas_secuencia,pas_tipo,pas_condiciones,pas_texto,pas_audio,pas_redireccion,pas_success)
                              VALUES (?,?,?,?,?,?,?,?); ";

    private $sqlInsertarRegistroFalso = "   INSERT INTO IVR_Pasos(ivr_id,pas_secuencia,pas_tipo)
                                              SELECT ivr_id, MAX(pas_secuencia) + 1,'AUDIO'
                                              FROM IVR_Pasos 
                                              WHERE ivr_id = ?
                                              GROUP BY ivr_id; ";   

    private $sqlEliminarUltimoPaso = "  DELETE ps1
                                        FROM IVR_Pasos AS ps1
                                        JOIN (
                                            SELECT MAX(pas_secuencia) AS secuencia
                                            FROM IVR_Pasos
                                            WHERE ivr_id = ?
                                        ) AS ps2 ON ps1.pas_secuencia = ps2.secuencia
                                        WHERE ps1.ivr_id = ?; 
                                      ";

    private $sqActualizar = " UPDATE IVR_Pasos 
                              SET pas_tipo = ?
                                  ,pas_condiciones = ?
                                  ,pas_texto = ?
                                  ,pas_audio = ?
                                  ,pas_redireccion = ?
                                  ,pas_success = ?
                              WHERE 
                                  ivr_id = ?
                                  AND pas_secuencia = ?; ";
  #}

    function paloSantoAdvance_Ivr_Step(&$pDB){
        // Se recibe como parámetro una referencia a una conexión paloDB
        if (is_object($pDB)) {
            $this->_DB =& $pDB;
            $this->errMsg = $this->_DB->errMsg;
        } else {
            $dsn = (string)$pDB;
            $this->_DB = new paloDB($dsn);

            if (!$this->_DB->connStatus) {
                $this->errMsg = $this->_DB->errMsg;
                // debo llenar alguna variable de error
            } else {
                // debo llenar alguna variable de error
            }
        }
    }

  /***************METODOS***************/
  #{

    function insertAdvance_Ivr_Step(){
        $query   = $this->sqlInsertar;

        $arrParam = array();
        $arrParam[] = $this->_ivr_id;
        $arrParam[] = $this->_secuencia;
        $arrParam[] = $this->_tipo;

        $arrParam[] = $this->_condiciones;
        $arrParam[] = $this->_texto;
        $arrParam[] = $this->_audio;
        $arrParam[] = $this->_redireccion;
        $arrParam[] = $this->_exito;
        
        if (!$this->_DB->genQuery($query, $arrParam)) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        return true;
    }

    function insertAdvance_Ivr_Dummy_Step(){
        $query   = $this->sqlInsertarRegistroFalso;

        $arrParam = array();
        $arrParam[] = $this->_ivr_id;
        
        $tmp = $this->_DB->genQuery($query, $arrParam);
        // writeLOG('advance_ivr', 'dummy - '.$tmp);


        if ($tmp) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        return true;
    }

    function getNumAdvance_Ivr_Steps_By_IvrId($id){
        $registros = $this->getAdvance_Ivr_Step_By_IvrId($id);
        return count($registros);
    }

    function getAdvance_Ivr_Step_By_IvrId($id){
        $query = $this->sqlPasosPorId;

        $arrParam = array();
        $arrParam[] = $id;

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function deleteAdvance_Ivr_Step_By_IvrId($id){
        $query = $this->sqlEliminarIvrPorId;

        $arrParam = array();
        $arrParam[] = $id;

        $result=$this->_DB->getFirstRowQuery($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return true;
    }

    function deleteAdvance_Ivr_Last_Step_By_IvrId(){
        
        $query = $this->sqlEliminarUltimoPaso;

        $arrParam = array();
        $arrParam[] = $this->_ivr_id;
        $arrParam[] = $this->_ivr_id;

        $result=$this->_DB->getFirstRowQuery($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return true;
    }

    function updateAdvance_Ivr_Step_By_Id(){

        $query   = $this->sqActualizar;

        $arrParam = array();
        $arrParam[] = $this->_tipo;
        $arrParam[] = $this->_condiciones;
        $arrParam[] = $this->_texto;
        $arrParam[] = $this->_audio;
        $arrParam[] = $this->_redireccion;
        $arrParam[] = $this->_exito;
        
        $arrParam[] = $this->_ivr_id;
        $arrParam[] = $this->_secuencia;

        if (!$this->_DB->genQuery($query, $arrParam)) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        return true;
    }
  #}  

}
