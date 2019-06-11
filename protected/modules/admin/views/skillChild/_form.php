<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/jquery.bootstrap-duallistbox.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/template_assets/js/fuelux/fuelux.spinner.min.js',  CClientScript::POS_END); ?>

<style>
 .spinner-up, .spinner-down{ 
  font-size: 10px !important;
  height: 16px !important;
  line-height: 8px !important;
  margin-left: 0 !important;
  padding: 0 !important;
  width: 22px !important;
 }
</style>
<?php 
Yii::app()->clientScript->registerScript('spinnerJS', '
$(".spinner").ace_spinner({value:0,min:1,max:9,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"})
   .on("change", function(){
    if( this.value < 1)
    {
     this.value = 1;
    }
    
    if( this.value > 9)
    {
     this.value = 9;
    }
   });
', CClientScript::POS_END);
 ?>
 
<?php 
	Yii::app()->clientScript->registerScript('select2js', '

		$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END);
 ?>
 
 <?php 
	Yii::app()->clientScript->registerScript('caller-option-dial-as-container-script', '
		$("#'.CHtml::activeId($model,'caller_option').'").on("change",function(){
			var callerOptionVal = $(this).val();
			
			if(callerOptionVal == "'.Skill::CALLER_OPTION_DIAL_AS.'")
			{
				$(".caller-option-dial-as-container").show();
			}
			else
			{
				$(".caller-option-dial-as-container").hide();
			}
		}); 

		$("#'.CHtml::activeId($model,'caller_option').'").trigger("change");
		
', CClientScript::POS_END);
 ?>

  <?php 
	Yii::app()->clientScript->registerScript('has_inbound-script', '
		$("#'.CHtml::activeId($model,'has_inbound').'").on("change",function(){
			var hasInboundVal = $(this).val();
			
			if(hasInboundVal == 1)
			{
				$(".has-inbound-container").show();
			}
			else
			{
				$(".has-inbound-container").hide();
			}
		}); 

		$("#'.CHtml::activeId($model,'has_inbound').'").trigger("change");
		
', CClientScript::POS_END);
 ?>
 
 
 
 
<?php 
	Yii::app()->clientScript->registerScript('assignedAgenetDualListScript','
	
	// var demo1 = $(\'select[name="SkillAccount[trained][]"]\').bootstrapDualListbox({
				// nonSelectedListLabel: "Assigned Agents - Not Trained",
				// selectedListLabel: "Assigned Agents - Trained",
			// });
				// var container1 = demo1.bootstrapDualListbox("getContainer");
				// container1.find(".btn").addClass("btn-white btn-info btn-bold");
				
	// $(".bootstrap-duallistbox-container .btn-group").hide();
	
	',CClientScript::POS_END);
?>

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
				url: "'.Yii::app()->createUrl('/admin/skillChild/removeSkillChildAccount').'",
				type: "GET",	
				data: { 
					"skill_child_id" : "'.$model->id.'",
					"account_id" : listItem.data("id")
				},
				success: function(r){
					
				},
			});
		}
		
		if(containerId == "sortable2")
		{
			$.ajax({
				url: "'.Yii::app()->createUrl('/admin/skillChild/addSkillAccount').'",
				type: "GET",	
				data: { 
					"skill_child_id" : "'.$model->id.'",
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
	if($model->isNewRecord){ 
		Yii::app()->clientScript->registerScript('showHide','
			$("#createNewOrCloneExisting").on("change",function(){
				$(".js-container-toggle").addClass("hidden");
				
				if($(this).val() == 1)
				{
					$(".js-container-toggle").removeClass("hidden");
					$("#clone-existing-dropdown-container").addClass("hidden");
					
					$("#submit-btn-container").removeClass("hidden");
				}
				else if($(this).val() == 2)
				{
					$(".js-container-toggle").addClass("hidden");
					$("#clone-existing-container").removeClass("hidden");
					$("#clone-existing-dropdown-container").removeClass("hidden");
					
					$("#submit-btn-container").removeClass("hidden");
				}
				else
				{
					$(".js-container-toggle").addClass("hidden");
					$("#submit-btn-container").addClass("hidden");
				}
			});
			
			$("#createNewOrCloneExisting").trigger("change");
			
		',CClientScript::POS_END); 
	}
?>
<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'skill-child-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions' => array('class'=>'form', 'enctype' => 'multipart/form-data')
)); ?>
<div class="col-md-6">
		<p class="note">Fields with <span class="required">*</span> are required.</p>

		<?php echo $form->errorSummary($model); ?>
		
		<?php if($model->isNewRecord){ ?>
			<div class="row">
				<div class="form-group">
					<?php echo CHtml::label('Create New or Clone Existing', '', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo CHtml::dropDownList('createNewOrCloneExisting',@$_REQUEST['createNewOrCloneExisting'],array('1' => 'Create New', '2'=> 'Clone Existing'), array('empty' => '-Select an option-')); ?>
					</div>
				</div>
			</div>
		<?php } ?>
		
		<div id="clone-existing-container" class="js-container-toggle">
			<?php
				$skillChildArray = array();
				$skills = Skill::model()->findAll();
				foreach($skills as $skill)
				{
					foreach($skill->skillChilds as $skillChild)
					{
						$skillChildArray[$skill->skill_name][$skillChild->id] = $skillChild->child_name;
					}
				}
			?>
			
			<?php if($model->isNewRecord){ ?>
			<div id="clone-existing-dropdown-container" class="js-container-toggle">
				<div class="row">
					<div class="form-group">
						<?php echo $form->labelEx($model,'existingId', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
						
						<div class="col-sm-7">
							<?php echo $form->dropDownList($model,'existingId', $skillChildArray,array('empty'=>'-Select Existing Child Skill-') ); ?>
						<?php echo $form->error($model,'existingId'); ?>
						
						</div>
					</div>
				</div>
			</div>
			<?php } ?>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'skill_id', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->dropDownList($model,'skill_id',CHtml::listData(Skill::model()->byIsDeletedNot()->findAll(),'id','skill_name'),array('empty'=>'-Select Skill-')); ?>
						<?php echo $form->error($model,'skill_id'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'child_name', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->textField($model,'child_name',array('size'=>60,'maxlength'=>128)); ?>
						<?php echo $form->error($model,'child_name'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'description', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->textField($model,'description',array('size'=>60,'maxlength'=>250)); ?>
						<?php echo $form->error($model,'description'); ?>
					</div>
				</div>
			</div>
		</div>
		
		<div class="js-container-toggle">
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'type', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->dropDownList($model,'type',SkillChild::listTypes(), array('class'=>'','empty'=>'-Select Call Type-')); ?>
						<?php echo $form->error($model,'type'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'status', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->dropDownList($model,'status',SkillChild::listStatus()); ?>
						<?php echo $form->error($model,'status'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'is_language', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->dropDownList($model,'is_language',array(1=> 'Yes', 0 => 'No')); ?>
						<?php echo $form->error($model,'is_language'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'language', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->dropDownList($model,'language',SkillChild::listLanguage(),array('empty'=>'-Select Language-')); ?>
						<?php echo $form->error($model,'language'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'is_reminder_call', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->dropDownList($model,'is_reminder_call',array(1=> 'Yes', 0 => 'No')); ?>
						<?php echo $form->error($model,'is_reminder_call'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'max_dials', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->textField($model,'max_dials',array('size'=>5,'maxlength'=>250)); ?>
						<?php echo $form->error($model,'max_dials'); ?>
					</div>
				</div>
			</div>
			
			<div class="row">
				<div class="space-6"></div>
				
				<?php echo $form->labelEx($model,'enable_dialer_script_tab', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
				
				<div class="col-sm-7">
					<?php echo $form->dropDownList($model,'enable_dialer_script_tab', array(0 => 'No', 1=> 'Yes'), array('class'=>'form-control form-control-inline')); ?>
					<?php echo $form->error($model,'enable_dialer_script_tab'); ?>
				</div>
			</div>
			
			<div class="row">
				<div class="form-group">
					<?php echo $form->labelEx($model,'fileUpload', array('class'=>'col-sm-4 control-label no-padding-right')); ?>
					
					<div class="col-sm-7">
						<?php echo $form->fileField($model,'fileUpload'); ?>
						<?php echo $form->error($model,'fileUpload'); ?>
						
						<?php
							if( $model->script_tab_fileupload_id != null )
							{
								echo '<div class="space-6"></div>';
								echo 'Current Script Tab File: ' . CHtml::link($model->scriptFileupload->original_filename, array('download', 'id'=>$model->script_tab_fileupload_id));
							}
						?>
					</div>
				</div>
			</div>
		</div>
</div>
	<?php if(!$model->isNewRecord){ ?>
	
	<div class="col-md-6">
		<div class="form-group">
			<div class="col-sm-12">
				<?php //echo CHtml::dropDownList('SkillAccount[trained][]', $skillAccountsArray['1'],CHtml::listData(Account::model()->byAccountTypeId(Account::TYPE_AGENT)->findAll(),'id','fullName'),array('multiple'=>'multiple') ); ?>
				
				<div class="row">
					<div class="col-sm-6">
						<label>Trained - Not Assigned</label>
						<ul id="sortable2" class="connectedSortable">
							<?php 
							
								$trainedAgents = AccountSkillChildTrained::model()->findAll(array(
									'condition' => 'skill_child_id = :skill_child_id',
									'params' => array(
										':skill_child_id' => $model->id,
									),
								));
							
								foreach($trainedAgents as $trainedAgent)
								{
									echo '<li class="ui-state-default" data-id="'.$trainedAgent->account->id.'" >'.$trainedAgent->account->accountUser->getfullName().'</li>';
								}
							?>
						</ul>
					</div>
					
					<div class="col-sm-6">
						<label>Trained - Assigned</label>
						<ul id="sortable1" class="connectedSortable">
							<?php 
							
							// $criteria = new CDbCriteria;
							// $criteria->compare('skill_id', $model->id);
							// $skillAccountList = CHtml::listData(SkillAccount::model()->findAll($criteria),'agent_id','agent_id');
							
							$assignedAgents = AccountSkillChildAssigned::model()->findAll(array(
								'condition' => 'skill_child_id = :skill_child_id',
								'params' => array(
									':skill_child_id' => $model->id,
								),
							));
							
							foreach( $assignedAgents as $assignedAgent)
							{
								echo '<li class="ui-state-default" data-id="'.$assignedAgent->account->id.'" >'.$assignedAgent->account->accountUser->getfullName().'</li>';
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
	
	<?php } ?>
	
		
	<div class="clearfix"></div>
	
	<div id="submit-btn-container" class="clearfix form-actions text-center js-container-toggle">
			<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-primary btn-xs')); ?>
	</div>
	
<?php $this->endWidget(); ?>

<?php  Yii::app()->clientScript->registerScript('select2js', '

	$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END); ?>

</div><!-- form -->