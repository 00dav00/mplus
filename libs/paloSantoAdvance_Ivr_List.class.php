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
class paloSantoAdvance_Ivr_List{

/***************ATRIBUTOS*******************/
#{
    private $_id;
    private $_saliente;
    private $_nombre;
    private $_activo;
    private $_amd;
    private $_turingTest;


    private $_fechaInicio;
    private $_fechaFin;

    var $pasos = array();

    var $_DB;
    var $errMsg;
#}

/***************GETTERS***************/
#{
    public function getId(){
        return $this->_id;
    }
    public function getSaliente(){
        return $this->_saliente;
    }
    public function getNombre(){
        return $this->_nombre;
    }
    public function getActivo(){
        return $this->_activo;
    }
    public function getAMD(){
        return $this->_amd;
    }
    public function getTuringTest(){
        return $this->_turingTest;
    }
    public function getFechaCreacion(){
        return $this->_fechaInicio;
    }

    public function obtenerError(){
        return $this->errMsg;
    }
  #}

/***************SETTERS***************/
#{
    private function setId($id){
        $this->_id = $id;
    }
    public function setSaliente($saliente){
        if ($saliente == -1) 
          $saliente = null;
        $this->_saliente = $saliente;
        return $this;
    }
    public function setNombre($nombre){
        if ($nombre == "") 
          $nombre = null;
        $this->_nombre = $nombre;
        return $this;
    }
    public function setActivo($activo){
        $this->_activo = $activo;
        return $this;
    }
    public function setFechaCreacion($fechaCreacion){
        $fecha = date('Y-m-d',strtotime($fechaCreacion));

        $this->_fechaInicio = $fecha;
        $this->_fechaFin = $fecha;
        return $this;
    }
    public function setAMD($amd){
        switch($amd){
            case "":
            case "false":
                $amd = 0;
                break;
            case "true":
                $amd = 1;
                break;
        }
            

        $this->_amd = $amd;
        writeLOG('advance_ivr',$amd);
        return $this;
    }
    public function setTuringTest($turingTest){
        if ($turingTest == "" || $turingTest == "0" || $turingTest == "-1") 
          $turingTest = null;
        $this->_turingTest = $turingTest;
        return $this;
    }


    public function setFechasConsulta($fechaInicio, $fechaFin){
        $fecha1 = strtotime($fechaInicio);
        $fecha2 = strtotime($fechaFin);

        if ($fecha1 > $fecha2)
        {
            $fechaAux = $fecha1;
            $fecha1 = $fecha2;
            $fecha2 = $fechaAux;
        }

        $this->_fechaInicio = date('Y-m-d',$fecha1) ==  '1969-12-31' ? null : date('Y-m-d',$fecha1); 
        $this->_fechaFin = date('Y-m-d',$fecha2) ==  '1969-12-31' ? null : date('Y-m-d',$fecha2);
        return $this;
    }
#}
    

/***********CONSULTAS SQL*****************/
#{
    private $sqlInsertarIvrAvanzado = " INSERT INTO IVRs(ivr_saliente,ivr_nombre,ivr_amd,tur_id,ivr_activo,ivr_fecha_creacion) 
                                        VALUES (?,?,?,?,?,?); ";

    private $sqlListado = "   SELECT
                                IVRs.ivr_id               AS id
                                ,IVRs.ivr_saliente        AS saliente
                                ,IVRs.ivr_nombre          AS nombre
                                ,IVRs.ivr_amd             AS amd
                                ,t.tur_id                 AS turing_test_id
                                ,t.tur_nombre             AS turing_test_nombre
                                ,IVRs.ivr_activo          AS activo                                
                                ,IVRs.ivr_fecha_creacion  AS fecha_creacion
                                ,count(pas_secuencia)     AS total_pasos
                              FROM IVRs 
                                  LEFT JOIN IVR_Pasos AS p ON IVRs.ivr_id = p.ivr_id
                                  LEFT JOIN Test_turing AS t ON IVRs.tur_id = t.tur_id
                              WHERE
                                (? is null OR ? = ivr_saliente)
                                AND (? is null OR ivr_nombre LIKE CONCAT('%', ? ,'%'))
                                AND (? is null OR ? = ivr_amd)
                                AND (? is null OR ? = IVRs.tur_id)
                                AND (? is null OR ? = ivr_activo)
                                AND ((? is null OR ? is null) OR (ivr_fecha_creacion between ? AND ?))
                              GROUP BY 
                                IVRs.ivr_id       
                                ,IVRs.ivr_saliente    
                                ,IVRs.ivr_nombre    
                                ,IVRs.ivr_amd
                                ,t.tur_id
                                ,t.tur_nombre
                                ,IVRs.ivr_activo
                                ,IVRs.ivr_fecha_creacion 
                              ORDER BY 1 DESC
                              LIMIT ? OFFSET ?; ";

