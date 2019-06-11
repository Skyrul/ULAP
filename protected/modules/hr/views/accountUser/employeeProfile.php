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
		
		$('.date-picker2').datepicker({
			autoclose: true,
			todayHighlight: true,
			showButtonPanel: true,
			changeMonth: true,
			changeYear: true,
			showOtherMonths: true,
			selectOtherMonths: true,
			yearRange: '1930:2018'	 		
		})
		//show datepicker when clicking on the icon
		.next().on(ace.click_event, function(){
			$(this).prev().focus();
		});
		
		$.mask.definitions['~']='[+-]';
		$('.input-mask-phone').mask('(999) 999-9999');
		$('.input-mask-ssn').mask('999-99-9999');
	", CClientScript::POS_END);
?>

<?php 
	// $this->widget("application.components.HrSideMenu",array(
		// 'active'=> Yii::app()->controller->id
	// ));
?>

<div class="tabbable tabs-left">

	<ul class="nav nav-tabs">
	
		<?php 
			if( Yii::app()->user->account->checkPermission('employees_employee_profile_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_profile_tab','only_for_direct_reports') )
			{
				echo '<li class="active">';
					
					if( $account->account_type_id == Account::TYPE_HOSTDIAL_AGENT )
					{
						echo CHtml::link('Host Profile', array('accountUser/hostdialUser'));
					}
					else
					{	
						echo CHtml::link('Employee Profile', array('accountUser/index'));
					}
				echo '</li>';
			}
		?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_employee_file_tab','visible') && Yii::app()->user->account->checkPermission('employees_employee_file_tab','only_for_direct_reports') ){ ?>
			<li><?php echo CHtml::link('Employee File', array('employeeFile', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_time_keeping_tab','visible') && Yii::app()->user->account->checkPermission('employees_time_keeping_tab','only_for_direct_reports') ){ ?>
			<li><?php echo CHtml::link('Time Keeping', array('timeKeeping', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_assigments_tab','visible') && Yii::app()->user->account->checkPermission('employees_assigments_tab','only_for_direct_reports') ){ ?>
			<li><?php echo CHtml::link('Assignments', array('assignments', 'id'=>$account->id)); ?></li>
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_performance_tab','visible') && Yii::app()->user->account->checkPermission('employees_performance_tab','only_for_direct_reports') ){ ?>
			<li><?php echo CHtml::link('Performance', array('performance', 'id'=>$account->id)); ?></li>
		<?php }?>
		
	</ul>
	
	<div class="tab-content">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<?php 
			$this->renderPartial('_form', array(
				'account' => $account,
				'accountUser' => $accountUser,
				'fileupload' => $fileupload,
				'audioFileupload' => $audioFileupload,
				'position'=>$position,
				'reportsToOptions' => $reportsToOptions,
			)); 
		?>
	</div>
</div>