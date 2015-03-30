var module_name = 'advance_ivr_list';
var template_step = null;
var template_condition =  null;

$(document).ready(function() {
	if (typeof PASOS_IVR == 'undefined') return;
	
	// Esconder recuadro de error hasta que se deba mostrar un mensaje
	$('#message_error, .message_board').hide();
	
	// Preparar la plantilla para inserción de campos
	var tbody_stepList = $('#tbody_stepList');

	template_condition = $('#tbody_conditionList > tr').detach();
	template_condition.addClass('condition_row');

	template_step = $('#tbody_stepList > tr').detach();
	template_step.addClass('step_row');

	if (IVR_DATA.id == 'undefined' || IVR_DATA.length == 0){
		$('input[name="ivr_direction"][value="0"]').prop("checked", true);
	}
	else{
		$('input[name="ivr_id"]').val(IVR_DATA['id']);
		$('input[name="ivr_name"]').val(IVR_DATA['name']);
		$('input[name="ivr_direction"][value="'+ IVR_DATA['direction'] +'"] ').prop("checked", true);
		$('input[name="chkoldivr_AMD"]').prop("checked", Boolean(parseInt(IVR_DATA['amd'])));
		$('select[name="turing_test_list"]').val(IVR_DATA['turing_test']);

		// alert(Boolean(parseInt(IVR_DATA['amd'])));
		insert_ivr_steps();	
	}
	

	if(!$('#tbody_stepList').children().hasClass('step_final'))
		$('#tbody_stepList').append(get_new_step_row());

	/***********************************************
			EVENTOS PARA CONTROL DE PASOS
	************************************************/
	// CAMBIOS EN EL TIPO DE PASO
	$(this).on('change','select[name="cbo_step_type"]', function(){
		var aux_row = $(this).parents('.step_row');

		switch($(this).val()){
			case 'AUDIO':
				aux_row.find('.fields_audio').show();
				aux_row.find('.fields_tts').hide();
				aux_row.find('.fields_redirect').hide();
				aux_row.find('.fields_condition').hide();
				aux_row.find('.fields_success').hide();
				aux_row.removeClass('step_final');
				break;
			case 'TTS':
				aux_row.find('.fields_audio').hide();
				aux_row.find('.fields_tts').show();
				aux_row.find('.fields_redirect').hide();
				aux_row.find('.fields_condition').hide();
				aux_row.find('.fields_success').hide();
				aux_row.removeClass('step_final');
				break;
			case 'REDIRECT':
				aux_row.find('.fields_audio').hide();
				aux_row.find('.fields_tts').hide();
				aux_row.find('.fields_redirect').show();
				aux_row.find('.fields_condition').hide();
				aux_row.find('.fields_success').hide();

				aux_row.find('select[name="cbo_step_ivr_in"]').show();
				aux_row.find('select[name="cbo_step_ivr_out"]').hide();
				aux_row.find('select[name="cbo_step_ivr_conv"]').hide();
				aux_row.addClass('step_final');
				break;
			case 'CONDITION':
				aux_row.find('.fields_audio').hide();
				aux_row.find('.fields_tts').hide();
				aux_row.find('.fields_redirect').hide();
				aux_row.find('.fields_condition').show();
				aux_row.find('.fields_success').hide();

				aux_row.find('.fields_cond_audio').show();
				aux_row.find('.fields_cond_tts').hide();
				aux_row.find('.fields_cond_redirect').hide();
				
				aux_row.find('input[name="condition_del"]').hide();

				aux_row.find('select[name="cbo_condition_type"]').change();
					
				aux_row.removeClass('step_final');
				break;
			case 'SUCCESS':
				aux_row.find('.fields_audio').hide();
				aux_row.find('.fields_tts').hide();
				aux_row.find('.fields_redirect').hide();
				aux_row.find('.fields_condition').hide();
				aux_row.find('.fields_success').show();

				aux_row.find('input[name="condition_del"]').hide();

				aux_row.find('select[name="cbo_success_action"]').change();
					
				aux_row.removeClass('step_final');
				break;
		}
	});

	// CAMBIOS EN EL TIPO DE REDIRECCION
	$(this).on('change','select[name="cbo_step_redirect"]', function(){
		var aux_row = $(this).parents('.step_row');

		switch($(this).val()){
			case 'ADVANCE_IVR_IN':
				aux_row.find('select[name="cbo_step_ivr_in"]').show();
				aux_row.find('select[name="cbo_step_ivr_out"]').hide();
				aux_row.find('select[name="cbo_step_ivr_conv"]').hide();
				break;
			case 'ADVANCE_IVR_OUT':
				aux_row.find('select[name="cbo_step_ivr_in"]').hide();
				aux_row.find('select[name="cbo_step_ivr_out"]').show();
				aux_row.find('select[name="cbo_step_ivr_conv"]').hide();
				break;
			case 'CONVENTIONAL_IVR':
				aux_row.find('select[name="cbo_step_ivr_in"]').hide();
				aux_row.find('select[name="cbo_step_ivr_out"]').hide();
				aux_row.find('select[name="cbo_step_ivr_conv"]').show();
				break;
		}				
	});

	// CAMBIOS EN EL TIPO DE MENSAJE EN LA CONDICION
	$(this).on('change','select[name="cbo_condition_type"]', function(){
		var aux_step = $(this).parents('.step_row');

		if ($(this).val() == 'AUDIO'){
			aux_step.find('select[name="cbo_cond_message"]').show();
			aux_step.find('input[name="txt_cond_message"]').hide();
		}
		else{
			aux_step.find('select[name="cbo_cond_message"]').hide();
			aux_step.find('input[name="txt_cond_message"]').show();
		}
	});

	// CAMBIOS EN EL TIPO DE MENSAJE DE LA BANDERA DE EXITO
	$(this).on('change','select[name="cbo_success_action"]', function(){
		var aux_step = $(this).parents('.step_row');

		switch($(this).val()){
			case 'NONE':
				aux_step.find('select[name="cbo_success_message"]').hide();
				aux_step.find('input[name="txt_success_message"]').hide();
				break;
			case 'AUDIO':
				aux_step.find('select[name="cbo_success_message"]').show();
				aux_step.find('input[name="txt_success_message"]').hide();
				break;
			case 'TTS':
				aux_step.find('select[name="cbo_success_message"]').hide();
				aux_step.find('input[name="txt_success_message"]').show();
				break;
			
		}
	});

	// AGREGAR PASO AL DAR CLIC EN EL BOTON (+)
	$(this).on('click', 'tr.step_row input[name="step_add"]', function() {
		var aux_step = $(this).parents('.step_row');
		var error_info = {id:"", description:""};

		if(is_step_ready(aux_step,error_info)){
			aux_step.removeClass('step_new');
			aux_step.find('input[name="step_add"]').hide();
			aux_step.find('input[name="step_del"]').show();
			if(!aux_step.hasClass('step_final')){
				aux_step.find('select[name="cbo_step_type"] option[value="REDIRECT"]').prop('disabled', true);
				$('#tbody_stepList').append(get_new_step_row());
			}
				
			renumerar_campos();
		}
		else{
			alert(error_info.description,'Error');
		}
	});

	// QUITAR PASO AL DAR CLIC EN EL BOTON (-)
	$(this).on('click','tr.step_row input[name="step_del"]', function() {
		var agregarCampo = $(this).parents('tr.step_row').hasClass('step_final');
		$(this).parents('tr.step_row').remove();

		if(agregarCampo){
			// $('#tbody_stepList tr:last').find('select[name="cbo_step_type"]  option[value="REDIRECT"]').prop('disabled', false);
			$('#tbody_stepList').append(get_new_step_row());
		}
				
		renumerar_campos();
	});


	/***********************************************
			EVENTOS PARA CONTROL DE CONDICIONES
	************************************************/
	// CAMBIOS EN EL TIPO DE CONDICION
	$(this).on('change','select[name="cbo_cond_type"]', function() {
		aux_condition = $(this).parents('.condition_row');

		switch($(this).val()){
			case 'AUDIO':
				aux_condition.find('.fields_cond_audio').show();
				aux_condition.find('.fields_cond_tts').hide();
				aux_condition.find('.fields_cond_redirect').hide();
				break;
			case 'TTS':
				aux_condition.find('.fields_cond_audio').hide();
				aux_condition.find('.fields_cond_tts').show();
				aux_condition.find('.fields_cond_redirect').hide();
				break;
			case 'REDIRECT':
				aux_condition.find('.fields_cond_audio').hide();
				aux_condition.find('.fields_cond_tts').hide();
				aux_condition.find('.fields_cond_redirect').show();

				aux_condition.find('select[name="cbo_cond_ivr_in"]').show();
				aux_condition.find('select[name="cbo_cond_ivr_out"]').hide();
				aux_condition.find('select[name="cbo_cond_ivr_conv"]').hide();
				break;
		}
	});

	// CAMBIOS EN EL TIPO DE REDIRECCION
	$(this).on('change','select[name="cbo_cond_redirect"]', function(){
		aux_condition = $(this).parents('.condition_row');

		switch($(this).val()){
			case 'ADVANCE_IVR_IN':
				aux_condition.find('select[name="cbo_cond_ivr_in"]').show();
				aux_condition.find('select[name="cbo_cond_ivr_out"]').hide();
				aux_condition.find('select[name="cbo_cond_ivr_conv"]').hide();
				break;
			case 'ADVANCE_IVR_OUT':
				aux_condition.find('select[name="cbo_cond_ivr_in"]').hide();
				aux_condition.find('select[name="cbo_cond_ivr_out"]').show();
				aux_condition.find('select[name="cbo_cond_ivr_conv"]').hide();
				break;
			case 'CONVENTIONAL_IVR':
				aux_condition.find('select[name="cbo_cond_ivr_in"]').hide();
				aux_condition.find('select[name="cbo_cond_ivr_out"]').hide();
				aux_condition.find('select[name="cbo_cond_ivr_conv"]').show();
				break;
		}				
	});
	
	// AGREGAR CONDICION AL DAR CLIC EN EL BOTON (+)
	$(this).on('click','tr.step_row input[name="condition_add"]', function() {
		var aux_condition = $(this).parents('.condition_row');
		var aux_step = aux_condition.parents('.step_row');
		var error_info = {id:"", description:""};

		if(is_condition_ready(aux_condition,error_info)){
			aux_condition.removeClass('condition_new');
			aux_condition.find('input[name="condition_add"]').hide();
			aux_condition.find('input[name="condition_del"]').show();
			aux_step.find('#tbody_conditionList').append(get_new_condition_row());
		}
		else{
			alert(error_info.description,'Error');
		}
	});

	// QUITAR CONDICION AL DAR CLIC EN EL BOTON (-)
	$(this).on('click', 'tr.condition_row input[name="condition_del"]', function() {
		$(this).parents('tr.condition_row').remove();
		renumerar_campos();
	});
	

	/***********************************************
		EVENTOS PARA FUNCIONALIDAD DE LA PAG
	************************************************/
	
	// REORDENAMIENTO DE CAMPOS DEL FORMULARIO
	$('#tbody_stepList').sortable({
		items: 'tr:not(.step_new .condition_row)',
		stop: renumerar_campos
	});
	
	// MANDAR LOS CAMBIOS AL SERVIDOR
	$('form[name="form_formulario"] input[name="apply_changes"]').on('click', function() {
		if ($('form[name="form_formulario"] input[name="ivr_name"]').val().trim() != ''){
			if ($('#tbody_stepList tr.step_row').not('.step_new').length > 0){
				var postvars = {
					menu:			module_name,
					action:			'save',
					rawmode:		'yes',
					id:				$('form[name="form_formulario"] input[name="ivr_id"]').val(),
					name:			$('form[name="form_formulario"] input[name="ivr_name"]').val(),
					direction:		$('form[name="form_formulario"] input[name="ivr_direction"]:checked').val(),
					amd: 			$('input[name="chkoldivr_AMD"]').prop('checked'),
					turing_test: 	$('select[name="turing_test_list"]').val(),
					
					steps:			get_selected_steps()
				};



				if (postvars.id == '') delete postvars.id;
				$.post('index.php', postvars, function (response) {
					if (response.action == 'error') {
						$('#mb_title').text(response.message.title);
						$('#mb_message').text(response.message.message);
						$('#message_error, .message_board').show().delay(10 * 1000).fadeOut(500);
					} else {
						window.open('?menu=' + module_name, '_parent');
					}
				});	
			}
			else{
				$('#mb_title').text('Error');
				$('#mb_message').text(ERR_NO_STEPS);
				$('#message_error, .message_board').show().delay(10 * 1000).fadeOut(500);
			}
		}
		else{
			$('#mb_title').text('Error');
			$('#mb_message').text(ERR_NO_NAME);
			$('#message_error, .message_board').show().delay(10 * 1000).fadeOut(500);
			$('form[name="form_formulario"] input[name="ivr_name"]').focus();
		}	
	});

});



