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
  $Id: index.php,v 1.1 2015-02-27 11:02:31 David Revelo drevelo@uio.palosanto.com Exp $ */
//include elastix framework
include_once "libs/paloSantoGrid.class.php";
include_once "libs/paloSantoForm.class.php";
require_once "libs/JSON.php";
// include_once "admin/modules/core/functions.inc.php";


function _moduleContent(&$smarty, $module_name){
    //include module files
    include_once "modules/$module_name/configs/default.conf.php";
    include_once "modules/$module_name/libs/paloSantoAdvance_Ivr_List.class.php";
    include_once "modules/$module_name/libs/paloSantoAdvance_Ivr_Step.class.php";
    include_once "modules/turing_test/libs/paloSantoTuring_Test.class.php";


    //include file language agree to elastix configuration
    //if file language not exists, then include language by default (en)
    $lang=get_language();
    $base_dir=dirname($_SERVER['SCRIPT_FILENAME']);
    $lang_file="modules/$module_name/lang/$lang.lang";
    if (file_exists("$base_dir/$lang_file")) include_once "$lang_file";
    else include_once "modules/$module_name/lang/en.lang";

    //global variables
    global $arrConf;
    global $arrConfModule;
    global $arrLang;
    global $arrLangModule;
    $arrConf = array_merge($arrConf,$arrConfModule);
    $arrLang = array_merge($arrLang,$arrLangModule);

    //folder path for custom templates
    $templates_dir=(isset($arrConf['templates_dir']))?$arrConf['templates_dir']:'themes';
    $local_templates_dir="$base_dir/modules/$module_name/".$templates_dir.'/'.$arrConf['theme'];

    //conexion resource
    $pDB = new paloDB($arrConf['cadena_dsn']);
    if (!is_object($pDB->conn) || $pDB->errMsg!="") {
        $smarty->assign("mb_message", _tr("Error when connecting to database")." ".$pDB->errMsg);
        return '';
    }
    // $smarty->assign("mb_message", ' x - ' .date('Y-m-d'));

    //actions
    $action = getAction();
    $content = "";

    switch($action){
        case 'add':
        case 'edit':
            $content = editAdvance_Ivr($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'delete':
            $content = deleteRecord($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;  
        case 'save':
            $content = insertRecord($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
        case 'list':
        default:
            $content = listAdvance_Ivr($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
            break;
    }
    return $content;
}


/************************* OPERACIONES CRUD *************************************/
#{   

    function listAdvance_Ivr($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
        $pAdvance_Ivr_List = new paloSantoAdvance_Ivr_List($pDB);
        $name = getParameter("name");
        $direction = getParameter("direction");
        $date_start = getParameter("date_start");
        $date_end = getParameter("date_end");
        $filter_field = getParameter("filter_field");
        $filter_value = getParameter("filter_value");

        //begin grid parameters
        $oGrid  = new paloSantoGrid($smarty);
        $oGrid->setTitle(_tr("Advance Ivr List"));
        $oGrid->pagingShow(true); // show paging section.
        $oGrid->addNew("?menu=$module_name&action=add", _tr('New Advance IVR'), TRUE);
        $oGrid->deleteList(_tr("Are you sure you want to delete this Advance IVR?"),'submit_delete', _tr('Delete'));
        $buttonDelete = "";

        //$oGrid->enableExport();   // enable export.
        //$oGrid->setNameFile_Export(_tr("Advance Ivr List"));

        $url = array(
            "menu"        => $module_name,
            "filter_field" =>  $filter_field,
            "filter_value" =>  $filter_value,
            "name"        => $name,
            "direction"   => $direction,
            "date_start"  => $date_start,
            "date_end"    => $date_end,
            
        );
        $oGrid->setURL($url);

        $arrColumns = array($buttonDelete, _tr("Name"),_tr("Direction"),_tr("Creation Date"), _tr('Options'));
        $oGrid->setColumns($arrColumns);


        $pAdvance_Ivr_List->setNombre($name)->setSaliente($direction)->setActivo("1")->setFechasConsulta($date_start, $date_end);
        $total   = $pAdvance_Ivr_List->getNumAdvance_Ivr_List();
        #$smarty->assign("mb_message", " total ".$total);

        if($oGrid->isExportAction()){
            $limit  = $total; // max number of rows.
            $offset = 0;      // since the start.
        }    
        else{
            $limit  = 20;
            $oGrid->setLimit($limit);
            $oGrid->setTotal($total);
            $offset = $oGrid->calculateOffset();
        }

        $arrResult =$pAdvance_Ivr_List->getAdvance_Ivr_List($limit, $offset);

        $arrData = null;
        if(is_array($arrResult) && $total>0){
            foreach($arrResult as $key => $value){ 
              $arrTmp[0] = "<input type='checkbox' name='id_".$value['id']."' />";
              $arrTmp[1] = $value['nombre'];
              $arrTmp[2] = $value['saliente'] == 0? _tr('Inconming') : _tr('Outgoing');
              $arrTmp[3] = $value['fecha_creacion'];
              $arrTmp[4] = "<a href='?menu=$module_name&action=view_edit&id=".$value['id']."' >"._tr("Edit")."</a>";
              $arrData[] = $arrTmp;
            }
        }
        $oGrid->setData($arrData);

        //begin section filter
        $oFilterForm = new paloForm($smarty, createFieldFilter());
        $smarty->assign("SHOW", _tr("Show"));
        $htmlFilter  = $oFilterForm->fetchForm("$local_templates_dir/filter.tpl","",$_POST);
        //end section filter

        $oGrid->showFilter(trim($htmlFilter));
        $content = $oGrid->fetchGrid();
        //end grid parameters

        $conventionalIvrList = $pAdvance_Ivr_List->getConvencionalIvrList();
        return $content;# . print_r($conventionalIvrList);
    }


    function editAdvance_Ivr($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
        if (isset($_POST['cancel'])) {
            Header('Location: ?menu='.$module_name);
            return;
        }

        $oJson = new Services_JSON();
        $oForm = new paloForm($smarty,createEditFields($pDB));
        $pAdvance_Ivr_List = new paloSantoAdvance_Ivr_List($pDB);
        $pAdvance_Ivr_Step = new paloSantoAdvance_Ivr_Step($pDB);


        $id = getParameter("id");


        $title = "";
        $ivrData = array();
        $ivrSteps = array();
        $inconmingAdvanceIvrList = array();
        $outgoingAdvanceIvrList = array();
        $conventionalIvrList = array();
        

        if (!$id){
            $title = _tr('New Advanced IVR');
        }else{
            $registroIvr = $pAdvance_Ivr_List->getAdvance_Ivr_ListById($id);

            $ivrData = array(
                'id'          => $registroIvr['id'],
                'name'        => $registroIvr['nombre'],
                'direction'   => $registroIvr['saliente'],
                'amd'         => $registroIvr['amd'], 
                'turing_test' => $registroIvr['turing_test_id'],
            );

            $registrosPasos = $pAdvance_Ivr_Step->getAdvance_Ivr_Step_By_IvrId($id);
            // shell_exec('logger -t foo "' . count($registrosPasos) .'"');

            foreach($registrosPasos as $paso){
                $nuevoPaso = array(
                    'ivr'        =>  $paso['ivr_id'],
                    'order'      =>  $paso['secuencia'],
                    'type'       =>  $paso['tipo'],
                    'conditions' =>  $paso['condiciones'],
                    'tts'        =>  $paso['texto'],
                    'audio'      =>  $paso['audio'],
                    'redirect'   =>  $paso['redireccion'],
                    'success'    =>  $paso['exito'],
                );

                $ivrSteps[] = $nuevoPaso;
            };

            $title = _tr("Edit Advance IVR: '") . $registroIvr['nombre'] . "'";
        }
        
        $pAdvance_Ivr_List->setActivo("1");

        $outgoingAdvanceIvrList = getKeyValueArray('id','nombre',$pAdvance_Ivr_List->setSaliente("1")->getAdvance_Ivr_List());
        $inconmingAdvanceIvrList = getKeyValueArray('id','nombre',$pAdvance_Ivr_List->setSaliente("0")->getAdvance_Ivr_List());
        $conventionalIvrList = getKeyValueArray('id','nombre',$pAdvance_Ivr_List->getConvencionalIvrList());
        $audioList = getKeyValueArray('id','nombre',$pAdvance_Ivr_List->getAudioRecondingsList());

        $actionListStep = array(
                'AUDIO'      =>  _tr('Audio'),
                'TTS'        =>  _tr('TTS'),
                'REDIRECT'   =>  _tr('Redirect'),
                'CONDITION'  =>  _tr('Condition'),
                'SUCCESS'    =>  _tr('Success'),
        );

        $actionListCondition = array(
                'AUDIO'      =>  _tr('Audio'),
                'TTS'        =>  _tr('TTS'),
                'REDIRECT'   =>  _tr('Redirect')
        );

        $actionListSuccess = array(
                'NONE'      =>  _tr('None'),
                'AUDIO'      =>  _tr('Audio'),
                'TTS'        =>  _tr('TTS'),
        );

        $redirectList = array(
                'ADVANCE_IVR_IN'    =>  _tr('Advance IVR (In)'),
                'ADVANCE_IVR_OUT'   =>  _tr('Advance IVR (Out)'),
                'CONVENTIONAL_IVR'  =>  _tr('Conventional IVR'),
        );

        $conditiontype = array(
            'AUDIO'      =>  _tr('Audio'),
            'TTS'        =>  _tr('TTS'),
        );

        $smarty->assign(array(
            'icon'              =>  'images/list.png',
            'CANCEL'            =>  _tr('Cancel'),
            'SAVE'              =>  _tr('Save'),
            'LABEL_DELETE'      =>  _tr('Delete'),
            
            'LABEL_ORDER'       =>  _tr('Order'),
            'LABEL_TYPE'        =>  _tr('Type'),
            'LABEL_ACTION'      =>  _tr('Action'),
            'LABEL_PARAMETERS'  =>  _tr('Parameters'),
            'LABEL_TYPE2'       =>  _tr('Type'),
            'LABEL_ACTION2'     =>  _tr('Action'),
            'LABEL_CONDITION'   =>  _tr('DTMF'),

            'LABEL_FFADD'       =>  '+',
            'LABEL_FFDEL'       =>  '-',
            'PLACEHOLDER_TTS'   =>  _tr('input text to transform'),
            'TTS_SPAN'          =>  _tr('Transform text to voice'),
            'TOOLTIP_DRAGDROP'  =>  _tr('Drag and drop to reorder steps'),

            'INCOMING_LABEL'    => _tr('Incoming'),
            'INCOMING_VALUE'    => "0",
            'OUTGOING_LABEL'    => _tr('Outgoing'),
            'OUTGOING_VALUE'    => "1",


            'ACTION_TYPE'       => combo($actionListStep, 'TEXT'),
            'ACTION_TYPE2'      => combo($actionListCondition, 'TEXT'),
            'ACTION_TYPE3'      => combo($actionListSuccess, 'TEXT'),
            'REDIRECT'          => combo($redirectList, 'TEXT'),
            'CONDITION_TYPE'    => combo($conditiontype, 'TEXT'),
            'ADVANCE_IVR_IN'    => combo($inconmingAdvanceIvrList, 'TEXT'),
            'ADVANCE_IVR_OUT'   => combo($outgoingAdvanceIvrList, 'TEXT'),
            'CONVENCIONAL_IVR'  => combo($conventionalIvrList, 'TEXT'),
            'AUDIOS'            => combo($audioList, 'TEXT'),

            'IVR_DATA'          => $oJson->encode($ivrData),
            'PASOS_IVR'         => $oJson->encode($ivrSteps),
            'ERR_NO_NAME'       => $oJson->encode(_tr('No name for IVR set')),
            'ERR_NO_STEPS'      => $oJson->encode(_tr('No steps for IVR set')),
            'ERR_NO_AUDIOS'     => $oJson->encode(_tr('No audio records avaible')),
            'ERR_NO_TTS'        => $oJson->encode(_tr('No text to transform defined')),
            'ERR_NO_IVR'        => $oJson->encode(_tr('No placed to redirect exists')),
            'ERR_NO_CONDITION'  => $oJson->encode(_tr('No condition inserted')),
            'ERR_NO_DTMF'       => $oJson->encode(_tr('No DTMF to compare defined')),
            
            // Estos campos sólo se asignan para hacer aparecer el widget de mensajes
            // con el propósito de manipularlo
            'mb_title'      =>  '<span class="mb_title" id="mb_title">mb_title</span>',
            'mb_message'    =>  '<span class="mb_message" id="mb_message">mb_message</span>',
        ));    
        
        return $oForm->fetchForm("$local_templates_dir/edit.tpl", $title, $ivrData);
    }


    function deleteRecord($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
        $pAdvance_Ivr_List = new paloSantoAdvance_Ivr_List($pDB);
        
        $contador = 0;
        foreach($_POST as $key => $values)
            if(substr($key,0,3) == "id_")
                $contador++;
            

        $borradoExitoso = true;
        foreach($_POST as $key => $values){
            if(substr($key,0,3) == "id_"){

                $id = str_replace("_",".",substr($key, 3));
                // writeLOG('advance_ivr', 'id -'. $id);

                if($contador == 1){
                    $ivr = $pAdvance_Ivr_List->getAdvance_Ivr_ListById($id);
                    if ($pAdvance_Ivr_List->deleteAdvance_IvrById($id)){
                        $smarty->assign("mb_message", _tr("Advanced IVR  '") . $ivr['nombre'] . _tr("' was eliminated succesfully!"));
                    }else {
                        $smarty->assign("mb_message", _tr("An error has ocurred while deleting Adnvanced IVR '") . $ivr['nombre'] . "'!. Error: ". $pDB->errMsg);
                    }
                }
                else {
                    if(!$pAdvance_Ivr_List->deleteAdvance_IvrById($id)){
                        $borradoExitoso = false;
                    }
                }
                
            }
        }


        if($contador > 1){
            if ($borradoExitoso){
                $smarty->assign("mb_message", _tr("Advanced IVR registers deleted succesfully!'"));
            }
            else{
                $smarty->assign("mb_message", _tr("There was problems during deleting operation!'"));   
            }
        }

        $content = listAdvance_Ivr($smarty, $module_name, $local_templates_dir, $pDB, $arrConf);
        return $content;
    }


    function insertRecord($smarty, $module_name, $local_templates_dir, &$pDB, $arrConf){
        Header('Content-Type: application/json');
        $json = new Services_JSON();
        $respuesta = array('action'    =>  'saved');


        $id = getParameter("id");
        $name = getParameter("name");
        $direction = getParameter("direction");
        $amd = getParameter("amd");
        $turingTest = getParameter("turing_test");

        $steps = getParameter("steps");
        $creationDate = date('Y-m-d');

        $pAdvance_Ivr_List = new paloSantoAdvance_Ivr_List($pDB);
        $pAdvance_Ivr_List->setNombre($name)->setSaliente($direction)->setAMD($amd)->setTuringTest($turingTest)->setActivo("1")->setFechaCreacion($creationDate);

        foreach($steps as $paso){
            $auxPaso = new paloSantoAdvance_Ivr_Step($pDB);
            $auxPaso->setIvrId($paso['ivr']);
            $auxPaso->setSecuencia($paso['order']);
            $auxPaso->setTipo($paso['type']);
            
            $auxPaso->setCondiciones(isset($paso['conditions']) ? $json->encode($paso['conditions']) : null);
            $auxPaso->setTexto($paso['tts']);
            $auxPaso->setAudio($paso['audio']);
            $auxPaso->setRedireccion($paso['redirect']);
            $auxPaso->setExito(isset($paso['success']) ? $json->encode($paso['success']) : null);

            $pAdvance_Ivr_List->pasos[] = $auxPaso;

        }


        if(isset($id) && $id != ''){
            if (! $pAdvance_Ivr_List->updateAdvance_Ivr_List($id)){
                $respuesta['action'] = 'error';
                $respuesta['message']['title'] = _tr('Error during update');
                $respuesta['message']['message'] = $pAdvance_Ivr_List->obtenerError();    
            }
        }
        /////////////////////INSERTAR/////////////////
        else{
            if (!$pAdvance_Ivr_List->insertAdvance_Ivr_List()){
                $respuesta['action'] = 'error';
                $respuesta['message']['title'] = _tr('Error during insertion');
                $respuesta['message']['message'] = $pAdvance_Ivr_List->obtenerError();
            }
        }

        return $json->encode($respuesta);
    }

#}

/************************* FUNCIONALIDAD ADICIONAL *************************************/
#{
    function getKeyValueArray($keyString, $valueString, $array){
        
        $returnArray = array();

        foreach ($array as $keyArr => $valueArr) {
            $auxKey = null;
            $auxValue = null;
            foreach ($valueArr as $key => $value) {
                if ($key == $keyString)
                    $auxKey = $value;
                if ($key == $valueString)
                    $auxValue = $value;
            }

            if (isset($auxKey))
                $returnArray[$auxKey] =  $auxValue;
        }

        // foreach ($returnArray as $key => $value) {
        //     shell_exec('logger -t foo "' . $key . ' - '. $value .'"');
        // }

        return $returnArray;
    }

    function createFieldFilter(){

        $arrDirection = array(
          "-1" => _tr('Any'), 
          "0" =>  _tr('Inconming'),
          "1" =>  _tr('Outgoing')
        );

        $arrFilter = array(
            'x' => 'y', 
        );

        $arrFormElements = array(
                "name" => array(        "LABEL"                  => _tr("Name"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
                "direction" => array(   "LABEL"                  => _tr("Direction"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrDirection,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
                "date_start"  => array( "LABEL"                  => _tr("Start Date"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "DATE",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "ereg",
                                        "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                "date_end"    => array( "LABEL"                  => _tr("End Date"),
                                        "REQUIRED"               => "yes",
                                        "INPUT_TYPE"             => "DATE",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "ereg",
                                        "VALIDATION_EXTRA_PARAM" => "^[[:digit:]]{1,2}[[:space:]]+[[:alnum:]]{3}[[:space:]]+[[:digit:]]{4}$"),
                "filter_field" => array("LABEL"                  => _tr("Search"),
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "SELECT",
                                        "INPUT_EXTRA_PARAM"      => $arrFilter,
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
                "filter_value" => array("LABEL"                  => "",
                                        "REQUIRED"               => "no",
                                        "INPUT_TYPE"             => "TEXT",
                                        "INPUT_EXTRA_PARAM"      => "",
                                        "VALIDATION_TYPE"        => "text",
                                        "VALIDATION_EXTRA_PARAM" => ""),
        );

        return $arrFormElements;
    }

    function createEditFields(&$pDB){
        $arrDirection = array(
          "0" =>  _tr('Inconming'),
          "1" =>  _tr('Outgoing')
        );


        $pTuring_Test = new paloSantoTuring_Test($pDB);
        $testList = getKeyValueArray('id','nombre',$pTuring_Test->getTuring_Test_List(0,0));


        $json = new Services_JSON();
        // writeLOG('advance_ivr','1 - '.$json->encode($testList));
        // array_unshift($testList, array('0' => 'None'));
        $vacio = array("-1" => "None");
        // $testList = array_merge($vacio, $testList);
        $testList = $vacio + $testList;

        // writeLOG('advance_ivr','2 - '.$json->encode($vacio));
        // writeLOG('advance_ivr','2 - '.$json->encode($testList));




        $arrFields = array(
                "ivr_id"        => array(   "LABEL"                  => "",
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "HIDDEN",
                                            "VALIDATION_TYPE"        => "numeric"),
                "ivr_name"      => array(   "LABEL"                  => _tr("Name"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "TEXT",
                                            "INPUT_EXTRA_PARAM"      => array("size" => "60"),
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "ivr_direction" => array(   "LABEL"                  => _tr("Direction"),
                                            "REQUIRED"               => "no",
                                            "INPUT_TYPE"             => "RADIO",
                                            "INPUT_EXTRA_PARAM"      => $arrDirection,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "turing_test_list" => array("LABEL"                  => _tr("Turing Test"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "SELECT",
                                            "INPUT_EXTRA_PARAM"      => $testList,
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
                "ivr_AMD" => array(         "LABEL"                  => _tr("Use AMD"),
                                            "REQUIRED"               => "yes",
                                            "INPUT_TYPE"             => "CHECKBOX",
                                            "INPUT_EXTRA_PARAM"      => "",
                                            "VALIDATION_TYPE"        => "text",
                                            "VALIDATION_EXTRA_PARAM" => ""),
        );


        return $arrFields;
    }

    function getAction(){
        if(getParameter("save_new")) //Get parameter by POST (submit)
            return "save_new";
        else if(getParameter("save_edit"))
            return "save_edit";
        else if(getParameter("submit_delete")) 
            return "delete";
        else if(getParameter("new_open")) 
            return "view_form";
        else if(getParameter("action")=="view")      //Get parameter by GET (command pattern, links)
            return "view_form";
        else if(getParameter("action")=="add")
            return "edit";
        else if(getParameter("action")=="view_edit")
            return "edit";
        else if(getParameter("action")=="save")
            return "save";
        else
            return "report"; //cancel
    }
#}

?>