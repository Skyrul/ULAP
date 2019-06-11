<?php 
	$baseUrl = Yii::app()->request->baseUrl;

	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerCss(uniqid(), '
		.profile-info-name { width:150px !important; } 
		.profile-user-info { width:calc(100%) !important; }
		
		.percentage { font-size:12px; font-weight:normal; }
		.profile-info-name { width:150px !important; } 
		.profile-user-info { width:calc(100%) !important; }
	');
	
	$cs->registerScript(uniqid(), '
	
		$(document).ready( function(){
			
			//temporary fix to highlight setup tab on customer side menu
			$(".nav-tabs li:eq(2)").addClass("active");		
		});
	', CClientScript::POS_END);
	
?>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id != null ? Customer::model()->findByPk($customer_id) : null,
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
	<h1>Import Settings</h1>
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
		
		<div class="form">
			<?php $form=$this->beginWidget('CActiveForm', array(
				'enableAjaxValidation'=>false,
				'htmlOptions' => array(
					'class' => 'form-horizontal',
				),
			)); ?>

				<?php echo $form->hiddenField($model, 'customer_id'); ?>
				
				<div class="form-group">
					<?php echo $form->labelEx($model,'skill_id', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'skill_id', CustomerSkill::items($customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
						<?php echo $form->error($model,'skill_id'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($model,'calendar_id', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'calendar_id', Calendar::items($customer_id), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
						<?php echo $form->error($model,'calendar_id'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($model,'lead_ordering', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'lead_ordering', Lists::model()->getOrderingOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
						<?php echo $form->error($model,'lead_ordering'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($model,'language', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<div class="col-sm-9">
						<?php echo $form->dropDownList($model,'language', Lists::model()->getLanguageOptions(), array('class'=>'form-control', 'style'=>'width:auto;')); ?>
						<?php echo $form->error($model,'language'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<?php echo $form->labelEx($model,'duplicate_action', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
					
					<?php echo $form->hiddenField($model, 'duplicate_action'); ?>
					
					<div class="col-sm-9">
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::DUPLICATES_DO_NOT_IMPORT; ?>" <?php echo $model->duplicate_action == Lists::DUPLICATES_DO_NOT_IMPORT ? 'checked' : ''; ?> >
								<span class="lbl"> Do not import</span>
							</label> 
						</div>
						
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::DUPLICATES_UPDATE_LEAD_INFO; ?>" <?php echo $model->duplicate_action == Lists::DUPLICATES_UPDATE_LEAD_INFO ? 'checked' : ''; ?> >
								<span class="lbl"> Update lead info in database to match. Keep Call History</span>
							</label>
						</div>
						
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS; ?>" <?php echo $model->duplicate_action == Lists::DUPLICATES_UPDATE_LEAD_INFO_RESET_DIALS ? 'checked' : ''; ?> >
								<span class="lbl"> Update lead info in database to match. Keep Call History. Reset Dial Count</span>
							</label>
						</div>
						
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS; ?>" <?php echo $model->duplicate_action == Lists::MOVE_LEAD_TO_CURRENT_LIST_RESET_DIALS ? 'checked' : ''; ?> >
								<span class="lbl"> Move lead record to current list. Reset Dial Count</span>
							</label>
						</div>
						
						<?php if( Yii::app()->user->account->getIsAdmin() || Yii::app()->user->account->getIsCustomerService() || in_array(Yii::app()->user->account, array(1,2,3)) || (isset(Yii::app()->user->account->accountUser) && Yii::app()->user->account->accountUser->job_title == "Customer Service Agent") ): ?>
						 
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::CUSTOMER_SERVICE_OVERRIDE; ?>" <?php echo $model->duplicate_action == Lists::CUSTOMER_SERVICE_OVERRIDE ? 'checked' : ''; ?> >
								<span class="lbl"> Move lead record to current list. Reset dial count and make callable for all leads except Do Not Call</span>
							</label>
						</div>
						
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::CUSTOMER_SERVICE_ALLOW_DUPLICATES; ?>" <?php echo $model->duplicate_action == Lists::CUSTOMER_SERVICE_ALLOW_DUPLICATES ? 'checked' : ''; ?> >
								<span class="lbl"> Allow Duplicates. Keep Call History. Reset Dial Count</span>
							</label>
						</div>
						
						<?php endif; ?>
						
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::MOVE_RECERTIFIABLE_LEAD_TO_CURRENT_LIST; ?>" <?php echo $model->duplicate_action == Lists::MOVE_RECERTIFIABLE_LEAD_TO_CURRENT_LIST ? 'checked' : ''; ?> >
								<span class="lbl"> Import any leads that are currently recertifiable from other lists</span>
							</label>
						</div>
						
						<div class="radio">
							<label>
								<input type="radio" class="ace" name="CustomerListImportSettings[duplicate_action]" value="<?php echo Lists::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST; ?>" <?php echo $model->duplicate_action == Lists::MOVE_RECYCLABLE_LEAD_TO_CURRENT_LIST ? 'checked' : ''; ?> >
								<span class="lbl"> Import any leads that are currently recyclable from other lists</span>
							</label>
						</div>
					
						<?php echo $form->error($model,'duplicate_action'); ?>
					</div>
				</div>
				
				<div class="form-group">
					<label for="form-field-1" class="col-sm-3 control-label no-padding-right">Manually Enter</label>

					<div class="col-sm-9">
						<label style="margin:8px 0 0 8px;">
							<?php echo $form->checkBox($model,'manually_enter', array('class'=>'ace')); ?>
							<span class="lbl"> </span>
						</label>
					</div>
				</div>
				
				<div class="form-group">
					<label for="form-field-1" class="col-sm-3 control-label no-padding-right">
						Import from leads waiting <small class="red"></small> 
					</label>

					<div class="col-sm-9">
						<label style="margin:8px 0 0 8px;">
							<?php echo CHtml::checkBox('CustomerListImportSettings[import_from_leads_waiting]', false, array('class'=>'ace')); ?>
							<span class="lbl"> </span>
						</label>
					</div>
				</div>
				
				<div class="form-actions text-center">
					<button type="submit" class="btn btn-xs btn-primary create-list-submit-btn">Save <i class="fa fa-arrow-right"></i></button>
				</div>

			<?php $this->endWidget(); ?>
		</div>
		
	</div>
</div>