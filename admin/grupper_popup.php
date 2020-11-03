<?require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");IncludeModuleLangFile(__FILE__);CModule::IncludeModule('kit.grupper');CModule::IncludeModule('iblock');$IBLOCK_ID = IntVal($_REQUEST["IBLOCK_ID"]);if($_REQUEST["tabControl_active_tab"]=="grupper_popup"){	if((isset($_REQUEST["save"]) || isset($_REQUEST["apply"])) && $IBLOCK_ID>0)	{		$arGroups = array();		$res1 = CRSGGroups::GetList(array("SORT"=>"ASC","NAME"=>"ASC"),array());		while($data1 = $res1->Fetch())		{			$CODE = abs(crc32($data1["CODE"]));			if(isset($_REQUEST["props_r_".$CODE]) && is_array($_REQUEST["props_r_".$CODE]) && count($_REQUEST["props_r_".$CODE])>0)			{				CRSGBinds::DeleteBindsForGroupID($data1["ID"]);				foreach($_REQUEST["props_r_".$CODE] as $prop_id)				{					$arFields = array(						"IBLOCK_PROPERTY_ID" => $prop_id,						"GROUP_ID" => $data1["ID"],					);					$BIND_ID = CRSGBinds::Add($arFields);				}			} else {				CRSGBinds::DeleteBindsForGroupID($data1["ID"]);			}		}		LocalRedirect( "/bitrix/admin/iblock_property_admin.php?lang=".LANG."&IBLOCK_ID=".$IBLOCK_ID );	} else {		$arErrors[] = GetMessage("GRUPPER_ERROR");	}}// tabs list$aTabs = array(	array(		"DIV" => "grupper_popup",		"TAB" => GetMessage("GRUPPER_TAB1_NAME"),		"ICON" => "main_user_edit",		"TITLE" => GetMessage("GRUPPER_POPUPADV_TAB1_DESCRIPTION")	),);$tabControl = new CAdminTabControl("tabControl", $aTabs);// set page title$APPLICATION->SetTitle( GetMessage("GRUPPER_POPUPADV_PAGE_TITLE") );// include prologrequire($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");// show errorsif(count($arErrors)>0){	CAdminMessage::ShowMessage( implode('<br />', $arErrors) );}// taking data$arGroups = array();$arBinds = array();$arPropesUsed = array();$res = CRSGGroups::GetList(array("SORT"=>"ASC","NAME"=>"ASC"),array());while($data = $res->Fetch()){	$key = abs(crc32($data["CODE"]));	$arGroups[$key] = array(		"ID" => $data["ID"],		"NAME" => $data["NAME"],	);	$res2 = CRSGBinds::GetList(array("SORT"=>"ASC","NAME"=>"ASC"),array("GROUP_ID"=>$data["ID"]));	while($data2 = $res2->Fetch())	{		$arBinds[$data["ID"]][$data2["ID"]] = array(			"ID" => $data2["ID"],			"PROPERTY_ID" => $data2["IBLOCK_PROPERTY_ID"],		);		$arPropesUsed[] = $data2["IBLOCK_PROPERTY_ID"];	}}$arProperties = array();$arPropertiesFull = array();$res2 = CIBlockProperty::GetList(Array("SORT"=>"ASC","NAME"=>"ASC"), Array("ACTIVE" => "Y", "IBLOCK_ID" => $IBLOCK_ID));while ($data2 = $res2->GetNext()){	$arProperties[$data2["ID"]] = $data2["NAME"];	$arPropertiesFull[$data2["ID"]] = $data2;}CAjax::Init();?><script>function rs_get_selected_group(what_return){	var opt_groups = document.getElementById("rs_groups");		for(i=0;i<opt_groups.options.length;i++)	{		if(opt_groups.options[i].selected==1)		{			var GROUP_CODE = opt_groups.options[i].value;			var GROUP_NAME = opt_groups.options[i].text;			break;		}    	}	if(what_return=="CODE")	{		return GROUP_CODE;	} else {		return GROUP_NAME;	}}function rs_change_group(){	var chosed_gr_code = rs_get_selected_group("CODE");	var opt_r = document.getElementById("props_r_"+chosed_gr_code);<?foreach($arGroups as $key1 => $group){	echo '	document.getElementById("props_r_'.$key1.'").style.display = "none";';}?>	opt_r.style.display = "block";}function rs_move_to_l(){	var opt_l = document.getElementById("props_l");	var chosed_gr_code = rs_get_selected_group("CODE");	var opt_r = document.getElementById("props_r_"+chosed_gr_code);	for(i=0;i<opt_r.options.length;i++)	{		if(opt_r.options[i].selected==1)		{			var o = opt_r.options[i];			opt_l.appendChild(o);		}    	}}function rs_move_to_r(){	var opt_l = document.getElementById("props_l");	var chosed_gr_code = rs_get_selected_group("CODE");	var opt_r = document.getElementById("props_r_"+chosed_gr_code);	for(i=0;i<opt_l.options.length;i++)	{		if(opt_l.options[i].selected==1)		{			var o = opt_l.options[i];			opt_r.appendChild(o);		}    	}}<?if($_REQUEST["bxpublic"]=="Y"):?>function rs_before_send_form(data){<?foreach($arGroups as $key1 => $group){	?>	var rs_select_for_all_select_<?=$key1?> = document.getElementById("props_r_<?=$key1?>");	for(i=0;i<rs_select_for_all_select_<?=$key1?>.options.length;i++)	{		if(rs_select_for_all_select_<?=$key1?>.options[i].selected!=1)		{			rs_select_for_all_select_<?=$key1?>.options[i].selected = true;		}    	}	<?}?>}<?endif;?></script><?// show form?><form id="kit_grupper_popup" method="POST" action="<?=$APPLICATION->GetCurPage()?>" ENCTYPE="multipart/form-data" name="kit_grupper_popup"><input type="hidden" name="IBLOCK_ID" value="<?=$IBLOCK_ID?>" /><?// sessid_id checkerecho bitrix_sessid_post();// tabs header$tabControl->Begin();//___________________________________________________________________________________________// tab//___________________________________________________________________________________________$tabControl->BeginNextTab();?>	<tr>		<td colspan="3" align="center" style="text-align:center;">			<b><?=GetMessage("GRUPPER_PARAM_GROUPS")?></b><br />			<select id="rs_groups" name="rs_groups" onchange="rs_change_group();" size="7" style="width:462px;"><?				$i=0;				foreach($arGroups as $key1 => $group)				{					echo '<option value="'.$key1.'"';					if($i<1) echo ' selected ';					echo'>'.$group["NAME"].'</option>';					$i++;				}			?></select>		</td>	</tr>	<tr>		<td width="45%" valign="top" align="right" class="adm-detail-content-cell-l">			<b><?=GetMessage("GRUPPER_PARAM_PROPERTIES")?></b><br />			<select id="props_l" name="props_l[]" size="7" style="width:200px;"><?				foreach($arProperties as $key2 => $prop)				{					if(!in_array($key2,$arPropesUsed))						echo '<option value="'.$key2.'">'.$prop.'</option>';				}			?></select>		</td>		<td width="10%" valign="top" align="center" style="text-align:center;">			&nbsp;<br />&nbsp;<br />&nbsp;<br />			<input type="button" name="rs_grupper_move_l" id="rs_grupper_move_l" onclick="rs_move_to_l();return false;" value="<" />			<input type="button" name="rs_grupper_move_r" id="rs_grupper_move_r" onclick="rs_move_to_r();return false;" value=">" />		</td>		<td width="45%" valign="top" align="left" class="adm-detail-content-cell-r">			<b><?=GetMessage("GRUPPER_PARAM_GRUPPED_PROPS")?></b><br /><?			$i=0;			foreach($arGroups as $key1 => $group)			{				?><select id="props_r_<?=$key1?>" name="props_r_<?=$key1?>[]" size="7" style="width:200px;<?if($i>0):?>display:none;<?endif;?>" multiple ><?					foreach($arBinds[$group["ID"]] as $key => $bind)					{						if($arPropertiesFull[$bind["PROPERTY_ID"]]["IBLOCK_ID"]==$IBLOCK_ID)						{							echo '<option value="'.$bind["PROPERTY_ID"].'">'.$arProperties[$bind["PROPERTY_ID"]].'</option>';						}					}				?></select><?			$i++;			}		?></td>	</tr><input type="hidden" name="lang" value="<?=LANG?>"></form><?// tab bottonsif($_REQUEST["bxpublic"]=="Y"){	$save = "{		title: '".CUtil::JSEscape(GetMessage("GRUPPER_BTN_SAVE"))."',		name: 'savebtn',		id: 'savebtn',		className: 'adm-btn-save',		action: function () {			rs_before_send_form(this);			var FORM = this.parentWindow.GetForm();			this.parentWindow.hideNotify();			this.disableUntilError();			this.parentWindow.Submit();		}	}";	$cancel = "{		title: '".CUtil::JSEscape(GetMessage("GRUPPER_BTN_CANCEL"))."',		name: 'cancel',		id: 'cancel',		action: function () {			BX.WindowManager.Get().Close();			if(window.reloadAfterClose)				top.BX.reload(true);		}	}";	$tabControl->ButtonsPublic(array(		$save,		$cancel,	));} else {	$tabControl->Buttons(		array(			"back_url" => "kit_grupper.php?lang=".LANG,		)	);}// tab footer$tabControl->End();// include epilogrequire($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>