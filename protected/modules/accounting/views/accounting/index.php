<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(),'

		var current_pay_period = "'.$currentPayPeriod.'";
	
	', CClientScript::POS_HEAD);
	
	$cs->registerScript(uniqid(), '
		
		$(document).ready( function(){
			
			$(document).on("change", "#payPeriodFilterSelect", function(){
			
				var value = $(this).val();
				
				$("#payperiodExportAllBtn").attr("href", yii.urls.absoluteUrl + "/accounting/accounting/exportPayrollFile?filter=" + value);
				
			});
			
			if( current_pay_period != "" )
			{
				$("#payPeriodFilterSelect").val(current_pay_period);
				$("#payPeriodFilterSelect").trigger("change");
			}
			
		});
		
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.AccountingSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1>
				Payroll File
			</h1>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php echo CHtml::dropDownList('', '', $payPeriodOptions, array('id'=>'payPeriodFilterSelect')); ?>
		
		<?php 
			if( Yii::app()->user->account->checkPermission('accounting_payroll_file_export_button','visible') )
			{
				echo CHtml::link('<i class="fa fa-group"></i> Export Pay Roll File', array('accounting/exportPayrollFile', 'filter'=>0), array('id'=>'payperiodExportAllBtn', 'class'=>'btn btn-yellow btn-xs'));
			}
		?>
	</div>
</div>