/****************************************************
						FUNCIONES
*****************************************************/
// DEVUELVE PASO EN BLANCO PARA SER INSERTADA EN LA LISTA
function get_new_step_row(){
	var aux_step = template_step.clone();
	aux_step.addClass('step_new');
	aux_step.attr('title', null);
	aux_step.find('span.step_order').text('');
	aux_step.find('input[name="txt_step_secuence"]').val('');
	// aux_step.find('select[name="cbo_step_type"]').val('TTS');
	
	/*****ACTION********/
	aux_step.find('select[name="cbo_step_audio"]').addClass('fields_audio');
	aux_step.find('span[name="lbl_tts"]').addClass('fields_tts');
	aux_step.find('select[name="cbo_step_redirect"]').addClass('fields_redirect');
	aux_step.find('select[name="cbo_condition_type"]').addClass('fields_condition');
	aux_step.find('select[name="cbo_success_action"]').addClass('fields_success');

	/*****PARAMETERS********/
	aux_step.find('input[name="txt_step_tts"]').val('');
	aux_step.find('input[name="txt_step_tts"]').addClass('fields_tts');
	aux_step.find('select[name="cbo_step_ivr_in"]').addClass('fields_redirect');
	aux_step.find('select[name="cbo_step_ivr_out"]').addClass('fields_redirect');
	aux_step.find('select[name="cbo_step_ivr_conv"]').addClass('fields_redirect');
	aux_step.find('table[name="tbl_condition"]').addClass('fields_condition');
	aux_step.find('select[name="cbo_cond_message"]').addClass('fields_condition');
	aux_step.find('input[name="txt_cond_message"]').addClass('fields_condition');
	aux_step.find('select[name="cbo_success_message"]').addClass('fields_success');
	aux_step.find('input[name="txt_success_message"]').addClass('fields_success');

	aux_step.find('#tbody_conditionList').append(get_new_condition_row());

	aux_step.find('.fields_tts').hide();
	aux_step.find('.fields_redirect').hide();
	aux_step.find('.fields_condition').hide();
	aux_step.find('.fields_success').hide();

	aux_step.find('input[name="step_del"]').hide();

	return aux_step;
}

