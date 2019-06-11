<?php 

$this->pageTitle = 'Engagex - Users';

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile( $baseUrl . '/template_assets/css/chosen.css');

$cs->registerScriptFile($baseUrl.'/js/hostDial/users.js?'.time(),  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fuelux/fuelux.spinner.min.js',  CClientScript::POS_END);

$cs->registerScriptFile( $baseUrl . '/template_assets/js/jquery.maskedinput.min.js');

$cs->registerScriptFile( $baseUrl . '/template_assets/js/chosen.jquery.min.js');

$cs->registerScript(uniqid(), '

	setInterval(function(){
		
		$(".spinner").ace_spinner({value:0,min:1,max:100,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"})
		.on("change", function(){
			if( this.value < 1)
			{
				this.value = 1;
			}
			
			if( this.value > 100)
			{
				this.value = 100;
			}
		});
		
		$(".spinner2").ace_spinner({value:0,min:2,max:60,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"})
		.on("change", function(){
			if( this.value < 1)
			{
				this.value = 1;
			}
			
			if( this.value > 100)
			{
				this.value = 100;
			}
		});
		
		$(".spinner3").ace_spinner({value:0,min:2,max:60,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"})
		.on("change", function(){
			if( this.value < 1)
			{
				this.value = 1;
			}
			
			if( this.value > 100)
			{
				this.value = 100;
			}
		});
		
	}, 500);
	
	
	$(".chosen").chosen({width: "95%"}); 
				
', CClientScript::POS_END);


$cs->registerCss(uniqid(), '
    .spinner-up, .spinner-down{ 
		font-size: 10px !important;
		height: 16px !important;
		line-height: 8px !important;
		margin-left: 0 !important;
		padding: 0 !important;
		width: 22px !important;
	}
	
	.row{ margin:3px 0; }
');

?>

<?php 
Yii::app()->clientScript->registerScript('js-calenderReceiveEmails'.uniqId(),'
	
	$("body").on("change",".js-is-received-email", function(){
		console.log("triggered");
		var thisVal = $(this).val();
		
		$(".calenderReceiveEmails-container").addClass("hidden");
		if(thisVal == 1)
		{
			$(".calenderReceiveEmails-container").removeClass("hidden");
		}
		else 
		{
			$(".js-calenderReceiveEmails").val("").trigger("chosen:updated");
		}
	});
',CClientScript::POS_END);
?>


<?php 
if(!empty($customer) && !$customer->isNewRecord){
	
	$this->widget("application.components.HostDialSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));

}

?>

<div class="page-header">
	<h1 class="bigger">
		<i class="fa fa-users"></i> Users
		<?php if( Yii::app()->user->account->checkPermission('customer_offices_add_new_staff_button','visible') && !Yii::app()->user->account->getIsHostManager() ){ ?>
			<a customer_office_id="<?php echo $office->id; ?>" customer_id="<?php echo $customer->id; ?>" class="btn btn-xs btn-primary add-staff-btn" style="border-radius:3px;">
				<i class="fa fa-plus"></i> Add
			</a>
		<?php } ?>
	</h1>
</div>

<div class="row">
	<div class="col-sm-6">
				
		<table class="table table-bordered table-condensed office-staff-tbl">
			<thead>
				<th>Name</th>
				<th width="25%" class="center">Options</th>
			</thead>
			<tbody>
				<?php 
					if($officeStaffs)
					{
						foreach( $officeStaffs as $officeStaff )
						{
							if( !empty($officeStaff->account_id) )
							{							
								$hasCalendarAssigned = CalendarStaffAssignment::model()->count(array(
									'condition' => 'staff_id = :staff_id',
									'params' => array(
										':staff_id' => $officeStaff->id,
									),
								));
								
								echo '<tr>';
									echo '<td>'.$officeStaff->staff_name.'</td>';
									echo '<td class="center">';
										echo CHtml::link('<i class="fa fa-edit"></i> Edit', array('customerOfficeStaff/update', 'id'=>$officeStaff->id, 'customer_id'=>$customer->id));
										
										echo '&nbsp;&nbsp;&nbsp;&nbsp;';
									
										if( !Yii::app()->user->account->getIsHostManager() )
										{
											echo CHtml::link('<i class="fa fa-times"></i> Delete', 'javascript:void(0);', array('id'=>$officeStaff->id, 'has_calendar_assigned'=>$hasCalendarAssigned, 'class'=>'delete-staff-btn'));
										}
									echo '</td>';
								echo '</tr>';
							}
						}
						
					}
					else
					{
						echo '<tr><td colspan="2">No staff found.</td></tr>';
					}
				?>
			</thead>
		</table>
				
	</div>
</div>