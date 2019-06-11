<?php 
	$isCustomerDisabled = "";
															
	if(Yii::app()->user->account->getIsCustomer() || Yii::app()->user->account->getIsCustomerOfficeStaff())
	{
		$isCustomerDisabled = 'disabled';
	}
?>

<div class="page-header">
	<h1>Dialing Settings</h1>
</div>

<?php $form=$this->beginWidget('CActiveForm', array(
	// 'id'=>'customer-skill-schedule-form',
	'enableAjaxValidation'=>false,
)); ?>

<?php echo CHtml::radioButtonList('dialing_setting', $customerSkill->skill_caller_option_customer_choice,CustomerSkill::listCustomerChoiceOption(), array('disabled' => $isCustomerDisabled)); ?>

<div class="space-6"></div>

<div class="row buttons">
	<div class="col-md-12">
		<?php if( empty($isCustomerDisabled) && Yii::app()->user->account->checkPermission('customer_skills_save_dialing_settings_button','visible') ){ ?>
			<button type="suibmit" class="btn btn-primary btn-mini">Save Dialing Setting <i class="fa fa-check"></i></button>
		<?php } ?>
	</div>
</div>
	
<?php $this->endWidget(); ?>

<div class="space-12"></div>
<div class="space-12"></div>

<div class="page-header">
	<h1>Xfr Address Book</h1>
</div>

<div class="row">
	<div class="col-sm-12">
		
		<form id="addXfrForm">	

			<?php echo CHtml::hiddenField('CustomerSkillXfrAddressBook[customer_skill_id]', $customerSkill->id); ?>
		
			<?php echo CHtml::textField('CustomerSkillXfrAddressBook[phone_number]', '', array('style'=>'width:350px;', 'placeholder'=>'Phone Number')); ?>
			
			<?php echo CHtml::textField('CustomerSkillXfrAddressBook[name]', '', array('style'=>'width:350px;', 'placeholder'=>'Name')); ?>
			
			<button type="button" class="btn btn-success btn-sm btn-add-xfr"><i class="fa fa-plus"></i> Add</button>
		</form>
	</div>
</div>

<div class="space-12"></div>

<div class="row">
	<div class="col-sm-8">
		<table class="table table-bordered table-striped table-hover table-condensed xfr-tbl">

			<thead>
				<td>Phone Number</td>
				<td>Name</td>
				<td>Options</td>

			</thead>

			<tbody>
				<?php 
					if( $xfrAddressBooks )
					{
						foreach( $xfrAddressBooks as $xfrAddressBook )
						{
							echo '<tr>';
							
								echo '<td>'.$xfrAddressBook->phone_number.'</td>';
								
								echo '<td>'.$xfrAddressBook->name.'</td>';
								
								echo '<td class="center">';
									echo '<button id="'.$xfrAddressBook->id.'" class="btn btn-info btn-minier btn-edit-xfr"><i class="fa fa-pencil"></i> Edit</button>';
									echo '<button id="'.$xfrAddressBook->id.'" style="margin-left:5px;"class="btn btn-danger btn-minier btn-delete-xfr"><i class="fa fa-times"></i> Delete</button>';
								echo '</td>';
								
							echo '</tr>';
						}
					}
					else
					{
						echo '<tr><td colspan="3">No results found.</td></tr>';
					}
				?>
			</tbody>
		</table>
	</div>
</div>
