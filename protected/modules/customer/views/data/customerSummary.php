<?php 
	Yii::app()->clientScript->registerScript('iconJs','
		$("#icon-calendar").on("click",function(){
			$("#myModalAppointment").modal();			
		});
		
		$("#icon-recycle").on("click",function(){
			$("#myModalRecyle").modal();			
		});
		
		$("#icon-phone").on("click",function(){
			$("#myModalPhone").modal();			
		});
	
	',CClientScript::POS_END);
?>

<?php 
$this->widget("application.components.CustomerSideMenu",array(
	'active'=> Yii::app()->controller->id,
	'customer' => $customer,
));
?>


<!-- Modal -->
<div class="modal fade" id="myModalPhone" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Remaining Callable</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<!-- END OF MODAL -->

<!-- Modal -->
<div class="modal fade" id="myModalRecyle" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Recycle Names</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<!-- END OF MODAL -->

<!-- Modal -->
<div class="modal fade" id="myModalAppointment" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Appointment Sets</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<div class="page-header">
	<h1><?php echo $customer->fullName; ?></h1>
</div>
<!-- END OF MODAL -->
<div class="page-header text-center">
	<div class="row">
		<div class="col-md-4">
			<div style="position: relative; width: auto; letter-spacing: 0px; display: inline;">
				<a href="javascript:void(0);" id="icon-calendar">
					<i style="font-size: 70px;" class="fa fa-calendar-o blue"></i>
					<span style="position: absolute; left: 25px; top: -10px;">18</span>
				</a>
			</div>
			<h5>Appointments Set</h5>
		</div>
		
		<div class="col-md-4">
			<div style="position: relative; width: auto; letter-spacing: 0px; display: inline;">
				<a href="javascript:void(0);" id="icon-recycle">
					<i style="font-size: 70px;" class="fa fa-recycle green"></i>
					<span style="position: absolute; left: 24px; top: -22px;">123</span>
				</a>
			</div>
			<h5>Recycle Names</h5>
		</div>
		
		<div class="col-md-4">
			<div style="position: relative; width: auto; letter-spacing: 0px; display: inline;">
				<a href="javascript:void(0);" id="icon-phone">
					<i style="font-size: 70px;" class="fa fa-phone red"></i>
					<span style="position: absolute; left: 25px; top: -30px;">47</span>
				</a>
			</div>
			<h5>Remaining Callable</h5>
		</div>
	</div>
	
</div>


			<h3>Action Center</h3>
			<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
			  <?php /*<div class="panel panel-default">
				<div class="panel-heading" role="tab" id="headingOne">
				  <h4 class="panel-title">
					<a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
						Appointment
					</a>
				  </h4>
				</div>
				<div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
				  <div class="panel-body">
					....
				  </div>
				</div>
			  </div> */ ?>
			  <div class="panel panel-default">
				<div class="panel-heading" role="tab" id="headingTwo">
				  <h4 class="panel-title">
					<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
					  Schedule Conflict
					</a>
				  </h4>
				</div>
				<div id="collapseTwo" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingTwo">
				  <div class="panel-body">
					...
				  </div>
				</div>
			  </div>
			  <div class="panel panel-default">
				<div class="panel-heading" role="tab" id="headingThree">
				  <h4 class="panel-title">
					<a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
					 Location Conflict
					</a>
				  </h4>
				</div>
				<div id="collapseThree" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingThree">
				  <div class="panel-body">
					...
				  </div>
				</div>
			  </div>
			</div>