// DEVUELVE CONDICION EN BLANCO PARA SER INSERTADA EN LA LISTA
function get_new_condition_row(){
	var aux_condition = template_condition.clone();
	aux_condition.addClass('condition_new');

	/*****CONDITION********/
	aux_condition.find('input[name="txt_dtmf"]').keyup(function () {
	    if (this.value != this.value.replace(/[^0-9\.]/g, '')) {
	       this.value = this.value.replace(/[^0-9\.]/g, '');
	    }
	});
	aux_condition.find('select[name="cbo_cond_audio"]').addClass('fields_cond_audio');
	aux_condition.find('input[name="txt_cond_tts"]').addClass('fields_cond_tts');
	aux_condition.find('select[name="cbo_cond_redirect"]').addClass('fields_cond_redirect');
	aux_condition.find('select[name="cbo_cond_ivr_in"]').addClass('fields_cond_redirect');
	aux_condition.find('select[name="cbo_cond_ivr_out"]').addClass('fields_cond_redirect');
	aux_condition.find('select[name="cbo_cond_ivr_conv"]').addClass('fields_cond_redirect');

	aux_condition.find('.fields_cond_audio').show();
	aux_condition.find('.fields_cond_tts').hide();
	aux_condition.find('.fields_cond_redirect').hide();

	aux_condition.find('input[name="condition_del"]').hide();

	return aux_condition;
}

