<?php
/* @var $this CustomerController */
/* @var $model Customer */

$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'customer',
		'customer' => $model,
));
?>

<h1>Customer <small><?php echo $model->fullName; ?></small></h1>

<?php echo CHtml::link('Update Customer',array('update','id'=>$model->id),array('class'=>'btn btn-success')); ?><br/><br/>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		array(               // related city displayed as a link
            'label'=>$model->getAttributeLabel('company_id'),
            'type'=>'raw',
            'value'=>isset($model->company) ? $model->company->company_name : '',
        ),
		'firstname',
		'middlename',
		'lastname',
		'gender',
		'phone',
		'fax',
		'mobile',
		'email_address',
		'address1',
		'address2',
		'city',
		array(               // related city displayed as a link
            'label'=>$model->getAttributeLabel('state'),
            'type'=>'raw',
            'value'=>isset($model->state0) ? $model->state0->name : '',
        ),
		'zip',
		array(               // related city displayed as a link
            'label'=>$model->getAttributeLabel('status'),
            'type'=>'raw',
            'value'=>$model->statusLabel,
        ),
		'is_deleted',
		'date_created',
		'date_updated',
	),
)); ?>
