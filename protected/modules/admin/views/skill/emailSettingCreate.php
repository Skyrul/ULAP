<div class="page-header">
	<h1>
		Email Setting - Create Template
		
		<button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button>
	</h1>
</div>

<?php $this->renderPartial('emailSettingForm',array(
	'model'=> $model,
	'skillEmailTemplate'=> $skillEmailTemplate,
	'attachments'=> $attachments,
)); ?>