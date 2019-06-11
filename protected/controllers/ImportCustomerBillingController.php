<?php 

class ImportCustomerBillingController extends Controller
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
		// echo $inputFileName = 'csv/ImportCustomerBilling/BillingRecords.csv';
		// echo $inputFileName = 'csv/Richard/BillingRecords.csv';
		// echo $inputFileName = 'csv/Thomas Keasler/BillingRecords.csv';
		echo $inputFileName = 'csv/ImportThreeCustomer/BillingRecords-5-11-2016.csv';

		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		
		echo '<pre>';
		$customerKeyHolder = array();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				
				$customerPrimaryKey = $worksheet->getCell('A'.$row)->getValue(); //CustomerPrimaryKey
				$transtype = $worksheet->getCell('B'.$row)->getValue(); //transtype
				$amount = $worksheet->getCell('C'.$row)->getValue(); //amount
				$description = $worksheet->getCell('D'.$row)->getValue(); //description
				$approveid = $worksheet->getCell('E'.$row)->getValue(); //approveid
				$approvecode = $worksheet->getCell('F'.$row)->getValue(); //approvecode
				$approvedate = $worksheet->getCell('G'.$row)->getValue(); //approvedate
				
				if(!empty($approvedate))
				{
					$customerKeyHolder[$customerPrimaryKey][] = array(
						'customerPrimaryKey' => $customerPrimaryKey,
						'transtype' => $transtype,
						'amount' => $amount,
						'description' => $description,
						'approveid' => $approveid,
						'approvecode' => $approvecode,
						'approvedate' => $approvedate,
					);
				}
			}
			
			
			$customerNotFound = array();
			
			foreach($customerKeyHolder as $customerPk => $customerData)
			{
				echo '<br>';
				echo '--- '.$customerPk.' ---';
				echo '<br>';
				
				// print_r($customerData); exit;
				$criteria = new CDbCriteria;
				$criteria->compare('import_customer_primary_key', $customerPk);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					$customer_id = $customer->id;
					
					foreach($customerData as $cData)
					{
						$dataDate = date("Y-m-d H:i:s", strtotime($cData['approvedate']));
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customer_id);
						$criteria->compare('amount', $cData['amount']);
						$criteria->compare('transaction_type', $cData['transtype']);
						$criteria->compare('payment_method', $cData['transtype']);
						$criteria->compare('description', $cData['description']);
						$criteria->compare('date_created', $dataDate);
						$criteria->compare('is_imported', 1);
						
						$existingCustomerBilling = CustomerBilling::model()->find($criteria);
						
						if($existingCustomerBilling === null)
						{
							$customerBilling = new CustomerBilling;
							$customerBilling->customer_id = $customer_id;
							$customerBilling->amount = $cData['amount'];
							$customerBilling->transaction_type = $cData['transtype'];
							$customerBilling->payment_method = $cData['transtype'];
							$customerBilling->description = $cData['description'];
							$customerBilling->is_imported = 1;
							
							$customerBilling->save(false);
							$customerBilling->date_created = $customerBilling->date_updated = $dataDate;
							$customerBilling->save(false);
							
							echo 'Billing Added...<br>';
						}
						else
						{
							echo 'Billing alrady existed...<br>';
						}
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