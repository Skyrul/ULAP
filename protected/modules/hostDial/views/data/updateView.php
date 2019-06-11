<?php
/* @var $this CustomerController */
/* @var $model Customer */

// $this->breadcrumbs=array(
	// 'Customers'=>array('index'),
	// $model->id=>array('view','id'=>$model->id),
	// 'Update',
// );

$this->widget("application.components.HostDialSideMenu",array(
		'active'=> 'customer',
		'customer' => $model,
));

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScript(uniqid(), "
	$.mask.definitions['~']='[+-]';
	$('.input-mask-phone').mask('(999) 999-9999');
	$('.input-mask-zip').mask('99999');
	
	$('#Customer_custom_customer_id').mask('?**-****',{
		completed:function(){ 
			$('#Customer_custom_customer_id').val(this.val().toUpperCase()); 
		},
		autoclear: false
	});
	
	$('#Customer_custom_customer_id').on('blur',function(){
		$('#Customer_custom_customer_id').val($(this).val().toUpperCase()); 
	});
	
	//temporary fix to highlight setup tab on customer side menu
	$('#yw0 li:eq(6)').addClass('active');
", CClientScript::POS_END);
?>

<div class="row">
	<div class="col-sm-12">
		<div class="row">
			<div class="page-header">
				<h1>Customer Information</h1>
			</div>
		</div>

		<div class="row">
			<div class="col-sm-12">
			
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Photo</div>
						
						<div class="profile-info-value">
							<div class="row">
								<div class="row">
									<div id="photo-container" class="col-sm-1 center">
										<?php 
											if($model->getImage())
											{
												echo CHtml::image($model->getImage(), '', array('style'=>'/* width:100%; */'));
											}
											else
											{
												echo 'No image uploaded yet.'; 
											}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name ">Voice</div>
						
						<div class="profile-info-value">
							<div class="row">
								<div class="row">
									<div id="voice-container" class="col-sm-6">
										<?php 
											if($model->getVoice())
											{
												echo '<audio controls src="'.$model->getVoice().'" id="audio"></audio>';
											}
											else
											{
												echo 'No voice uploaded yet.'; 
											}
										?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
						
						
				
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name "><label>Property Name</label></div>
						<div class="profile-info-value">
							<?php echo $model->firstname; ?>
						</div>
					</div>
				</div>
				
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name "><label>Primary Contact</label></div>
						<div class="profile-info-value">
							<?php echo $model->lastname; ?>
						</div>
					</div>
				</div>
					
				<div class="profile-user-info profile-user-info-striped">
					<div class="profile-info-row">
						<div class="profile-info-name "><label>Customer Notes to Agent</label></div>
						<div class="profile-info-value">
							<div style="width:350px;">
								<?php echo $model->notes; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		
	</div>
</div>