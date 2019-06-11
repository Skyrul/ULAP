<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
	$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.nestable.min.js' );
	
	$cs->registerScript('sortable-script','
		
		$(".dd").nestable().nestable({}).on("change", function() {
			var jsondata = $(this).nestable("serialize");

			$.ajax({
				url: "'.Yii::app()->createUrl('/hr/orgChart/updatePositionOrder').'",
				type: "post",	
				dataType: "json",	
				 data : {"items": jsondata},
				success: function(r) {
					
				},
			});
			
		});
				
		$(".dd-handle a").on("mousedown", function(e){
			e.stopPropagation();
		});

',CClientScript::POS_END);
 ?>
 
<style>
	#sortable {
		border: 1px solid #eee;
		width: 100%;
		min-height: 40px;
		list-style-type: none;
		margin: 0;
		padding: 5px 0 0 0;
		margin-right: 10px;
	}
	#sortable li{
		margin: 0 5px 5px 5px;
		padding: 5px;
		font-size: 1.2em;
		width: 95%;
	}
</style>
 
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
				<?php
					foreach(Yii::app()->user->getFlashes() as $key => $message) {
						echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
					}
				?>
			
				<div class="page-header">
					<h1>
						Employees
						<?php echo CHtml::link('<i class="fa fa-plus"></i> Add Employee',array('create'),array('class'=>'btn btn-sm btn-primary')); ?> <br><br>
					</h1>
				</div>
				
				<?php 
					function showNested( $parentID )  
					{
						$models = Position::model()->findAll(array(
							'with' => 'account',
							'condition' => 'parent_id = :parent_id AND account.status=1',
							'params' => array(
								'parent_id' => $parentID
							),
						));

						if ( $models ) 
						{
							echo '<ol class="dd-list">';
								
								foreach( $models as $model )
								{ 
									echo '<li class="dd-item" data-id="'.$model->id.'">';
									
										echo '<div class="dd-handle">';
											echo $model->account->accountUser->job_title.' - '.$model->account->accountUser->getFullName();
											echo '<div class="pull-right action-buttons">';
												echo CHtml::link('<i class="ace-icon fa fa-pencil bigger-130"></i>', array('/hr/accountUser/employeeProfile', 'id'=>$model->account_id), array('class'=>'blue', 'title'=>'Edit'));
												
												echo CHtml::link('<i class="ace-icon fa fa-times bigger-130"></i>', array('delete', 'id'=>$model->id), array('class'=>'red', 'title'=>'Delete', 'confirm'=>'Are you sure you want to delete this?'));
											echo '</div>';
										echo '</div>';
										
										showNested( $model->id );  
									echo '</li>';
								}
							
							echo '</ol>';
						}
					}
					

					echo '<div class="dd" id="nestable">';
					
						echo '<ol class="dd-list">';
						
							if( $models )
							{
								foreach( $models as $model )
								{    
									echo '<li class="dd-item" data-id="'.$model->id.'">';
									
										echo '<div class="dd-handle">';
											echo $model->account->accountUser->job_title.' - '.$model->account->accountUser->getFullName();
											echo '<div class="pull-right action-buttons">';
												echo CHtml::link('<i class="ace-icon fa fa-pencil bigger-130"></i>', array('/hr/accountUser/employeeProfile', 'id'=>$model->account_id), array('class'=>'blue', 'title'=>'Edit'));
												
												echo CHtml::link('<i class="ace-icon fa fa-times bigger-130"></i>', array('delete', 'id'=>$model->id), array('class'=>'red', 'title'=>'Delete', 'confirm'=>'Are you sure you want to delete this?'));
											echo '</div>';
										echo '</div>';
										
										showNested( $model->id );  
									echo '</li>';
								}   
							}   
							
						echo '</ol>';
						
					echo '<div>';
				?>
	
			</div>
		</div>
	</div>
</div>