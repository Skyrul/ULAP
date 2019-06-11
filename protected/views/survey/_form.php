<?php
/* @var $this SurveyController */
/* @var $model Survey */
/* @var $form CActiveForm */

$cs = Yii::app()->clientScript;

$cs->registerScript(uniqid(), '

	$(".add-question").on("click", function() {
		
		bootbox.prompt("Question Name", function(result) {
			if (result === null) 
			{
				//Example.show("Prompt dismissed");
			} 
			else 
			{
				//Example.show("Hi <b>"+result+"</b>");
			}
		});
	});

', CClientScript::POS_END);

?>

<div class="tabbable tabs-left">
	<ul id="myTab3" class="nav nav-tabs">
		<li class="active">
			<a href="#tab1" data-toggle="tab">
				<i class="green ace-icon fa fa-wrench bigger-110"></i>
				General
			</a>
		</li>

		<li>
			<a href="#tab2" data-toggle="tab">
				<i class="blue ace-icon fa fa-comments bigger-110"></i>
				Questions
			</a>
		</li>
	</ul>
		
	<div class="tab-content">	
		<div id="tab1" class="tab-pane in active">
			<div class="form">
				<?php $form=$this->beginWidget('CActiveForm', array(
					'id'=>'survey-form',
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class'=>'form-horizontal',
					),
				)); ?>
			
					<?php echo $form->errorSummary($model); ?>
					
					<div class="form-group">
						<?php echo $form->labelEx($model,'name', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->textField($model,'name',array('class'=>'col-xs-10 col-sm-5', 'size'=>60,'maxlength'=>255)); ?>
							<?php echo $form->error($model,'name'); ?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo $form->labelEx($model,'description', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->textArea($model,'description',array('class'=>'col-xs-10 col-sm-5', 'size'=>60,'maxlength'=>255)); ?>
							<?php echo $form->error($model,'description'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $form->labelEx($model,'status', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->dropDownList($model,'status', array(1=>'Active', 2=>'Inactive')); ?>
							<?php echo $form->error($model,'status'); ?>
						</div>
					</div>	
					
					<div class="clearfix form-actions">
						<div class="col-md-offset-3 col-md-9">
							<button type="submit" class="btn btn-info btn-sm">
								<i class="ace-icon fa fa-check bigger-110"></i>
								Submit
							</button>
							
							<button type="reset" class="btn btn-sm">
								<i class="ace-icon fa fa-undo bigger-110"></i>
								Reset
							</button>
						</div>
					</div>
				
				<?php $this->endWidget(); ?>
			</div>
		</div>
		
		<div id="tab2" class="tab-pane">
			
			
			<table class="table table-striped table-hover table-bordered table-responsive">
				<thead>
					<th></th>
					<th>Question</th>
					<th>Options</th>
				</thead>
				
				<tbody>
					<?php 
						if($questions)
						{
							$ctr = 0;
							
							foreach($questions as $question)
							{
								$ctr++;
								
								echo '<tr>';
									echo '<td>'.$ctr.'</td>';
									echo '<td>'.$question->name.'</td>';
									echo '<td>'.CHtml::link('Options', array()).'</td>';
								echo '</tr>';
							}
						}
						else
						{
							echo '<tr><td colspan="3">No question added.</td></tr>';
						}
					?>
				</tbody>
			</table>
			
			<br />
		
			<button type="button" class="btn btn-success btn-xs add-question"><i class="fa fa-plus"></i> Add Question</button>
			
		</div>
	</div>
</div>