<?php 
	$cs = Yii::app()->clientScript;
	$baseUrl = Yii::app()->request->baseUrl;
?>

<?php $cs->registerScriptFile($baseUrl.'/js/select2.min.js'); ?>
<?php $cs->registerCssFile($baseUrl.'/css/select2.min.css'); ?>

<?php 
	$cs->registerScript(uniqid(), '
		
		$(document).on("click", ".btn-skill-history", function(){
			
			skill_id = $(this).attr("skill_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/skill/history",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "skill_id":skill_id },
				success: function(response) {

					if(response.html  != "" )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo("body");

					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
			
		});
	
	', CClientScript::POS_END);
?>

<?php
	Yii::app()->clientScript->registerScript('tababbleScript','
	
		$("#myTab3 a").click(function (e) {
			
			if(!($(this).parent().hasClass("link")))
			{
				e.preventDefault()
				$(this).tab("show");
			}
		});
	', CClientScript::POS_END);
 ?>
 
<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<button class="btn btn-yellow btn-sm pull-right btn-skill-history" skill_id="<?php echo $model->id; ?>"><i class="fa fa-history"></i> History</button>
	<h1>Update <small>&raquo; <?php echo $model->skill_name; ?></small></h1>
</div>


<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '<div class="flash-' . $key . '">' . $message . "</div>\n";
    }
?>

<div class="tabbable tabs-left">
	<ul id="myTab3" class="nav nav-tabs">
		
		<li data-toggle="tab" class="<?php echo empty($tab) ? 'active' : ''; ?>">		
			<a href="#skill-setup">Setup</a>
		</li>
		
		<?php if($model->enable_email_setting == 1){ ?>
		<li data-toggle="tab" class="<?php echo !empty($tab) && ($tab == 'emailSetting' || $tab == 'emailSettingCreate' || $tab == 'emailSettingUpdate') ? 'active' : ''; ?>">
			<a href="#email-settings">Email Settings</a>
		</li>
		
		<li data-toggle="tab" class="<?php echo !empty($tab) && $tab == 'emailSettingAttachment' ? 'active' : ''; ?>">
			<a href="#attachments">Attachments</a>
		</li>
		<?php } ?>
		
		
	</ul>
	
	<div class="tab-content">		
		<div role="tabpanel" class="tab-pane <?php echo empty($tab) ? 'active' : ''; ?>" id="skill-setup">
			<?php $this->renderPartial('_form', array(
				'model'=>$model,
				'skillAccountsArray'=>$skillAccountsArray,
				'selectedSkillCompany'=>$selectedSkillCompany,
				'selectedServiceTab'=>$selectedServiceTab,
			)); ?>
		</div>
		
		<div role="tabpanel" class="tab-pane <?php echo !empty($tab) && $tab == 'emailSetting' ? 'active' : ''; ?>" id="email-settings">
			<?php $this->forward('/admin/skill/emailSetting/id/'.$model->id,false); ?>
		</div>
		
		<?php if($tab == 'emailSettingCreate'){ ?>
			<div role="tabpanel" class="tab-pane <?php echo !empty($tab) && $tab == 'emailSettingCreate' ? 'active' : ''; ?>" id="email-settings-create">
				<?php $this->forward('/admin/skill/emailSettingCreate/id/'.$model->id,false); ?>
			</div>
		
		<?php } ?>
		
		<?php if($tab == 'emailSettingUpdate'){ ?>
			<div role="tabpanel" class="tab-pane <?php echo !empty($tab) && $tab == 'emailSettingUpdate' ? 'active' : ''; ?>" id="email-settings-update">
				<?php $this->forward('/admin/skill/emailSettingUpdate/id/'.$model->id,false); ?>
			</div>
		
		<?php } ?>
		
		<div role="tabpanel" class="tab-pane <?php echo !empty($tab) && $tab == 'emailSettingAttachment' ? 'active' : ''; ?>" id="attachments">
			
			
			<div class="form-group">
				<div class="col-sm-6">
				
					<table class="table table-striped table-condensed table-hover">	
						<tr>
							<th>Email Attachments</th>
							<th>Options</th>
						</tr>
	
							<?php 
								if( !empty($attachments ))
								{
									foreach( $attachments as $attachment )
									{
										?>
										<tr>
											<td><?php echo CHtml::link($attachment->fileUpload->original_filename, array('/site/download', 'file'=>$attachment->fileUpload->original_filename), array('target'=>'_blank')); ?> </td>
											
											
											<td>
												<?php
														echo CHtml::link('<i class="fa fa-times"></i> Delete',array('skill/deleteEmailAttachment', 'id'=>$model->id, 'attachment_id'=>$attachment->id,'tab'=>'emailSettingAttachment'),array('class'=>'btn btn-xs btn-danger btn-minier', 'confirm'=>'Are you sure you want to delete this?')); 
												?>
											</td>
											
										</tr>
									<?php
									}
								}
								else
								{
									echo '<tr><td colspan="2">No attachments</td></tr>';
								}
							?>
					
					</table>
				</div>
			</div>
		</div>
		
		
	</div>
</div>



		
