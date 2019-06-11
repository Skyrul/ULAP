<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">

<?php 
	Yii::app()->clientScript->registerScript('sortable-script','
	
	$("#sortable1, #sortable2" ).sortable({
      connectWith: ".connectedSortable",
	  receive: function(event, ui) {
		  
		var containerId = $(this).attr("id");
		var listItem = ui.item;
		if(containerId == "sortable1")
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/hr/team/addMember').'",
				type: "post",	
				data: { 
					"team_id" : "'.$model->id.'",
					"account_id" : listItem.data("id")
				},
				success: function(r){
					
				},
			});
		}
		
		if(containerId == "sortable2")
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/hr/team/removeMember').'",
				type: "post",	
				data: { 
					"team_id" : "'.$model->id.'",
					"account_id" : listItem.data("id")
				},
				success: function(r){
					
				},
			});
		}
	}
	  
    }).disableSelection();
	
',CClientScript::POS_END);

?>

<style>
	#sortable1, #sortable2 {
		border: 1px solid #eee;
		width: 100%;
		min-height: 40px;
		list-style-type: none;
		margin: 0;
		padding: 5px 0 0 0;
		margin-right: 10px;
	}
	#sortable1 li, #sortable2 li {
		margin: 0 5px 5px 5px;
		padding: 5px;
		font-size: 1.2em;
		width: 95%;
	}
</style>


<?php
	foreach(Yii::app()->user->getFlashes() as $key => $message) {
		echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
	}
?>

<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'id'=>'team-form',
		// Please note: When you enable ajax validation, make sure the corresponding
		// controller action is handling ajax validation correctly.
		// There is a call to performAjaxValidation() commented in generated controller code.
		// See class documentation of CActiveForm for details on this.
		'enableAjaxValidation'=>false,
	)); ?>

		<div class="col-sm-6">
			<p class="note">Fields with <span class="required">*</span> are required.</p>

			<?php echo $form->errorSummary($model); ?>

			<div class="form-group">
				<?php echo $form->labelEx($model,'leader_account_id'); ?>
				<?php echo $form->dropDownList($model,'leader_account_id', AccountUser::items(), array('prompt'=>'- Select -')); ?>
				<?php echo $form->error($model,'leader_account_id'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'name'); ?>
				<?php echo $form->textField($model,'name',array('size'=>60,'maxlength'=>255)); ?>
				<?php echo $form->error($model,'name'); ?>
			</div>

			<div class="form-group">
				<?php echo $form->labelEx($model,'description'); ?>
				<?php echo $form->textArea($model,'description',array('rows'=>6, 'cols'=>50)); ?>
				<?php echo $form->error($model,'description'); ?>
			</div>
		</div>
		
		
		<?php if(!$model->isNewRecord): ?>
		
		<div class="col-md-6">
			<div class="form-group">
				<div class="col-sm-12">
					<?php //echo CHtml::dropDownList('SkillAccount[trained][]', $skillAccountsArray['1'],CHtml::listData(Account::model()->byAccountTypeId(Account::TYPE_AGENT)->findAll(),'id','fullName'),array('multiple'=>'multiple') ); ?>
					
					<div class="row">
						<div class="col-sm-6">
							<label>Available</label>
							<ul id="sortable2" class="connectedSortable">
								<?php 
									if( $employees )
									{
										foreach( $employees as $employee)
										{
											echo '<li class="ui-state-default" data-id="'.$employee->account->id.'" >'.$employee->account->accountUser->getfullName().'</li>';
										}
									}
								?>
							</ul>
						</div>
						
						<div class="col-sm-6">
							<label>Members</label>
							<ul id="sortable1" class="connectedSortable">
								<?php 
									if( $members )
									{
										foreach( $members as $member)
										{
											echo '<li class="ui-state-default" data-id="'.$member->account->id.'" >'.$member->account->accountUser->getfullName().'</li>';
										}
									}
								?>
							</ul>
						</div>
					</div>
					<br style="clear:both">
					<div class="hr hr-16 hr-dotted"></div>
				</div>
			</div>
		</div>
		
		<?php endif; ?>
		
		<div class="clearfix"></div>
					
		<div class="form-actions text-center">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-xs')); ?>
		</div>
	
	<?php $this->endWidget(); ?>
	
</div><!-- form -->