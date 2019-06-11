<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					Create New List
					<small>
						<i class="ace-icon fa fa-angle-double-right"></i>
						<a class="orange" href="">Download list template</a>
					</small>
				</h4>
			</div>
			
			<div class="modal-body">
			
				<?php $form=$this->beginWidget('CActiveForm', array(
					'enableAjaxValidation'=>false,
					'htmlOptions' => array(
						'class' => 'form-horizontal',
					),
				)); ?>
				
					<div class="form-group">
						<?php echo $form->labelEx($model,'name', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->textField($model,'name',array('class'=>'form-control')); ?>
							<?php echo $form->error($model,'name'); ?>
						</div>
					</div>
															
				
					<div class="form-group">
						<?php echo $form->labelEx($model,'description', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->textArea($model,'description',array('class'=>'form-control col-xs-12')); ?>
							<?php echo $form->error($model,'description'); ?>
						</div>
					</div>

					<div class="form-group">
						<?php echo $form->labelEx($model,'skill_id', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->dropDownList($model,'skill_id', array(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
							<?php echo $form->error($model,'skill_id'); ?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo $form->labelEx($model,'calendar_id', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->dropDownList($model,'calendar_id', Calendar::items(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
							<?php echo $form->error($model,'calendar_id'); ?>
						</div>
					</div>
					
					<div class="form-group">
						<?php echo $form->labelEx($model,'lead_ordering', array('class'=>'col-sm-3 control-label no-padding-right')); ?>
						
						<div class="col-sm-9">
							<?php echo $form->dropDownList($model,'lead_ordering', $model::getOrderingOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
							<?php echo $form->error($model,'lead_ordering'); ?>
						</div>
					</div>
					
					<div class="space-12"></div>
					
					<div class="form-group">
						<label for="form-field-1" class="col-sm-3 control-label no-padding-right"></label>

						<div class="col-sm-9">
							<input type="file">
						</div>
					</div>
					
					<div class="form-group">
						<label for="form-field-1" class="col-sm-3 control-label no-padding-right"></label>

						<div class="col-sm-9">
							<label>
								<?php echo $form->checkBox($model,'manually_enter', array('class'=>'ace')); ?>
								<span class="lbl"> Manually Enter</span>
							</label>
						</div>
					</div>

				<?php $this->endWidget(); ?>
			</div>
			
			<div class="modal-footer">
				<div class="center">
					<button type="button" class="btn btn-sm btn-primary btn-white" data-action="save">Submit</button>
				</div>
			</div>
		</div>
	</div>
</div>