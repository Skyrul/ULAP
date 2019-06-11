<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/setup_customer_tiers.js',CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/tier.css'); ?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/select2.min.js'); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/select2.min.css'); ?>

<?php 
	Yii::app()->clientScript->registerScript('select2js', '

		$(".select2").css("width","300px").select2({allowClear:true});

', CClientScript::POS_END);
 ?>

<?php Yii::app()->clientScript->registerScript(uniqid(), "
	
		var customer_name = '".addslashes($model->getFullName())."';
	",CClientScript::POS_END);
	

?>

<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/voice/recorder.js?time='.time(),CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/voice/jquery.voice.min.js?time='.time(),CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/voice/record.js?time='.time(),CClientScript::POS_END); ?>

<style>
      .button{
        display: inline-block;
        vertical-align: middle;
        margin: 0px 5px;
        padding: 5px 12px;
        cursor: pointer;
        outline: none;
        font-size: 13px;
        text-decoration: none !important;
        text-align: center;
        color:#fff;
        background-color: #4D90FE;
        background-image: linear-gradient(top,#4D90FE, #4787ED);
        background-image: -ms-linear-gradient(top,#4D90FE, #4787ED);
        background-image: -o-linear-gradient(top,#4D90FE, #4787ED);
        background-image: linear-gradient(top,#4D90FE, #4787ED);
        border: 1px solid #4787ED;
        box-shadow: 0 1px 3px #BFBFBF;
      }
      a.button{
        color: #fff;
      }
      .button:hover{
        box-shadow: inset 0px 1px 1px #8C8C8C;
      }
      .button.disabled{
        box-shadow:none;
        opacity:0.7;
      }
  </style>	
	  

<?php
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	$cs->registerCssFile($baseUrl . '/css/extra.css');
	
	$cs->registerCss(uniqid(), '
		.redactor-toolbar {
			background: #438EB9;
			box-shadow: none;
		}
		.redactor-toolbar li a {
			color: rgba(255, 255, 255, .55);
		}
		.redactor-toolbar li a:hover {
			background: #2C5976;
			color: #fff;
		}
		
		.tab-content { overflow:hidden !important; }
		
		span.filename > a { text-decoration:none; }
	');
	

	$inputDisabled = false;
?>


<!--- MODAL --->


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<!-- END OF MODAL -->


<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'customer-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
	'htmlOptions'=>array(
		'class'=> 'form-horizontal',
		'enctype' => 'multipart/form-data',
	),
)); ?>

	<div class="row">
		<div class="col-md-7">

			<?php echo $form->errorSummary($model); ?>
			
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
			
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'company_id'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->dropDownList($model,'company_id',Company::listCompanies(),array('empty'=>'-Select Company-', 'disabled' => true)); ?>
								<?php echo $form->error($model,'company_id'); ?>
							</div>
						</div>
					</div>
					
					
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'custom_customer_id'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'custom_customer_id',array('maxlength'=>10, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'custom_customer_id'); ?>
							</div>
						</div>
					</div>
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'firstname'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'firstname',array('maxlength'=>120, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'firstname'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'lastname'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'lastname',array('maxlength'=>120, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'lastname'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'phone'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'phone',array('maxlength'=>128, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'phone'); ?>
							</div>
						</div>
					</div>

				
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'email_address'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'email_address',array('maxlength'=>128, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'email_address'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'address'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'address',array('maxlength'=>250, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'address'); ?>
							</div>
						</div>
					</div>


					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'city'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'city',array('maxlength'=>64, 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'city'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'state'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->dropDownList($model,'state',State::listStates(),array('empty'=>'-Select State-', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'state'); ?>
							</div>
						</div>
					</div>

					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'zip'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'zip',array('size'=>12,'maxlength'=>12, 'class'=>'input-mask-zip', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'zip'); ?>
							</div>
						</div>
					</div>
			
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name "><?php echo $form->labelEx($model,'tier_level'); ?></div>
							<div class="profile-info-value">
								<?php echo $form->textField($model,'tier_level',array('size'=>12,'maxlength'=>12, 'class'=>'input-mask-zip', 'disabled'=>$inputDisabled)); ?>
								<?php echo $form->error($model,'tier_level'); ?>
							</div>
						</div>
					</div>
					
			<div class="form-actions buttons center">
				<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save',array('class'=>'btn btn-sm btn-primary')); ?>
			</div>
		</div>
	</div>
<?php $this->endWidget(); ?>

</div><!-- form -->

