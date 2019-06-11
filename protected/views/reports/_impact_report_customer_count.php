<?php 

	if( $customerQueues )
	{
		foreach( $customerQueues as $customerQueue )
		{
			$customerSkill = CustomerSkill::model()->find(array(
				'with' => 'customer',
				'condition' => '
					t.customer_id = :customer_id 
					AND t.skill_id = :skill_id 
					AND customer.company_id NOT IN(15, 17,18,23, 24, 25, 26, 27)
					AND customer.status=1
					AND customer.is_deleted=0
				',
				'params' => array(
					':customer_id' => $customerQueue->customer_id,
					':skill_id' => $customerQueue->skill_id,
				),

			));
			
			$customerRemoved = CustomerBillingWindowRemoved::model()->find(array(
				'condition' => '
					customer_id = :customer_id 
					AND skill_id = :skill_id 
					AND MONTH(date_created) = :month
					AND YEAR(date_created) = :year
				',
				'params' => array(
					':customer_id' => $customerQueue->customer_id,
					':skill_id' => $customerQueue->skill_id,
					':month' => date('n', strtotime($billingPeriod)),
					':year' => date('Y', strtotime($billingPeriod))
				),
			));
			
			if( $customerSkill && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) && empty($customerRemoved) )
			{
				if( isset($customerSkill->contract) )
				{
					$contract = $customerSkill->contract;
					$customer = $customerSkill->customer;
					
					$customerIsCallable = false;
					
					$contractCreditSubsidys = $this->getCustomerContractCreditAndSubsidy($customer, $contract, $billingPeriod);		
					$totalAmount = $contractCreditSubsidys[$contract->id]['totalAmount'];										
					$totalCreditAmount = $contractCreditSubsidys[$contract->id]['totalCreditAmount'];		
					$subsidyAmount = $contractCreditSubsidys[$contract->id]['subsidyAmount'];		
					$totalReducedAmount = $contractCreditSubsidys[$contract->id]['totalReducedAmount'];		
					$totalLeads = $contractCreditSubsidys[$contract->id]['totalLeads'];		
					
					//credit amount should not be over the Amount, for the customer will ask it to be billed next month -aug 9, 2016
					if($totalCreditAmount > $totalAmount)
					{
						$totalCreditAmount = $totalAmount - $subsidyAmount;
					}

					$totalReducedAmount = abs($totalAmount - $subsidyAmount);
					
					if( $totalCreditAmount < 0 )
					{
						$totalReducedAmount = $totalReducedAmount + abs($totalCreditAmount);
					}
					else
					{
						$totalReducedAmount = $totalReducedAmount - abs($totalCreditAmount);
					}
					
					if( $totalReducedAmount < 0 )
					{
						$totalReducedAmount = 0;
					}
					
					$totalReducedAmount = number_format($totalReducedAmount, 2);
					
					echo '<tr>';

						echo '<td>'.date('Y-m', strtotime($customerSkill->start_month)).'</td>';
						echo '<td>'.date('Y-m', strtotime('+1 month', strtotime($customerSkill->end_month))).'</td>';
						echo '<td>'.date('Y-m', strtotime($billingPeriod)).'</td>';
						echo '<td>'.$customer->getFullName().'</td>';
						echo '<td>'.$totalAmount.'</td>';
						echo '<td>'.$totalCreditAmount.'</td>';
						echo '<td>'.$subsidyAmount.'</td>';
						echo '<td>'.$totalReducedAmount.'</td>';
						
					echo '</tr>';
					
									
					//check status and start date
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && !empty($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' && date('Y-m', strtotime($billingPeriod)) >= date('Y-m', strtotime($customerSkill->start_month)) )
					{
						$customerIsCallable = true;
					}

					//check if on hold
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( strtotime($billingPeriod) >= strtotime($customerSkill->is_contract_hold_start_date) && strtotime($billingPeriod) <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$customerIsCallable = false;
							}
						}
					}

					//check if cancelled
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( strtotime($billingPeriod) >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
						}
					}

					if( $customerIsCallable )
					{
						echo $customerQueue->customer_name;
						echo '<br>';
					}
				}
			}
		}
	}

?>