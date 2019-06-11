<?php 

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;
$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
$cs->registerCssFile( $baseUrl.'/template_assets/css/chosen.css'); 
$cs->registerScriptFile( $baseUrl.'/template_assets/js/chosen.jquery.min.js'); 
$cs->registerScriptFile($baseUrl . '/js/jquery.simplyCountable.js', CClientScript::POS_END);

$cs->registerCss(uniqid(), '

	div.form label { line-height:40px; }
	.itemdiv { min-height: 20px !important; }
	.profile-activity { cursor:pointer; }
');

$cs->registerScript(uniqid(), '

	$(".datepicker").datepicker({
		autoclose: true,
		todayHighlight: true
	});

	$(".scrollable").each(function () {
		var $this = $(this);
		$(this).ace_scroll({
			size: $this.data("size") || 500,
		});
	});
	
	$(".chosen-select").chosen({
		allow_single_deselect:true
	}); 

	$(document).on("click", ".profile-activity", function(){
		
		var id = $(this).prop("id");
		
		$.ajax({
			url: yii.urls.absoluteUrl + "/hr/texting/view",
			type: "post",
			dataType: "json",
			data: { "ajax":1, "id":id },
			success: function(response) {
				
				if(response.status  == "success")
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

$cs->registerScript(uniqid(), '
	
		$(document).ready(function() {

			$("#SmsEmployee_content").simplyCountable({
				counter: "#counter",
				countType: "characters",
				maxCount: 160,
				strictMax: true,
				countDirection: "up"
			});
		});
	
	', CClientScript::POS_END);

?>

<div class="tabbable tabs-left">
	<ul id="myTab" class="nav nav-tabs">
		<li class="<?php echo Yii::app()->getController()->getId() == 'accountUser' ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('/hr'); ?>">
				Employees
			</a>
		</li>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_permissions_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'permission' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/permission'); ?>">
					Permissions
				</a>
			</li>
		<?php } ?>

		<?php if( Yii::app()->user->account->checkPermission('employees_teams_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'team' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/team'); ?>">
					Teams
				</a>
			</li>
			
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_news_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'news' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/news'); ?>">
					News
				</a>
			</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('training_library_main_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'trainingLibrary' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/trainingLibrary'); ?>">
					Training Library
				</a>
			</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_texting_main_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'texting' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/texting'); ?>">
					Texting
				</a>
			</li>
		
		<?php } ?>
	</ul>
	<div class="tab-content">
		<div class="row">
			<div class="col-md-12">
				
				<?php
					foreach(Yii::app()->user->getFlashes() as $key => $message) {
						echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
					}
				?>

				<div class="row">
					<div class="grid2">
						<div class="widget-box transparent">
							<div class="widget-header">
								<h4 class="widget-title lighter smaller">
									Create New Text
								</h4>
							</div>
							
							<div class="widget-body" style="margin-top:10px;">
								<div class="widget-main">
									<div class="form">
										<?php $form=$this->beginWidget('CActiveForm', array(
											'id'=>'texting-form',
											'enableAjaxValidation'=>false,
											'htmlOptions'=>array(
												'class'=> 'form-horizontal',
											),
										)); ?>

											<div class="form-group">
												<?php echo $form->labelEx($model,'security_groups', array('class'=>'col-sm-3 text-right')); ?>
												
												<div class="col-sm-9">
													<?php echo CHtml::dropDownList('SmsEmployee[securityGroupIds]', array(), $securityGroups, array('class'=>'col-sm-12 chosen-select', 'multiple'=>true, 'data-placeholder'=>'Select a security group...') ); ?>
													<?php echo $form->error($model,'security_groups'); ?>
												</div>	
											</div>

											<div class="form-group">
												<?php echo $form->labelEx($model,'description', array('class'=>'col-sm-3 text-right')); ?>
												
												<div class="col-sm-9">
													<?php echo $form->textField($model,'description',array('class' => 'col-sm-12')); ?>
													<?php echo $form->error($model,'description'); ?>
												</div>	
											</div>
											
											<div class="form-group">
												<label class="col-sm-3 text-right">Schedule Send Date</label>
												
												<div class="col-sm-9">
													<div class="pull-left">
														<div class="input-group">
															<span class="input-group-addon">
																<i class="fa fa-calendar bigger-110"></i>
															</span>
															<input type="text" name="scheduleSendDate" class="form-control datepicker" value="" placeholder="<?php echo date('m/d/Y'); ?>" style="width:100px;margin-top:0;">
														</div>
													</div>

													<div class="pull-left" style="margin-left:5px;">
														<div class="input-group">	
															<span class="input-group-addon">
																<i class="fa fa-clock-o bigger-110"></i>
															</span>
															<input type="text" name="scheduleSendTime" class="form-control" value="" placeholder="<?php echo date('g:i A'); ?>" style="width:75px;margin-top:0;">
														</div>
													</div>
												</div>
											</div>
											
											<div class="form-group">
												<?php echo $form->labelEx($model,'content', array('class'=>'col-sm-3 text-right')); ?>
												
												<div class="col-sm-9">
													<?php echo $form->textArea($model,'content',array('class' => 'col-sm-12', 'style'=>'min-height:300px; height:300px;')); ?>
													
													<p><span id="counter"></span> characters /160</p>
													
													<?php echo $form->error($model,'content'); ?>
												</div>	
											</div>
											
											<div class="form-group center">
												<div class="col-sm-3"></div>
												<div class="col-sm-9">
													<div class="col-sm-6">
														<button type="submit" name="submitBtn" class="btn btn-sm btn-success" style="width: 120px;">
															Send
														</button>
													</div >
													
													<div class="col-sm-6">
														<button type="submit" name="scheduleSendSubmitBtn" class="btn btn-sm btn-primary" style="width: 120px;">
															Schedule Send
														</button>
													</div>
												</div>
											</div>
										
										<?php $this->endWidget(); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					
					<div class="grid2">
						<div class="widget-box transparent">
						
							<div class="widget-header">
								<h4 class="widget-title lighter smaller">
									Text History
								</h4>
							</div>
							
							<div class="widget-body">
								<!-- #section:pages/dashboard.conversations -->
								<div class="dialogs scrollable">
									<div class="profile-feed" style="height:500px; overflow:auto;">
										<?php 
											$this->widget('zii.widgets.CListView', array(
												'id'=>'textHistoryList',
												'dataProvider'=>$dataProvider,
												'itemView'=>'_list',
												'template'=>'<div class="profile-feed">{items}</div>',
											)); 
										?>
									</div>
								</div>
							</div>
							
							<p><i>Click record to view each user/phone number that it was sent to</i></p>
						</div>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>
