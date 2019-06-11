<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id != null ? Customer::model()->findByPk($customer_id) : null,
	));
?>

<div class="page-header">
	<h1>
		Update List 
		<small>
			<i class="ace-icon fa fa-angle-double-right"></i>
			<?php echo CHtml::link('Download list template', array('downloadStandardTemplate'), array('class'=>'orange')); ?>
		</small>
	</h1>
</div>

<div class="row">
	<div class="col-xs-12">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '
					<div class="alert alert-' . $key . '">
						<button data-dismiss="alert" class="close" type="button">
							<i class="ace-icon fa fa-times"></i>
						</button>' . $message . "
					</div>\n";
			}
		?>

		<?php 
			$this->renderPartial('_form', array(
				'model' => $model,
				'customer_id' => $customer_id,
				'simpleView' => $simpleView,
				'leadsWaiting' => $leadsWaiting,
				'contract' => $contract,
			));
		?>
		
	</div>
</div>