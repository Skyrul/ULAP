<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'calendar_page',
		'customer' => Customer::model()->findByPk($model->list->customer_id),
	));
?>

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

<div class="page-header">
	<h1>Lead Lists</h1>
</div>

<div class="row">
	<div class="col-xs-12">
	
	</div>
</div>