<?php 

class ImportCustomerLeadHistoryController extends Controller
{
	
	public function actionIndex()
	{
		// echo 'Check file before running this import script';
		// exit;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		// $inputFileName = 'csv/ImportCustomerLeadHistory/CallHistory-1000.csv';
		// $inputFileName = 'csv/TestImport/leadHistory.csv';
		// echo $inputFileName = 'csv/ImportCustomerLeadHistory/RealLeadHistory-CustomerFound.csv';
		// echo $inputFileName = 'csv/ImportCustomerLeadHistory/RealLeadHistory-CustomerFound-Peter-Mathison.csv';
		// echo $inputFileName = 'csv/ImportCustomerLeadHistory/RealLeadHistory-CustomerFound-Peter-Mathison.csv';
		// echo $inputFileName = 'csv/ImportThreeCustomer/ThreeCustomerLeadHistory.csv';
		// echo $inputFileName = 'csv/Richard/CallHistory.csv';
		// echo $inputFileName = 'csv/Thomas Keasler/CallHistory.csv';
		echo $inputFileName = 'csv/ImportThreeCustomer/CallHistory-5-11-2016.csv';

		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		
		$customerKeyHolder = array();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				
				$customerPrimaryKey = $worksheet->getCell('A'.$row)->getValue(); //CustomerPrimaryKey
				$clientPrimaryKey = $worksheet->getCell('B'.$row)->getValue(); //ClientPrimaryKey
				$datetime = $worksheet->getCell('C'.$row)->getValue(); //datetime
				$result = $worksheet->getCell('D'.$row)->getValue(); //result
				$subresult = $worksheet->getCell('E'.$row)->getValue(); //subresult
				
				$customerKeyHolder[$customerPrimaryKey][$clientPrimaryKey][$datetime] = array(
					'import_client_primary_key' => $clientPrimaryKey,
					'import_customer_primary_key' => $customerPrimaryKey,
					'datetime' => $datetime,
					'result' => $result,
					'subresult' => $subresult,
				);
			}
			
			
			$customerNotFound = array();
			$leadNotFound = array();
			$noListLeads = array();
			echo '<pre>';
			
