<div class="form">
	<?php $form=$this->beginWidget('CActiveForm', array(
		'enableAjaxValidation'=>false,
		'htmlOptions' => array(
			'class' => 'form-horizontal',
		),
	)); ?>
	
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Description <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $model->description; ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Type <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php 
						if( $model->type == 1 )
						{
							echo '1 Month';
						}
						else
						{
							echo 'Month Range';
						}
					?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Month <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo  date('F', mktime(0, 0, 0, $model->start_month, 10)); ?>
					
					<span class="end-month-range" style="display:<?php echo $model->type == 2 ? '' : 'none;'?>;">
						to
						<?php echo  date('F', mktime(0, 0, 0, $model->end_month, 10)); ?>
					</span>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Contract <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo $model->contract->contract_name; ?>
				</div>
			</div>
		</div>
		
		<div class="profile-user-info profile-user-info-striped">
			<div class="profile-info-row">
				<div class="profile-info-name"> Amount <span class="required">*</span></div>

				<div class="profile-info-value">
					<?php echo number_format($model->amount, 2) ?>
				</div>
			</div>
		</div>
		
		<div class="space-12"></div>
		
		<?php if(!empty($model->customerCreditBillingHistory)){ ?>
		<h3>Credit History</h3>
		<table class="table table-bordered table-condensed table-hover">
			<thead>
				<tr>
					<th>Date</th>
					<th>Transaction ID</th>
				</tr>
			</thead>


			<tbody>
			<?php foreach($model->customerCreditBillingHistory as $ccbh){?>
				<tr>
					<td>
						<?php 
							$dateTime = new DateTime($ccbh->date_created, new DateTimeZone('America/Chicago'));
							$dateTime->setTimezone(new DateTimeZone('America/Denver'));
							
							echo $dateTime->format('m/d/Y g:i a'); 
						 ?>
					</td>
					<td><?php echo $ccbh->customerBilling->anet_transId; ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
		<?php } ?>
		
	<?php $this->endWidget(); ?>
</div>