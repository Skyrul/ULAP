<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerScript(uniqid(), '
		
		$(document).on("change", "#payPeriodFilterSelect", function(){
			
			var value = $(this).val();
			
			$("#payperiodExportAllBtn").attr("href", yii.urls.absoluteUrl + "/accounting/exportPayrollFile?filter=" + value);
			
		});
		
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="row">
	<div class="col-sm-12">
		<div class="page-header">
			<h1>
				Accounting
			</h1>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-sm-12">
		<?php echo CHtml::dropDownList('', '', $payPeriodOptions, array('id'=>'payPeriodFilterSelect')); ?>
		
		<?php 
			echo CHtml::link('<i class="fa fa-group"></i> Export Pay Roll File', array('accounting/exportPayrollFile', 'filter'=>0), array('id'=>'payperiodExportAllBtn', 'class'=>'btn btn-yellow btn-xs'))
		?>
	</div>
</div>