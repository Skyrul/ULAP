<?php 
if( $model->payment_method == 'echeck' )
{
	$creditCardNumber = '**** **** **** ' . substr($model->ach_account_number, -4);
}
else
{
	$creditCardNumber = '**** **** **** ' . substr($model->credit_card_number, -4);
}

$customerName = ucfirst($model->customer->firstname).' '.ucfirst($model->customer->lastname);


$itemName = '';

$criteria = new CDbCriteria;
$criteria->compare('customer_id',$model->customer->id);
$criteria->compare('status',CustomerSkill::STATUS_ACTIVE);
$selectedCustomerSkills = CustomerSkill::model()->findAll($criteria);

if( !empty($selectedCustomerSkills) )
{
	foreach( $selectedCustomerSkills as $selectedCustomerSkill )
	{
		$contract = $selectedCustomerSkill->contract;
		
		if( isset($contract) && $contract->fulfillment_type != null )
		{
			if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME )
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $selectedCustomerSkill->getCustomerSkillLevelArray();
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;

						if( $customerSkillLevelArrayGroup != null )
						{							
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$itemName .= $contract->description.' - ';

								$itemName .= $model->billing_period . ' | ';
						
								$itemName .= 'Goal Volume';
								
								$itemName .= ' | Quantity ' . $customerSkillLevelArrayGroup->quantity;
								
								$itemName .= ' | ' . $subsidyLevel['goal'];
								
								$itemName .= ' <br />';
							}
						}
					}
				}
			}
			else
			{
				if( !empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME]) )
				{
					foreach( $contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel )
					{
						$customerSkillLevelArray = $selectedCustomerSkill->getCustomerSkillLevelArray();
						
						$customerSkillLevelArrayGroup = isset($customerSkillLevelArray[$subsidyLevel['group_id']]) ? $customerSkillLevelArray[$subsidyLevel['group_id']] : null;
						
						if( $customerSkillLevelArrayGroup != null )
						{
							if( $customerSkillLevelArrayGroup->status == CustomerSkillLevel::STATUS_ACTIVE )
							{
								$itemName .= $contract->description.' - ';

								$itemName .= $model->billing_period . ' | ';
						
								$itemName .= 'Lead Volume';
								
								$itemName .= ' | Quantity ' . $customerSkillLevelArrayGroup->quantity;
								
								$itemName .= ' | ' . $subsidyLevel['low'].' - '.$subsidyLevel['high'];
								
								$itemName .= ' <br />';
							}
						}
					}
				}
			}
		}
	}
}

?>
	
<table width="100%" align="center" style="font-size:10px;">
	<tr>
		<td>
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td width="63%" style="text-align:left;"><img src="http://system.engagexapp.com/webform/images/engagex-logo.jpg" width="190"></td>
					<td style="font-size:14px;text-align:left;"><br /><br /><br /><?php echo $model->transaction_type; ?> Receipt</td>
				</tr>
			</table>
			
			<br />
			<br />
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td valign="top" width="30%" style="text-align:left;">
						From: 
						<br />
						Engagex
						<br />
						585 E 1860 S
						<br />
						Provo, UT 84606
						<br />
						1.800.515.8734

						<br />
						<br />
						<a href="http://insurance.engagex.com" target="_blank">insurance.engagex.com</a>
					</td>
					
					<td valign="top" width="5%" style="border-left: 2px solid #2C6AA0;"></td>
					
					<td valign="top" width="30%" style="text-align:left;">
						Billed to:
						<br />
						<?php echo $customerName; ?>
						<br />
						<br />
						Account #
						<br />
						<?php echo $model->customer->account_number; ?>
						<br />
						<br />
						<a href="mailto:<?php echo $model->customer->email_address; ?>"><?php echo $model->customer->email_address; ?></a>
					</td>
					
					<td valign="top" width="5%" style="border-left: 2px solid #2C6AA0;"></td>
					
					<td valign="top" width="30%">
						<br />
						<table width="100%" border="0" cellpadding="0" cellspacing="0" style="text-align:left;">
							<tr>
								<td width="50%">Receipt Date</td>
								<td width="50%" align="right"><?php echo date('m/d/Y'); ?></td>
							</tr>
							<tr>
								<td width="50%">Card Number</td>
								<td width="50%" align="right"><?php echo $creditCardNumber; ?></td>
							</tr>
							<tr>
								<td width="50%">Total <?php echo $model->transaction_type; ?></td>
								<td width="50%" align="right"><?php echo '$' . number_format($model->amount, 2); ?></td>
							</tr>
						</table>
					</td>
				</tr>
				
			</table>
			
			<br />
			<br />
			<br />
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr bgcolor="#2C6AA0">
					<td width="60%" style="padding:2px 0 2px 15px;text-align:left"><font color="#ffffff">Description</font></td>
					<td width="10%" style="padding:2px 0 2px 15px;text-align:center"><font color="#ffffff"><?php echo $model->transaction_type.' Amount'; ?></font></td>
					<td width="10%" style="padding:2px 0 2px 15px;text-align:center"><font color="#ffffff">Billing Credit</font></td>
					<td width="10%" style="padding:2px 0 2px 15px;text-align:center"><font color="#ffffff">Subsidy Amount</font></td>
					<td width="10%" style="padding:2px 0 2px 15px;text-align:center"><font color="#ffffff">Reduced Amount</font></td>
				</tr>
				
				<tr>
					<td style="text-align:left"><?php echo $itemName; ?></td>
					<td align="center" style="text-align:center">
						<?php 
							if( $model->original_amount != null )
							{
								echo '$' . number_format( $model->original_amount, 2);
							}
							else
							{
								if( $model->credit_amount != null )
								{
									echo '$' . number_format( ($model->amount + $model->credit_amount), 2);
								}
								
								if( $model->subsidy_amount != null )
								{
									echo '$' . number_format( ($model->amount + $model->subsidy_amount), 2);
								}
							}
						?>
					</td>
					
					<td align="center" style="text-align:center"><?php echo '$'.number_format($model->credit_amount, 2); ?></td>
					
					<td align="center" style="text-align:center"><?php echo '$'.number_format($model->subsidy_amount, 2); ?></td>
					
					<td align="center" style="text-align:center"><?php echo '$' . number_format($model->amount, 2); ?></td>
				</tr>
				
			</table>
			
			<br />
			<br />
			<br />
			
			<table width="100%" border="0" cellpadding="0" cellspacing="0">
				<tr bgcolor="#2C6AA0">
					<td width="85%" style="padding:2px 0 2px 15px;text-align:left"><font color="#ffffff">Total</font></td>
					<td width="15%" style="padding:2px 0 2px 80px;text-align:center"><font color="#ffffff"><?php echo '$' . number_format($model->amount, 2); ?></font></td>
				</tr>
			</table>
		</td>
	</tr>
</table>