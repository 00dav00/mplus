<!-- end of Message board -->
<form method="POST" name="form_formulario">
    <table width="99%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tr>
            <td>
                <table width="100%" cellpadding="3" cellspacing="0" border="0">
                    <tr>
                        <td align="left">
                            <input class="button" type="button" name="apply_changes" value="{$SAVE}"/>
                            <input class="button" type="submit" name="cancel" value="{$CANCEL}" />
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tabForm">
                                <tr>
                                    <td align="right" valign="top">{$ivr_name.LABEL}: </td>
                                    <td valign="top">{$ivr_name.INPUT}</td>
                                </tr>
                                <!-- <tr>
                                    <td align="right" valign="top">{$ivr_direction.LABEL}:</td>
                                    <td valign="top">{$ivr_direction.INPUT}</td>
                                </tr> -->
                                <tr>
                                    <td align="right" valign="top">{$ivr_direction.LABEL}:</td>
                                    <td class="some-class">
                                        <input type="radio" id="ivr_direction1" name="ivr_direction" value={$INCOMING_VALUE} />
                                        <label for="ivr_direction1">{$INCOMING_LABEL}</label>
                                        <input type="radio" id="ivr_direction2" name="ivr_direction" value={$OUTGOING_VALUE} />
                                        <label for="ivr_direction2">{$OUTGOING_LABEL}</label>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="right" valign="top">{$turing_test_list.LABEL}:</td>
                                    <td valign="top">{$turing_test_list.INPUT}</td>
                                </tr>
                                <tr>
                                    <td align="right" valign="top">{$ivr_AMD.LABEL}:</td>
                                    <td valign="top">{$ivr_AMD.INPUT}</td>
                                </tr>
                            </table>
                        </td>
                </table>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <table class="step_list" border='0' cellspacing='0' cellpadding='0' width='100%' align='center'>
                    <thead>
                        <tr>
                            <td width="40">{$LABEL_ORDER|escape:html}</td>
                            <td width="80">{$LABEL_TYPE|escape:html}</td>
                            <td width="200">{$LABEL_ACTION|escape:html}</td>
                            <td width="500">{$LABEL_PARAMETERS|escape:html}</td>
                            <td width="10">&nbsp;</td>
                        </tr>
                    </thead>
                    <tbody id="tbody_stepList">
                        <tr title="{$TOOLTIP_DRAGDROP}">
                            <td valign="top">
                                <span class="step_order">?</span>
                                <input type="hidden" name="txt_step_secuence" />
                            </td>
                            <td valign="top" class='step_type'>
                                <select name="cbo_step_type">{$ACTION_TYPE}</select>
                            </td>
                            <td valign="top" class="step_action"> 

                                <select name="cbo_step_audio">{$AUDIOS}</select>
                                <span name="lbl_tts">{$TTS_SPAN}</span>
                                <select name="cbo_step_redirect">{$REDIRECT}</select>
                                <select name="cbo_condition_type">{$CONDITION_TYPE}</select>
                                <select name="cbo_success_action">{$ACTION_TYPE3}</select>

                            </td>                            
                            <td valign="top" class='step_paramters'>

                                <input name="txt_step_tts" type="textarea" value="" class="tts_box" placeholder="{$PLACEHOLDER_TTS|escape:html}" />
                                <select name="cbo_step_ivr_in">{$ADVANCE_IVR_IN}</select>
                                <select name="cbo_step_ivr_out">{$ADVANCE_IVR_OUT}</select>
                                <select name="cbo_step_ivr_conv">{$CONVENCIONAL_IVR}</select>
                                <select name="cbo_cond_message">{$AUDIOS}}</select>
                                <input name="txt_cond_message" type="text" value="" placeholder="{$PLACEHOLDER_TTS|escape:html}" />
                                <select name="cbo_success_message">{$AUDIOS}}</select>
                                <input name="txt_success_message" type="textarea" class="tts_box" value="" placeholder="{$PLACEHOLDER_TTS|escape:html}" />
                                <table name="tbl_condition" class="condition_list">
                                    <thead>
                                        <tr>
                                            <td width="10">{$LABEL_CONDITION|escape:html}</td>
                                            <td width="80">{$LABEL_TYPE|escape:html}</td>
                                            <td width="200">{$LABEL_ACTION|escape:html}</td>
                                            <td width="200">{$LABEL_PARAMETERS|escape:html}</td>
                                            <td width="10">&nbsp;</td>
                                        </tr>
                                    </thead>
                                    <tbody id="tbody_conditionList">
                                        <tr>
                                            <td><input type="text" name="txt_dtmf" size="5" /></td>
                                            <td><select name="cbo_cond_type">{$ACTION_TYPE2}</select></td>
                                            <td>
                                                <select name="cbo_cond_audio">{$AUDIOS}</select>
                                                <input type="textarea" name="txt_cond_tts" class="tts_box" value="" placeholder="{$PLACEHOLDER_TTS|escape:html}" />
                                                <select name="cbo_cond_redirect">{$REDIRECT}</select>
                                            </td>
                                            <td>
                                                <select name="cbo_cond_ivr_in">{$ADVANCE_IVR_IN}</select>
                                                <select name="cbo_cond_ivr_out">{$ADVANCE_IVR_OUT}</select>
                                                <select name="cbo_cond_ivr_conv">{$CONVENCIONAL_IVR}</select>
                                            </td>
                                            <td>
                                                <input class="button" type="button" name="condition_add" value="{$LABEL_FFADD|escape:html}" />
                                                <input class="button" type="button" name="condition_del" value="{$LABEL_FFDEL|escape:html}" />
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class='step_order'>
                                <input class="button" type="button" name="step_add" value="{$LABEL_FFADD|escape:html}" />
                                <input class="button" type="button" name="step_del" value="{$LABEL_FFDEL|escape:html}" />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
    {$ivr_id.INPUT}
</form>
<script type="text/javascript">
    IVR_DATA = {$IVR_DATA};
    PASOS_IVR = {$PASOS_IVR};
    ERR_NO_AUDIOS = {$ERR_NO_AUDIOS};
    ERR_NO_TTS = {$ERR_NO_TTS};
    ERR_NO_IVR = {$ERR_NO_IVR};
    ERR_NO_CONDITION = {$ERR_NO_CONDITION};
    ERR_NO_DTMF = {$ERR_NO_DTMF};
    ERR_NO_NAME = {$ERR_NO_NAME};
    ERR_NO_STEPS = {$ERR_NO_STEPS};
</script>