// VERIFICAR SI UN PASO CONTIENE TODOS LOS CAMPOS NECESARIOS 
function is_step_ready(par_step, error_info){
	var complete_info = true;

	switch(par_step.find('select[name="cbo_step_type"]').val()){
		case 'AUDIO':
			if (par_step.find('select[name="cbo_step_audio"] option').size() <= 0){
				complete_info = false;
				error_info.description = ERR_NO_AUDIOS;
			}
			break;
		case 'TTS':
			if (par_step.find('input[name="txt_step_tts"]').val().trim() == ''){
				complete_info = false;
				error_info.description = ERR_NO_TTS;
			}
			break;
		case 'REDIRECT':
			switch(par_step.find('select[name="cbo_step_redirect"]').val()){
				case 'CONVENTIONAL_IVR':
					if (par_step.find('select[name="cbo_step_ivr_in"]').size() <= 0 ){
						complete_info = false;
						error_info.description = ERR_NO_IVR;
					}
					break;
				case 'ADVANCE_IVR_IN':
					if (par_step.find('select[name="cbo_step_ivr_out"]').size() <= 0 ){
						complete_info = false;
						error_info.description = ERR_NO_IVR;
					}
					break;
				case 'ADVANCE_IVR_OUT':
					if (par_step.find('select[name="cbo_step_ivr_conv"]').size() <= 0 ){
						complete_info = false;
						error_info.description = ERR_NO_IVR;
					}
					break;
			}
			break;
		case 'CONDITION':
			if (par_step.find('select[name="cbo_condition_type"]').val() == 'AUDIO'){
				if (par_step.find('select[name="cbo_cond_message"] option').size() <= 0){
					complete_info = false;
					error_info.description = ERR_NO_AUDIOS;
					par_step.find('select[name="cbo_cond_message"]').focus();
				}
			}
			else{
				if (par_step.find('input[name="txt_cond_message"]').val().trim() == ''){
					complete_info = false;
					error_info.description = ERR_NO_TTS;
					par_step.find('input[name="txt_cond_message"]').focus();
				}
			}

			if (complete_info)
				if (par_step.find('#tbody_conditionList tr').length < 2){
					complete_info = is_condition_ready(par_step.find('#tbody_conditionList tr'),error_info);
				}
			break;
		case 'SUCCESS':
			if (par_step.find('select[name="cbo_success_action"]').val() != 'NONE'){
				if (par_step.find('select[name="cbo_success_action"]').val() == 'AUDIO'){
					if (par_step.find('select[name="cbo_success_message"] option').size() <= 0){
						complete_info = false;
						error_info.description = ERR_NO_AUDIOS;
						par_step.find('select[name="cbo_success_message"]').focus();
					}
				}
				else{
					if (par_step.find('input[name="txt_success_message"]').val().trim() == ''){
						complete_info = false;
						error_info.description = ERR_NO_TTS;
						par_step.find('input[name="txt_success_message"]').focus();
					}
				}
			}
			break;
	}

	return complete_info;
}