			foreach($customerKeyHolder as $customerPk => $customerLeadData)
			{
				echo '<br>';
				echo '--- '.$customerPk.' ---';
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('import_customer_primary_key', $customerPk);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					$customer_id = $customer->id;
					
					$customerCalendar = Calendar::model()->find(array(
						'condition' => 'customer_id = :customer_id',
						'params' => array(
							':customer_id' => $customer_id,
						),
					));
		
					$customerSkill = CustomerSkill::model()->find(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer_id,
						),
					));
					
					
					if($customerCalendar !== null && $customerSkill !== null)
					{
						
						foreach($customerLeadData as  $clientPrimaryKey => $dateLeadHistoryData)
						{
							echo '<br>';
							echo '--- Client Primary Key: '.$clientPrimaryKey.'---';
							echo '<br>';
							
							$criteria = new CDbCriteria;
							$criteria->compare('customer_id', $customer_id);
							$criteria->compare('import_client_primary_key', $clientPrimaryKey);
							
							$lead = Lead::model()->find($criteria);
							
							if($lead !== null)
							{
								foreach($dateLeadHistoryData as $leadHistory)
								{
									$dataDate = date("Y-m-d H:i:s",strtotime($leadHistory['datetime']));
									
									/* $criteria = new CDbCriteria;
									$criteria->compare('start_call_time', $dataDate);
									$criteria->compare('disposition', $leadHistory['result']);
									$criteria->compare('agent_note', $leadHistory['subresult']);
									$criteria->compare('external_note', $leadHistory['subresult']);
									$criteria->compare('lead_id', $lead->id);
									$criteria->compare('customer_id', $customer_id);
									
									
									$leadCallHistory = LeadCallHistory::model()->find($criteria);
									 */
									 
										
									$criteria = new CDbCriteria;
									// $criteria->compare('lead_name', $lead->first_name.' '.$lead->last_name);
									$criteria->compare('lead_id', $lead->id);
									$criteria->compare('type', 1);
									$criteria->compare('disposition', $leadHistory['result']);
									$criteria->compare('note', $leadHistory['result'].' '.$leadHistory['subresult']);
									$criteria->compare('date_created', $dataDate);
									
									
									$leadCallHistory = LeadHistory::model()->find($criteria);
									
									// echo  $leadHistory['result'];
									// echo  $lead->id;
									// var_dump($leadCallHistory);
									// exit;
									if($leadCallHistory === null)
									{
										if(empty($lead->list))
										{
											$noListLeads[$lead->import_client_primary_key] = $lead->import_client_primary_key;
										}
										
										echo '<br>';
										echo 'Creating new History...';
											
										/* $leadCallHistory = new LeadCallHistory;
										// $leadCallHistory->date_created = $dataDate;
										$leadCallHistory->disposition = $leadHistory['result'];
										$leadCallHistory->agent_note = $leadHistory['subresult'];
										$leadCallHistory->external_note = $leadHistory['subresult'];
										
										$leadCallHistory->setAttributes(array(
											'lead_id' => $lead->id, 
											'list_id' => isset($lead->list) ? $lead->list->id : null, 
											'customer_id' => $customer->id, 
											'company_id' => $customer->company_id, 
											'contract_id' => $customerSkill->contract_id,
											'agent_account_id' => Yii::app()->user->id, 
											'dial_number' => 0,
											'lead_phone_number' => preg_replace("/[^0-9]/","", $lead->office_phone_number), 
											'start_call_time' => $dataDate,
										));
										
										$leadCallHistory->is_imported = 1;
										
										if( $leadCallHistory->save(false) )
										{
											echo 'History Saved';
											echo '</br>';
										}
										else
										{
											print_r($leadCallHistory->getErrors());
											exit;
										} */
										
										
										
										$leadHistoryModel = new LeadHistory;
										$leadHistoryModel->lead_id = $lead->id;
										// $leadHistory->lead_name = $lead->first_name.' '.$lead->last_name;
										$leadHistoryModel->type = 1;
										$leadHistoryModel->disposition = $leadHistory['result'];
										$leadHistoryModel->note = $leadHistory['result'].' '.$leadHistory['subresult'];
										$leadHistoryModel->is_imported = 1;
							
										
										if( $leadHistoryModel->save(false) )
										{
											$leadHistoryModel->date_created = $dataDate;
											$leadHistoryModel->save(false);
											
											echo 'History Saved';
											echo '</br>';
										}
										else
										{
											print_r($leadHistoryModel->getErrors());
											exit;
										}
										
									}
									else
									{
										echo $dataDate.' - '. $leadHistory['result'] .' - '. $leadHistory['subresult'];
										echo '<br>';
									}
									
								}
							}
							else
							{
								$leadNotFound[$customerPk][$clientPrimaryKey] = $clientPrimaryKey;
							}
							
						}
					}
					else 
					{
						$setFlashMessage = '';
						
						'<br>Customer lack information: '.$customerPk;
						
						echo '<br>';
						if( empty($customerCalendar) )
						{
							$setFlashMessage .= '<li>Please create atleast <b>1 calendar</b> in order to create a list - '.CHtml::link('Click to create a calendar', array('/customer/calendar', 'customer_id'=>$customer_id)).'.</li>';
						}
						
						if( empty($customerSkill) )
						{
							$setFlashMessage .= '<li>Please add atleast <b>1 skill</b> in order to create a list - '.CHtml::link('Click to add a skill', array('/customer/customerSkill', 'customer_id'=>$customer_id)).'.</li>';
						}
						
						echo $setFlashMessage;
					}
				}
				else
				{
					
					$customerNotFound[$customerPk] = $customerPk;
				}
			}
			
			echo '<br>';
			echo 'Customer not found:';
			echo '<br>';
			print_r($customerNotFound);
			echo '<br>';
			echo '<br>-------------<br>';
			echo '<br>';
			echo 'Lead not found:';
			echo '<br>';
			print_r($leadNotFound);
			echo '<br>';
			
			echo '<br>-------------<br>';
			echo '<br>';
			echo 'Lead without a list not found:';
			echo '<br>';
			print_r($noListLeads);
			echo '<br>';
			
			
			
			$transaction->commit();
		}
		catch(Exception $e)
		{
			$transaction->rollback();
			print_r($e);
		}
			
	}

}

?>