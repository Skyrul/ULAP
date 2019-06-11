<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');


	$cs->registerScript(uniqid(), "
		
		$(document).on('click', '#Account_status', function() {
			
			if( $(this).not(':checked') )
			{
				$('#AccountUser_has_employee_portal_access').prop('checked', false);
			}
			
		});
		
		$(document).on('click', '#AccountUser_has_employee_portal_access', function() {
			
			if( $('#Account_status').is(':checked') )
			{
				return true;
			}
			else
			{
				return false;
			}
			
		});

	
		$('.date-picker').datepicker({
			autoclose: true,
			todayHighlight: true
		});
		
		$.mask.definitions['~']='[+-]';
		$('.input-mask-phone').mask('(999) 999-9999');
		$('.input-mask-ssn').mask('999-99-9999');
	", CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.HrSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1>
				Create User
			</h1>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">

		<div class="tab-content">
			<?php 
				$this->renderPartial('_form',array(
					'account'=>$account,
					'accountUser'=>$accountUser,
					'fileupload'=>$fileupload,
					'audioFileupload'=>$audioFileupload,
				));
			?>
		</div>
	</div>
</div>

