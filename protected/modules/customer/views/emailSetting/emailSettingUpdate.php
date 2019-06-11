<div class="page-header">
	<h1>
		Email Setting - Update Template
		
		<button type="button" class="btn btn-primary btn-sm replacement-codes-modal"><i class="fa fa-search"></i> View Replacement Codes</button>
	</h1>
</div>

<?php $this->renderPartial('emailSettingForm',array(
	'customerSkill'=> $customerSkill,
	'customerSkillEmailTemplate'=> $customerSkillEmailTemplate,
	'attachments'=> $attachments,
)); ?>