<?php 

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile( $baseUrl . '/template_assets/css/chosen.css');

$cs->registerScriptFile($baseUrl.'/js/calendar/customer_manage_calendar.js?'.time(),  CClientScript::POS_END);

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
	
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));

}

?>

<div class="page-header">
	<h1 class="bigger">Offices</h1>
</div>

<div class="row">
	<div class="col-sm-12">
				
		<div class="tabbable tabs-left">
			<ul id="myTab3" class="nav nav-tabs">
				
				<?php						
					$ctr = 1;
					
					if( $offices )
					{
						foreach( $offices as $office )
						{
							$active = $ctr == 1 ? 'active' : '';								
						?>
							<li id="<?php echo $office->id; ?>" class="<?php echo $active; ?>">
								<a href="#office<?php echo $office->id; ?>" data-toggle="tab">
									<?php echo $office->office_name; ?>
								</a>
							</li>
						<?php
						
						$ctr++;
						}
					}
				?>
				
				<?php if( Yii::app()->user->account->checkPermission('customer_offices_add_office','visible') ){ ?>
				
					<li customer_id="<?php echo $customer->id; ?>" class="add-office-btn">
						<a href="javascript:void(0);">
							<i class="fa fa-plus"></i> Add Office 
						</a>
					</li>
					
				<?php } ?>
			</ul>

			<div class="tab-content office-tab-content" style="display:<?php echo count($offices) > 0 ? 'block' : 'none'; ?>;">
			
				<?php
					$ctr = 1;
					
					if( $offices )
					{
						foreach( $offices as $office )
						{
							$active = $ctr == 1 ? 'active' : '';		
							
							$calendars = Calendar::model()->findAll(array(
								'condition' => 'office_id = :office_id AND status=1',
								'params' => array(
									':office_id' => $office->id,
								),
							));
							
							$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
								'condition' => 'customer_id = :customer_id AND customer_office_id = :customer_office_id AND is_deleted=0',
								'params' => array(
									':customer_id' => $customer->id,
									':customer_office_id' => $office->id,
								),
							));
							?>

							<div class="tab-pane fade in <?php echo $active; ?>" id="office<?php echo $office->id; ?>">

								<?php 
									$this->renderPartial( ($ctr != 1 && $ctr <= 3 && ( count($officeStaffs) == 0 || count($calendars) == 0 ) ) ? 'index_get_started_layout' : 'index_standard_layout', array(
										'ctr' => $ctr,
										'office' => $office,
										'officeStaffs' => $officeStaffs,
										'customer' => $customer,
										'calendars' => $calendars,
									)); 
									
									// $this->renderPartial( ($ctr != 1 && $ctr <= 3) ? 'index_get_started_layout' : 'index_standard_layout', array(
										// 'ctr' => $ctr,
										// 'office' => $office,
										// 'officeStaffs' => $officeStaffs,
										// 'customer' => $customer,
										// 'calendars' => $calendars,
									// )); 
								?>
								
							</div>
							
						<?php
						$ctr++;
						}
					}
				?>

			</div>
		</div>
		
	</div>
</div>