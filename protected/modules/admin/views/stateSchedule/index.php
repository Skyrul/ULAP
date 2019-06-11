<?php
	$this->pageTitle = 'Engagex - State Schedule';
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> 'stateSchedule'
	));
?>

<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;
	
	$cs->registerCss(uniqid(), '
		.state-link { color: #333333; }
	');
?>

<div class="page-header">
	<h1>State Schedule</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		<form action="" method="post">
			<?php
				if( $states )
				{
					foreach( $states as $state )
					{
						echo '
							<div class="col-sm-2">
								<div class="checkbox">
									<label>
										<span class="lbl"> <a class="state-link" href="'.$this->createUrl('update', array('state_id'=>$state->id)).'">'.$state->name.' ('.$state->abbreviation.')</a></span>
									</label>
								</div>
							</div>
						';
					}
				}
				
			?>
		</form>
	</div>
</div>