// VERIFICAR SI UNA CONDICION CONTIENE TODOS LOS CAMPOS NECESARIOS 
function is_condition_ready(par_condition, error_info){
	var complete_info = true;

	if (par_condition.find('input[name="txt_dtmf"]').val().trim() == ''){
		complete_info = false;
		error_info.description = ERR_NO_DTMF;
		par_condition.find('input[name="txt_dtmf"]').focus();
	}
	else {
		switch(par_condition.find('select[name="cbo_cond_type"]').val()){
			case 'AUDIO':
				if (par_condition.find('select[name="cbo_cond_audio"] option').size() <= 0){
					complete_info = false;
					error_info.description = ERR_NO_AUDIOS;
				}
				break;
			case 'TTS':
				if (par_condition.find('input[name="txt_cond_tts"]').val().trim() == ''){
					complete_info = false;
					error_info.description = ERR_NO_TTS;
				}
				break;
			case 'REDIRECT':
				switch(par_condition.find('select[name="cbo_cond_redirect"]').val()){
					case 'CONVENTIONAL_IVR':
						if (par_condition.find('select[name="cbo_cond_ivr_in"]').size() <= 0 ){
							complete_info = false;
							error_info.description = ERR_NO_IVR;
						}
						break;
					case 'ADVANCE_IVR_IN':
						if (par_condition.find('select[name="cbo_cond_ivr_out"]').size() <= 0 ){
							complete_info = false;
							error_info.description = ERR_NO_IVR;
						}
						break;
					case 'ADVANCE_IVR_OUT':
						if (par_condition.find('select[name="cbo_cond_ivr_conv"]').size() <= 0 ){
							complete_info = false;
							error_info.description = ERR_NO_IVR;
						}
						break;
				}
				break;
		}
	}

	return complete_info;
}

// OBTENER MENSAJE PASOS DEL IVR
function get_selected_steps(){
	var aux_return = [];

	$('#tbody_stepList tr.step_row').each(function() {
		var error_info = {id:"", description:""};
		// alert($(this).find('input[name="txt_step_secuence"]').val() + ' - ' + $(this).find('select[name="cbo_step_type"]').val());

		if(!$(this).hasClass('step_new')){
			if(is_step_ready($(this),error_info)){
				$(this).addClass('step_ready');
			}	
		}
	});

	$('#tbody_stepList tr.step_ready').each(function (){
		var aux_audio = get_selected_audio($(this));
		var aux_tts = get_selected_tts($(this));
		var aux_redirect = get_selected_redirection($(this),'step');
		var aux_condition = get_selected_conditions($(this));
		var aux_success = get_selected_success($(this));

		// alert(	'order:  ' +		$(this).find('input[name="txt_step_secuence"]').val() +  '\n' +
		// 		'type:  ' +			$(this).find('select[name="cbo_step_type"]').val() + '\n' +
		// 		'conditions:  ' + 	JSON.stringify(aux_condition,null,4) + '\n' +
		// 		'tts:  ' +			aux_tts + '\n' +
		// 		'audio:  ' + 		aux_audio + '\n' +
		// 		'redirect:  ' + 	aux_redirect + '\n' +
		// 		'success: 	' + 	JSON.stringify(aux_success,null,4));

		aux_item = {
						ivr:		$('form[name="form_formulario"] input[name="ivr_id"]').val(),
						order:		$(this).find('input[name="txt_step_secuence"]').val(),
						type:		$(this).find('select[name="cbo_step_type"]').val(),
						conditions: aux_condition,
						tts:		aux_tts,
						audio: 		aux_audio,
						redirect: 	aux_redirect,
						success: 	aux_success,
					};

		aux_return.push(aux_item);
	});

	// alert(aux_return.length);
	return aux_return;
}

