<?php
/* @var $this SiteController */
/* @var $model LoginForm */
/* @var $form CActiveForm  */

$this->pageTitle=Yii::app()->name . ' - Logout';
$this->breadcrumbs=array(
	'Logout',
);

Yii::app()->clientScript->registerCss(uniqid(), '

.login-container { width:60% !important; } 

');
?>

<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;

	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');

	$cs->registerCssFile($baseUrl.'/template_assets/css/fullcalendar.css');

	$cs->registerCss(uniqid(), ' div.external-event:hover { cursor:grab; } ');

	$cs->registerScriptFile($baseUrl.'/template_assets/js/date-time/moment.min.js', CClientScript::POS_END);

	$cs->registerScriptFile($baseUrl.'/template_assets/js/fullcalendar.min.js',  CClientScript::POS_END);
	
	$cs->registerScriptFile($baseUrl.'/js/logout/calendar_work_scheduler.js?t='.time(),  CClientScript::POS_END);
	
	
	$cs->registerScript(uniqid(),'
		
		var account_id = "'.$authAccount->id.'";
	
	', CClientScript::POS_HEAD);
?>

<?php
    foreach(Yii::app()->user->getFlashes() as $key => $message) {
        echo '
			<div class="alert alert-' . $key . '">
				<button data-dismiss="alert" class="close" type="button">
					<i class="ace-icon fa fa-times"></i>
				</button>' . $message . "
			</div>\n";
    }
?>

<span class="hide">
	<?php 
		echo 'startDate: ' . $startDate;
		
		echo '<br>';
		
		echo 'endDate: ' . $endDate;
	?>
</div>

<div class="space-6"></div>

<div class="col-sm-10 col-sm-offset-1">
	<div class="login-container">
	
		<div class="page-header center">
			<h2>
				<span class="blue">Logout</span>
			</h2>
		</div>
		
		<div class="space-6"></div>
		
		<div class="position-relative">
			<div class="login-box visible widget-box no-border" id="login-box">
				<div class="widget-body">
					<div class="widget-main">
						<h4 class="header blue lighter bigger">
							Employee Note
						</h4>

						<div class="space-6"></div>

						<div class="form">
							<?php $form=$this->beginWidget('CActiveForm', array(
								'id'=>'login-form',
								'enableClientValidation'=>true,
								'clientOptions'=>array(
									'validateOnSubmit'=>true,
								),
							)); ?>
							
								<label class="block clearfix">
									<?php echo $form->textArea($model,'employee_note', array('class'=>'form-control', 'placeholder'=>'Leave a note...')); ?>
									
									<?php echo $form->error($model, 'employee_note'); ?>
								</label>

								<div class="space"></div>

								<div class="clearfix">
									<button class="width-36 pull-right btn btn-sm btn-danger">
										<i class="ace-icon fa fa-key"></i>
										<span class="bigger-110">Logout & Verify Time</span>
									</button>
								</div>
								
								<div class="space"></div>
								
								<div class="row">
									<div class="col-sm-12">
										<div id="calendar"></div>
									</div>
								</div>
								
								<div class="space"></div>
								
								<h4 class="header blue lighter bigger">
									Total Worked Hours (Day) - <?php echo $authAccount->getTotalLoginHours(date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'), 'logout'); ?> <br />
									Total Worked Hours (Week) - <?php echo $authAccount->getTotalLoginHours(date('Y-m-d 00:00:00', strtotime('monday this week')), date('Y-m-d 23:59:59', strtotime('sunday this week')), 'logout'); ?> <br />
									Total Worked Hours (Pay Period) - <?php echo $authAccount->getTotalLoginHours($startDate, $endDate, 'logout'); ?> <br />
									<small class="note" style="font-size:12px;">* Reflects approved hours only</small>
								</h4>
								
								<div class="row">
									<div class="col-sm-12">
										<?php 
											$this->widget('zii.widgets.CListView', array(
												'id'=>'payPeriodList',
												'dataProvider'=>$payPeriodDataProvider,
												'itemView'=>'_pay_period_list',
												'template'=>'<table class="table table-striped table-hover table-condensed">{items}</table> <div class="text-center">{pager}</div>',
												'pagerCssClass' => 'pagination',
												'pager' => array(
													'header' => '',
												),
											)); 
										?>
									</div>
								</div>

								<div class="space-4"></div>
								
							<?php $this->endWidget(); ?>
						</div><!-- form -->
						
					</div><!-- /.widget-main -->

					<div class="toolbar clearfix">
						<div style="float:none; text-align:center; width:100%;">
							&nbsp;
						</div>
					</div>
				</div><!-- /.widget-body -->
			</div><!-- /.login-box -->
		</div>
	</div>
</div>