    private $sqlIvrPorId = "   SELECT
                                ivr_id               AS id
                                ,ivr_saliente        AS saliente
                                ,ivr_nombre          AS nombre
                                ,ivr_amd             AS amd
                                ,t.tur_id            AS turing_test_id
                                ,t.tur_nombre        AS turing_test_nombre
                                ,ivr_activo          AS activo
                                ,ivr_fecha_creacion  AS fecha_creacion
                              FROM IVRs
                                LEFT JOIN Test_turing AS t ON IVRs.tur_id = t.tur_id
                              WHERE ivr_id = ? 
                            ";


    private $sqlActualizarIvrPorID =  " UPDATE IVRs
                                        SET 
                                            ivr_saliente = ?
                                            ,ivr_nombre = ?
                                            ,ivr_amd = ?
                                            ,tur_id = ?
                                            -- ,ivr_activo          AS activo
                                            -- ,ivr_fecha_creacion  AS fecha_creacion
                                        WHERE
                                            ivr_id = ? ;
                                      ";

     private $sqlEliminarIvrPorId = "  UPDATE IVRs
                                      SET ivr_activo = 0
                                      WHERE ivr_id = ? ; 
                                    ";



    private $sqlIvrConvencionalListado = "    SELECT
                                                id              AS id
                                                ,name           AS nombre
                                                -- ,announcement   AS anuncio
                                              FROM asterisk.ivr_details ; 
                                          ";

    private $sqlGrabacionesListado =    "   SELECT
                                              id              AS id
                                              ,displayname    AS nombre
                                              ,filename       AS direccion
                                              ,description    AS descripcion
                                            FROM asterisk.recordings 
                                            WHERE displayname != '__invalid'; 
                                        ";

    private $sqlTroncalesListado =  "   SELECT
                                            trunkid     AS id
                                            ,name       AS nombre
                                            ,maxchans   AS maximo_canales
                                        FROM asterisk.trunks
                                        WHERE disabled = 'off';
                                    ";   

#}