// OBTENER CONDICIONES DEFINIDAS
function get_selected_conditions(par_step){
	
	var aux_return = [];

	if (par_step.find('select[name="cbo_step_type"]').val() == 'CONDITION'){

		var aux_conditions_array = [];

		if (par_step.find('#tbody_conditionList tr').length > 0){
			
			par_step.find('#tbody_conditionList tr').each(function(){
				var error_info = {id:"", description:""};
				if (is_condition_ready($(this),error_info)){
					$(this).addClass('condition_ready');
				}	
			});

			par_step.find('#tbody_conditionList tr.condition_ready').each(function(){
				aux_condition = $(this).find('input[name="txt_dtmf"]').val();
				aux_action_type = $(this).find('select[name="cbo_cond_type"]').val();
				aux_action = "";

				switch(aux_action_type){
					case 'AUDIO':
						aux_action = $(this).find('select[name="cbo_cond_audio"]').val();
						break;
					case 'TTS':
						aux_action = $(this).find('input[name="txt_cond_tts"]').val();
						break;
					case 'REDIRECT':
						aux_action = get_selected_redirection($(this),'cond');
						break;
				}

				// alert(	'condition: ' + 		aux_condition + '\n' +
				// 		'action_type: ' + 	aux_action_type + '\n' +
				// 		'action: ' + 		aux_action + '\n' );

				aux_item = {
					condition: 		aux_condition,
					action_type: 	aux_action_type,
					action: 		aux_action
				};

				aux_conditions_array.push(aux_item);
			});
		}

		aux_return = 	{
							type: par_step.find('select[name="cbo_condition_type"]').val(),
							message: get_condition_message(par_step),
							conditions: aux_conditions_array,
						};
	}

	return aux_return;
}

// OBTENER PARAMETROS DE REDIRECCION DEFINIDOS
function get_selected_redirection(par_row, par_type){
	var aux_redirect = ""

	if (par_row.find('select[name="cbo_'+ par_type +'_type"]').val() == 'REDIRECT'){
		switch(par_row.find('select[name="cbo_'+ par_type +'_redirect"]').val()){
			case 'ADVANCE_IVR_IN':
				aux_redirect = par_row.find('select[name="cbo_'+ par_type +'_ivr_in"]').val();
				break;
			case 'ADVANCE_IVR_OUT':
				aux_redirect = par_row.find('select[name="cbo_'+ par_type +'_ivr_out"]').val();
				break;
			case 'CONVENTIONAL_IVR':
				aux_redirect = par_row.find('select[name="cbo_'+ par_type +'_ivr_conv"]').val();
				break;
		}
	}

	if(aux_redirect != ''){
		aux_redirect = par_row.find('select[name="cbo_'+ par_type +'_redirect"]').val() + '|' + aux_redirect;
	}
		
	return aux_redirect;
}

// OBTENER PARAMETROS DE AUDIO DEFINIDOS
function get_selected_audio(par_row){
	var aux_audio = ""
	
	if (par_row.find('select[name="cbo_step_type"]').val() == 'AUDIO'){
		aux_audio = par_row.find('select[name="cbo_step_audio"]').val();
	}
	// else if (par_row.find('select[name="cbo_step_type"]').val() == 'SUCCESS' && par_row.find('select[name="cbo_success_action"]').val() == 'AUDIO'){
	// 	aux_audio = par_row.find('select[name="cbo_success_message"]').val();	
	// }

	return aux_audio;
}

// OBTENER PARAMETROS DE TTS DEFINIDOS
function get_selected_tts(par_row){
	var aux_return = ""
	
	if (par_row.find('select[name="cbo_step_type"]').val() == 'TTS'){
		aux_return = par_row.find('input[name="txt_step_tts"]').val();
	}
	// else if (par_row.find('select[name="cbo_step_type"]').val() == 'SUCCESS' && par_row.find('select[name="cbo_success_action"]').val() == 'TTS'){
	// 	aux_audio = par_row.find('select[name="txt_success_message"]').val();	
	// }

	return aux_return;
}

// OBTENER PARAMETROS DE TTS DEFINIDOS
function get_selected_success(par_row){
	var aux_return = []
	
	if (par_row.find('select[name="cbo_step_type"]').val() == 'SUCCESS'){
		aux_return=	{
						type: par_row.find('select[name="cbo_success_action"]').val(),
						message: get_success_message(par_row),
					}
	}

	return aux_return;
}

// OBTENER MENSAJE A SER REPRODUCDO DURANTE CONDICION
function get_condition_message(par_row){
	var aux_return = "";
	if(par_row.find('select[name="cbo_condition_type"]').val() == 'AUDIO'){
		aux_return = par_row.find('select[name="cbo_cond_message"]').val();
	}
	else{
		aux_return = par_row.find('input[name="txt_cond_message"]').val().trim();
	}

	return aux_return;
}

// COLOCAR MENSAJE A SER REPRODUCDO DURANTE CONDICION
function set_condition_message(par_row, par_type, par_message){
	if(par_type == 'AUDIO'){
		par_row.find('select[name="cbo_cond_message"]').val(par_message).show();
		par_row.find('input[name="txt_cond_message"]').hide();
	}
	else{
		par_row.find('input[name="txt_cond_message"]').val(par_message).show();
		par_row.find('select[name="cbo_cond_message"]').hide();
	}
}

