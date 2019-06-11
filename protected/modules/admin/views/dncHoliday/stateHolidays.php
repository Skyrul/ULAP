<?php
	$this->pageTitle = 'Engagex - DNC State Holidays';
?>

<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
	
	$cs->registerScriptFile($baseUrl . '/js/admin/dncHoliday/stateHolidays.js?time='.time());
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active' => 'stateHolidays'
	));
?>

<div class="alert alert-block alert-success" style="display:none;">
	<button type="button" class="close alert-close">
		<i class="ace-icon fa fa-times"></i>
	</button>

	<div class="alert-message"></div>
</div>

<div class="page-header">
	<h1>
		DNC STATE HOLIDAYS
		<button class="btn btn-sm btn-success btn-add-state-holiday"><i class="fa fa-plus"></i> Add</button>
	</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		
		<table class="table table-striped table-bordered table-condensed table-hover">
			<thead>
				<th width="15%"></th>
				<th>State</th>
				<th>Holiday</th>
				<th>Date</th>
				<th>Audit (Dials Made)</th>
			</thead>
			<tbody>
				<?php 
					if( $models )
					{
						foreach( $models as $model )
						{
						?>
						
							<tr>
								<td class="center">
									<button id="<?php echo $model->id; ?>" class="btn btn-minier btn-primary btn-edit-state-holiday"><i class="fa fa-pencil"></i> Edit</button>
									<button id="<?php echo $model->id; ?>" class="btn btn-minier btn-danger btn-delete-state-holiday"><i class="fa fa-times"></i> Delete</button>
								</td>
								<td><?php echo State::model()->findByPk($model->state)->name; ?></td>
								<td class="model_name"><?php echo $model->name; ?></td>
								<td><?php echo date('m/d/Y', strtotime($model->date)); ?></td>
								<td class="center"> <?php echo number_format($model->dials); ?></td>
							</tr>
						
						<?php
						}
					}
				?>
			</tbody>
		</table> 

	</div>
</div>