    function paloSantoAdvance_Ivr_List(&$pDB){
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
    function insertAdvance_Ivr_List(){

        $query   = $this->sqlInsertarIvrAvanzado;

        $arrParam = array();
        $arrParam[] = $this->_saliente;
        $arrParam[] = $this->_nombre;
        $arrParam[] = $this->_amd;
        $arrParam[] = $this->_turingTest;
        $arrParam[] = $this->_activo;
        $arrParam[] = $this->_fechaInicio;


        $this->_DB->beginTransaction();
        
        if (!$this->_DB->genQuery($query, $arrParam)) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        $nuevoId = $this->_DB->getLastInsertId();
        if (!$nuevoId){
            $this->errMsg = $paso->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        foreach ($this->pasos as $paso) {
            $paso->setIvrId($nuevoId);
            if(!$paso->insertAdvance_Ivr_Step()){
                $this->errMsg = $paso->errMsg;
                // $this->_db->rollBack();
                return FALSE;
            }
        }

        $this->_DB->commit();

        return true;
    }

    function updateAdvance_Ivr_List($id){

        $query   = $this->sqlActualizarIvrPorID;

        $arrParam = array();
        $arrParam[] = $this->_saliente;
        $arrParam[] = $this->_nombre;
        $arrParam[] = $this->_amd;
        $arrParam[] = $this->_turingTest;
        $arrParam[] = $id;

        $this->_DB->beginTransaction();
        
        if (!$this->_DB->genQuery($query, $arrParam)) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        $numeroPasosActuales = $this->pasos[0]->getNumAdvance_Ivr_Steps_By_IvrId($id);
        // writeLOG('advance_ivr', 'num - '.$numeroPasosActuales);

        if (is_null($numeroPasosActuales)){
            $this->errMsg = $this->pasos[0]->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        if ($numeroPasosActuales == 0){
            foreach ($this->pasos as $paso) {
                if(!$paso->insertAdvance_Ivr_Step()){
                    $this->errMsg = $paso->errMsg;
                    // $this->_db->rollBack();
                    return FALSE;
                }
            }
        }
       else{

            // IGUALAR NUMERO DE PASOS
            while ($numeroPasosActuales != count($this->pasos)) {
                if($numeroPasosActuales > count($this->pasos)){
                    if($this->pasos[0]->deleteAdvance_Ivr_Last_Step_By_IvrId()){
                        $this->errMsg = $this->_DB->errMsg;
                        // $this->_db->rollBack();
                        return FALSE;
                    }                
                }
                else{
                     if($this->pasos[0]->insertAdvance_Ivr_Dummy_Step()){
                        $this->errMsg = $this->_DB->errMsg;
                        // $this->_db->rollBack();
                        return FALSE;
                    }
                }
                
                $numeroPasosActuales = $this->pasos[0]->getNumAdvance_Ivr_Steps_By_IvrId($id);
                if (is_null($numeroPasosActuales)){
                    $this->errMsg = $this->pasos[0]->errMsg;
                    // $this->_db->rollBack();
                    return FALSE;
                }

                // writeLOG('advance_ivr', 'Actual - '. $numeroPasosActuales);
                // writeLOG('advance_ivr', 'Nuevo - '. count($this->pasos));
            }

            foreach ($this->pasos as $paso) {
                if(!$paso->updateAdvance_Ivr_Step_By_Id()){
                    $this->errMsg = $paso->errMsg;
                    // $this->_db->rollBack();
                    return FALSE;
                }
            }
        }

        $this->_DB->commit();

        return true;
    }

    function simpleUpdateAdvance_Ivr_List($id){

        $query   = $this->sqlActualizarIvrPorID;

        $arrParam = array();
        $arrParam[] = $this->_saliente;
        $arrParam[] = $this->_nombre;
        $arrParam[] = $this->_amd;
        $arrParam[] = $this->_turingTest;
        $arrParam[] = $id;

        if (!$this->_DB->genQuery($query, $arrParam)) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }

        return true;
    }    

    function getNumAdvance_Ivr_List(){
        $registros = $this->getAdvance_Ivr_List(0, 0);
        return count($registros);
    }

    function getAdvance_Ivr_List($limit=0, $offset=0){
        // $query = "select * from IVRs;";
        $query   = $this->sqlListado;

        $arrParam = array();
        $arrParam[] = $this->_saliente;
        $arrParam[] = $this->_saliente;
        $arrParam[] = $this->_nombre;
        $arrParam[] = $this->_nombre;
        $arrParam[] = $this->_amd;
        $arrParam[] = $this->_amd;
        $arrParam[] = $this->_turingTest;
        $arrParam[] = $this->_turingTest;
        $arrParam[] = $this->_activo;
        $arrParam[] = $this->_activo;
        $arrParam[] = $this->_fechaInicio;
        $arrParam[] = $this->_fechaFin;
        $arrParam[] = $this->_fechaInicio;
        $arrParam[] = $this->_fechaFin;
        $arrParam[] = $limit == 0 ? 32000000 : $limit;
        $arrParam[] = $offset;# == 0 ? 1 : $limit;

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return array();
        }
        return $result;
    }

    function getAdvance_Ivr_ListById($id){
        $query = $this->sqlIvrPorId;

        $arrParam = array();
        $arrParam[] = $id;

        $result=$this->_DB->getFirstRowQuery($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }

    function deleteAdvance_IvrById($id){
        $query = $this->sqlEliminarIvrPorId;

        $arrParam = array();
        $arrParam[] = $id;

        if (!$this->_DB->genQuery($query, $arrParam)) {
            $this->errMsg = $this->_DB->errMsg;
            // $this->_db->rollBack();
            return FALSE;
        }
        // $result=$this->_DB->getFirstRowQuery($query, true, $arrParam);

        // if($result==FALSE){
        //     $this->errMsg = $this->_DB->errMsg;
        //     return null;
        // }
        return true;
    }
#}  


/*********METODOS INFO FREE-PBX*********/
#{
    function getConvencionalIvrList(){
        $query = $this->sqlIvrConvencionalListado;

        $arrParam = array();

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }


    function getAudioRecondingsList(){
        $query = $this->sqlGrabacionesListado;

        $arrParam = array();

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }


    function getTrunksLsist(){
        $query = $this->sqlTroncalesListado;

        $arrParam = array();

        $result=$this->_DB->fetchTable($query, true, $arrParam);

        if($result==FALSE){
            $this->errMsg = $this->_DB->errMsg;
            return null;
        }
        return $result;
    }
    
#}

}