// COLOCAR MENSAJE DE EXITO
function get_success_message(par_row){
	var aux_return = "";

	if(par_row.find('select[name="cbo_success_action"]').val() == 'AUDIO'){
		aux_return = par_row.find('select[name="cbo_success_message"]').val();
	}
	else if (par_row.find('select[name="cbo_success_action"]').val() == 'TTS'){
		aux_return = par_row.find('input[name="txt_success_message"]').val().trim();
	}

	return aux_return;
}

// COLOCAR MENSAJE DE EXITO
function set_success_message(par_row, par_type, par_message){
	switch(par_type){
		case 'NONE':
			par_row.find('select[name="cbo_success_message"]').hide();
			par_row.find('input[name="txt_success_message"]').hide();
			break;
		case 'AUDIO':
			par_row.find('select[name="cbo_success_message"]').val(par_message).show();
			par_row.find('input[name="txt_success_message"]').hide();
			break;
		case 'TTS':
			par_row.find('input[name="txt_success_message"]').val(par_message).show();
		par_row.find('select[name="cbo_success_message"]').hide();
			break;
	}
	// if(par_type == 'AUDIO'){
	// 	par_row.find('select[name="cbo_cond_message"]').val(par_message).show();
	// 	par_row.find('input[name="txt_cond_message"]').hide();
	// }
	// else{
	// 	par_row.find('input[name="txt_cond_message"]').val(par_message).show();
	// 	par_row.find('select[name="cbo_cond_message"]').hide();
	// }
}

// INSERTAR LOS CAMPOS DEL FORMULARIO EXISTENTE (SI EXISTEN)
function insert_ivr_steps(){
	for (var i = 0; i < PASOS_IVR.length; i++) {

		var aux_step = get_new_step_row();
		aux_step.find('span.step_order').text(PASOS_IVR[i].order);
		aux_step.find('input[name="txt_step_secuence"]').val(PASOS_IVR[i].order);
		aux_step.find('select[name="cbo_step_type"]').val(PASOS_IVR[i].type);

		switch(PASOS_IVR[i].type){
			case 'AUDIO':
				aux_step.find('.fields_audio').show();
				aux_step.find('.fields_tts').hide();
				aux_step.find('.fields_redirect').hide();
				aux_step.find('.fields_condition').hide();
				aux_step.find('.fields_success').hide();
				aux_step.find('select[name="cbo_step_audio"]').val(PASOS_IVR[i].audio);
				break;
			case 'TTS':
				aux_step.find('.fields_audio').hide();
				aux_step.find('.fields_tts').show();
				aux_step.find('.fields_redirect').hide();
				aux_step.find('.fields_condition').hide();
				aux_step.find('.fields_success').hide();
				aux_step.find('input[name="txt_step_tts"]').val(PASOS_IVR[i].tts);
				// aux_step.find('input[name="txt_step_tts"]').attr('placeholder', '');
				break;
			case 'REDIRECT':
				aux_step.find('.fields_audio').hide();
				aux_step.find('.fields_tts').hide();
				aux_step.find('.fields_redirect').show();
				aux_step.find('.fields_condition').hide();
				aux_step.find('.fields_success').hide();

				var redirectType = PASOS_IVR[i].redirect.substr(0, PASOS_IVR[i].redirect.indexOf('|')); 
				var redirectValue = PASOS_IVR[i].redirect.replace(redirectType + '|','');

				aux_step.find('select[name="cbo_step_redirect"]').val(redirectType);
				aux_step.addClass('step_final');

				switch(redirectType){
					case 'ADVANCE_IVR_IN':
						aux_step.find('select[name="cbo_step_ivr_in"]').val(redirectValue);
						aux_step.find('select[name="cbo_step_ivr_out"]').hide();
						aux_step.find('select[name="cbo_step_ivr_conv"]').hide();
						break;
					case 'ADVANCE_IVR_OUT':
						aux_step.find('select[name="cbo_step_ivr_in"]').hide();
						aux_step.find('select[name="cbo_step_ivr_out"]').val(redirectValue);
						aux_step.find('select[name="cbo_step_ivr_conv"]').hide();
						break;
					case 'CONVENTIONAL_IVR':
						aux_step.find('select[name="cbo_step_ivr_in"]').hide();
						aux_step.find('select[name="cbo_step_ivr_out"]').hide();
						aux_step.find('select[name="cbo_step_ivr_conv"]').val(redirectValue);
						break;
				}				
				break;
			case 'CONDITION':
				aux_step.find('.fields_audio').hide();
				aux_step.find('.fields_tts').hide();
				aux_step.find('.fields_redirect').hide();
				aux_step.find('.fields_condition').show();
				aux_step.find('.fields_success').hide();
				
				aux_step.find('#tbody_conditionList tr').remove();
				
				var jsonObj = $.parseJSON(PASOS_IVR[i].conditions);
				
				aux_step.find('select[name="cbo_condition_type"]').val(jsonObj.type);
				set_condition_message(aux_step, jsonObj.type, jsonObj.message);

				insert_conditions(aux_step,jsonObj.conditions);
				// alert(jsonObj.length);
				


				break;
			case 'SUCCESS':
				aux_step.find('.fields_audio').hide();
				aux_step.find('.fields_tts').hide();
				aux_step.find('.fields_redirect').hide();
				aux_step.find('.fields_condition').hide();
				aux_step.find('.fields_success').show();
				
				var jsonObj = $.parseJSON(PASOS_IVR[i].success);
				
				aux_step.find('select[name="cbo_success_action"]').val(jsonObj.type);
				set_success_message(aux_step, jsonObj.type, jsonObj.message);

				break;
		}

		aux_step.find('input[name="step_add"]').hide();
		aux_step.find('input[name="step_del"]').show();
		aux_step.removeClass('step_new');

		if(PASOS_IVR[i].type != 'REDIRECT' && PASOS_IVR.length > i+1){
			aux_step.find('select[name="cbo_step_type"] option[value="REDIRECT"]').prop('disabled', true);
		}
			
		$('#tbody_stepList').append(aux_step);
		
	}
}

