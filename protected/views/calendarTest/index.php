<?php 

$baseUrl = Yii::app()->request->baseUrl;

$cs = Yii::app()->clientScript;

$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } ');

$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/js/calendar/calendarTest.js?'.time(),  CClientScript::POS_END);

$cs->registerScriptFile($baseUrl.'/template_assets/js/fuelux/fuelux.spinner.min.js',  CClientScript::POS_END);

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
	
	.external-event:hover{
		background-color: #5B5B5B;
	}
	
	
	.dropdown-submenu {
		position: relative;
	}

	.dropdown-submenu>.dropdown-menu {
		top: 0;
		left: 100%;
		margin-top: -6px;
		margin-left: -1px;
		-webkit-border-radius: 0 6px 6px 6px;
		-moz-border-radius: 0 6px 6px;
		border-radius: 0 6px 6px 6px;
	}

	.dropdown-submenu:hover >.dropdown-menu {
		display: block;
	}

	.dropdown-submenu > div:after {
		display: block;
		content: " ";
		float: right;
		width: 0;
		height: 0;
		border-color: transparent;
		border-style: solid;
		border-width: 5px 0 5px 5px;
		border-left-color: #ccc;
		margin-top: 5px;
		margin-right: -10px;
	}
	
	.dropdown-submenu.pull-left {
		float: none;
	}

	.dropdown-submenu.pull-left >.dropdown-menu {
		left: -100%;
		margin-left: 10px;
		-webkit-border-radius: 6px 0 6px 6px;
		-moz-border-radius: 6px 0 6px 6px;
		border-radius: 6px 0 6px 6px;
	}
	
	.dropdown-menu div {
		text-align:center;
	}
	
	.dropdown-menu div span { 
		padding: 0 10px;
	}
	
	.dropdown-menu div span:hover { 
		cursor:pointer;
		color:#FFFFFF;
		background-color:#4F99C6;
	}
');

$cs->registerScript(uniqid(), '
	var current_lead_id = "";
	var current_calendar_id = "'.$calendar_id.'";
	var customer_id = "'.$customer_id.'";
	var viewer = "customer";
	
', CClientScript::POS_HEAD);

$cs->registerScript(uniqid(), '

	$(document).ready( function(){
		
		$(document).on("change", "#load-office-select", function(){

			var office_id = $(this).val();
			
			if( office_id != "" )
			{
				$.ajax({
					url: yii.urls.absoluteUrl + "/calendar/updateCalendarOptions",
					type: "post",
					dataType: "json",
					data: { "office_id": office_id },
					success: function(response) {
						if( response.status == "success" )
						{		
							$("#load-calendar-select").html( response.html );
						}
					}
				});
			}
			
		});
		
		$(document).on("click", ".load-calendar", function(){
			
			var calendar_id = $("#load-calendar-select").val();
			
			if( calendar_id != "" )
			{
				$(location).attr("href", yii.urls.absoluteUrl + "/calendar/index?calendar_id=" + calendar_id + "&customer_id=" + customer_id);
			}
			
		});
		
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
			
			$(".spinner2").ace_spinner({value:0,min:1,max:60,step:1, btn_up_class:"btn-info" , btn_down_class:"btn-info"})
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
		
		
		$("[data-rel=tooltip]").tooltip();
	});
	
', CClientScript::POS_END);



?>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'calendar_page',
		'customer' => isset($_REQUEST['customer_id']) ? Customer::model()->findByPk($_REQUEST['customer_id']) : null,
	));
?>

<div class="page-header">
	<h1>
		Calendar 
		<small>
			<i class="ace-icon fa fa-angle-double-right"></i> 
			<?php 
				if( $calendar_id != null )
				{
					echo $model->name;
				}
				else
				{
					echo 'No calendar selected.';
				}
			?>
		</small>

		<button id="<?php echo $model->id; ?>" class="btn btn-xs btn-white btn-primary btn-round pull-right calendar-settings">
			<i class="ace-icon fa fa-cog"></i>
			Settings
		</button>

		<button id="<?php echo $model->id; ?>" class="btn btn-xs btn-white btn-primary btn-round pull-right manage-schedule" style="margin-right:8px;">
			<i class="ace-icon fa fa-calendar"></i>
			Manage Schedule
		</button>
	</h1>
</div>

<div class="row">
	<div class="col-xs-12">
		<div class="row">
			<div class="col-xs-10">
				<form class="form-inline">
					<?php echo CHtml::dropDownList('office_id', $office_id, $officeOptions, array('id'=>'load-office-select', 'prompt' => '- Select -')); ?>
					
					<?php echo CHtml::dropDownList('calendar_id', $calendar_id, $calendarOptions, array('id'=>'load-calendar-select', 'prompt' => '- Select -')); ?>
					
					<button type="button" class="btn btn-info btn-xs load-calendar">Load</button>
				</form>
			</div>
			
			<div class="col-xs-2">
				<div id="external-events">
					<!--<div class="external-event label-inverse" data-class="label-inverse">
						<i class="ace-icon fa fa-ban"></i>
						BLACKOUT DAYS
					</div>-->
					
					<div data-rel="tooltip" data-placement="left" data-original-title="Drag and drop on the date you want to black out" style="cursor:grab; position: fixed; z-index: 2; bottom: auto; top: 215.4px; right: 0px; text-align: center; font-size: 11px; border-radius: 9px 0px 0px 9px; width: 61px; height: 40px; line-height: 20px;" class="external-event label-inverse ui-draggable ui-draggable-handle" data-class="label-inverse">
					   BLACKOUT <br /> DAYS
					</div>
				</div>
			</div>
		</div>
		
		<div class="hr hr-12 dotted"></div>
							
		<div class="space-12"></div>
			
		<div class="row">
			<div class="col-xs-12">

				<?php 
					if( $calendar_id != null )
					{
						echo '<div id="calendar"></div>';
					}
					else
					{
						echo 'No calendar selected.';
					}
				?>

			</div>
		</div>

		<!-- PAGE CONTENT ENDS -->
	</div><!-- /.col -->
</div><!-- /.row -->

