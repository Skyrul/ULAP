<?php 
	$listsArray = array();
	
	$memberNumberCustomData = LeadCustomData::model()->find(array(
		'condition' => '
			lead_id = :lead_id 
			AND field_name = :field_name
		',
		'params' => array(
			':lead_id' => $lead->id,
			':field_name' => 'Member Number',
		),
	));
	
	if( $memberNumberCustomData )
	{
		$leadCustomDatas = LeadCustomData::model()->findAll(array(
			'condition' => 'member_number = :member_number AND list_id IS NOT NULL',
			'group' => 'list_id',
			'params' => array(
				':member_number' => $memberNumberCustomData->value,
			),
			'order' => 't.date_created DESC'
		));
		
		if( $leadCustomDatas )
		{
			foreach( $leadCustomDatas as $leadCustomData )
			{
				if( !in_array($leadCustomData->list_id, $listsArray) && $leadCustomData->list->status != 3 )
				{
					$listsArray[$leadCustomData->list_id] = $leadCustomData->list->name;
				}
			}
		}
	}
?>

<?php if( $listsArray ): ?>
								
	<div class="row-fluid">
		<div class="col-sm-12">
			Previous List Data Points: <?php echo CHtml::dropDownList('dataTabListId', $lead->list_id, $listsArray, array('lead_id'=>$lead->id, 'class'=>'data-tab-dropdown', 'style'=>'width:auto;')); ?>
		</div>
	</div>
	
	<div class="space-12"></div>
	<div class="space-12"></div>
	<div class="space-12"></div>

	<div class="hr hr32 hr-dotted"></div>
	
<?php else: ?>

<div class="row-fluid">
	<div class="col-sm-12">
		No Previous List Data Points Found: 
		<select disabled>
			<option selected>- Select -</option>
		</select>
	</div>
</div>

<?php endif; ?>

<div class="data-fields-tab">

	<div class="form">
		<?php $form=$this->beginWidget('CActiveForm', array(
			'enableAjaxValidation'=>false,
			'htmlOptions' => array(
				'id' => 'dataTabForm',
				'class' => 'form-horizontal',
			),
		)); ?>
		

			<?php echo CHtml::hiddenField('lead_id', $lead->id); ?>
			
			<div class="row-fluid">
				<div class="col-sm-12">
					<?php 
						$listCustomDatas = ListCustomData::model()->findAll(array(
							'condition' => 'list_id = :list_id AND display_on_form=1 AND status=1',
							'params' => array(
								':list_id' => $lead->list_id,
							),
							'order' => 'ordering ASC',
						));
		
						if( $listCustomDatas )
						{
							$defaultValues = array(
								'Last Name' => $lead->last_name,
								'First Name' => $lead->first_name,
								'Partner First Name' => $lead->partner_first_name,
								'Partner Last Name' => $lead->partner_last_name,
								'Address 1' => $lead->address,
								'Address 2' => $lead->address2,
								'City' => $lead->city,
								'State' => $lead->state,
								'Zip' => $lead->zip_code,
								'Office Phone' => $lead->office_phone_number,
								'Mobile Phone' => $lead->mobile_phone_number,
								'Home Phone' => $lead->home_phone_number,
								'Email Address' => $lead->email_address,
							);
							
							$ctr = 1;
							
							foreach( $listCustomDatas as $listCustomData )
							{	
							
								$fieldIsDisabled = $listCustomData->allow_edit == 1 ? 'style="color:#000000 !important;"' : 'disabled="" style="background:#f0f0f0 !important; color:#000000 !important;"';
								
								$fieldValue = '';
								
								if( array_key_exists($listCustomData->original_name, $defaultValues) )
								{
									$fieldValue = $defaultValues[$listCustomData->original_name];
								}
								else
								{
									$leadCustomData = LeadCustomData::model()->find(array(
										'condition' => 'lead_id = :lead_id AND list_id = :list_id AND field_name = :field_name',
										'params' => array(
											':lead_id' => $lead->id,
											':list_id' => $lead->list_id,
											':field_name' => $listCustomData->original_name
										),
									));
									
									if( $leadCustomData )
									{
										$fieldValue = $leadCustomData->value;
									}
								}
								?>
									
									<div class="col-sm-4">
										<div class="row">
											<div class="col-sm-12">
												<label><?php echo $listCustomData->custom_name; ?></label>
												<input type="text" name="updateLeadCustomDatas[<?php echo $listCustomData->original_name; ?>]" value="<?php echo $fieldValue; ?>" class="form-control" <?php echo $fieldIsDisabled; ?>>
											</div>
										</div>
									</div>

								<?php
							}
						}
						else
						{
							echo '<tr><td colspan="6">No custom fields found.</td></tr>';
						}
					?>
				</div>
				
				<div class="clearfix"></div>
			</div>
			
			<div class="form-actions text-center">
				<button type="button" class="btn btn-xs btn-primary data-tab-submit-btn">Save <i class="fa fa-arrow-right"></i></button>
			</div>
			
		<?php $this->endWidget(); ?>
	</div>
	
</div>
