<div class="tabbable tabs-left">
	<ul id="myTab" class="nav nav-tabs">
		<li>
			<a href="<?php echo $this->createUrl('/hr/accountUser/index'); ?>">
				Employees
			</a>
		</li>

		<li>
			<a href="<?php echo $this->createUrl('/hr/team'); ?>">
				Teams
			</a>
		</li>
	</ul>
	<div class="tab-content">
		<div class="row">
			<div class="col-md-12">
				<div class="page-header">
					<h1>
						Update Position <small><?php echo $model->name; ?></small>
					</h1>
				</div>

				<?php $this->renderPartial('_positionForm', array('model'=>$model, 'fileupload'=>$fileupload)); ?>
			</div>
		</div>
	</div>
</div>