// INSERTAR LAS CONDICIONES SI EXISTEN
function insert_conditions(par_step,par_condition_list){
	for (var i = 0; i < par_condition_list.length; i++) {

		var aux_condition = get_new_condition_row();

		aux_condition.find('input[name="txt_dtmf"]').val(par_condition_list[i].condition);
		aux_condition.find('select[name="cbo_cond_type"]').val(par_condition_list[i].action_type);

		switch(par_condition_list[i].action_type){
			case 'AUDIO':
				aux_condition.find('.fields_cond_audio').show();
				aux_condition.find('.fields_cond_tts').hide();
				aux_condition.find('.fields_cond_redirect').hide();
				aux_condition.find('select[name="cbo_cond_audio"]').val(par_condition_list[i].action);
				break;
			case 'TTS':
				aux_condition.find('.fields_cond_audio').hide();
				aux_condition.find('.fields_cond_tts').show();
				aux_condition.find('.fields_cond_redirect').hide();
				aux_condition.find('input[name="txt_cond_tts"]').val(par_condition_list[i].action);
				// aux_condition.find('input[name="txt_cond_tts"]').attr('placeholder', '');
				break;
			case 'REDIRECT':
				aux_condition.find('.fields_cond_audio').hide();
				aux_condition.find('.fields_cond_tts').hide();
				aux_condition.find('.fields_cond_redirect').show();

				var redirectType = par_condition_list[i].action.substr(0, par_condition_list[i].action.indexOf('|')); 
				var redirectValue = par_condition_list[i].action.replace(redirectType + '|','');

				aux_condition.find('select[name="cbo_cond_redirect"]').val(redirectType);

				switch(redirectType){
					case 'ADVANCE_IVR_IN':
						aux_condition.find('select[name="cbo_cond_ivr_in"]').val(redirectValue);
						aux_condition.find('select[name="cbo_cond_ivr_out"]').hide();
						aux_condition.find('select[name="cbo_cond_ivr_conv"]').hide();
						break;
					case 'ADVANCE_IVR_OUT':
						aux_condition.find('select[name="cbo_cond_ivr_in"]').hide();
						aux_condition.find('select[name="cbo_cond_ivr_out"]').val(redirectValue);
						aux_condition.find('select[name="cbo_cond_ivr_conv"]').hide();
						break;
					case 'CONVENTIONAL_IVR':
						aux_condition.find('select[name="cbo_cond_ivr_in"]').hide();
						aux_condition.find('select[name="cbo_cond_ivr_out"]').hide();
						aux_condition.find('select[name="cbo_cond_ivr_conv"]').val(redirectValue);
						break;
				}				
				break;
		}

		aux_condition.find('input[name="condition_add"]').hide();
		aux_condition.find('input[name="condition_del"]').show();
		aux_condition.removeClass('condition_new');
		par_step.find('#tbody_conditionList').append(aux_condition);
	}
	par_step.find('#tbody_conditionList').append(get_new_condition_row());
}

// CAMBIAR EL CODIGO DEL ORDEN DE LOS PASOS
function renumerar_campos(){
	$('#tbody_stepList tr.step_row').not('.step_new').each(function(i) {
		$(this).find('span.step_order').text(i + 1);
		$(this).find('input[name="txt_step_secuence"]').val(i + 1);
	});
}
