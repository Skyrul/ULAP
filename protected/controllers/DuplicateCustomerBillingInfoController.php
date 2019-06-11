<?php 

class DuplicateCustomerBillingInfoController extends Controller
{
	
	public function actionIndex()
	{
		echo 'Check file before running this import script';
		exit;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		$inputFileName = 'csv/';
		#$inputFileName = 'csv/Customer Billing Info.csv';

		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				$customerName = $worksheet->getCell('A'.$row)->getValue();
				$managerAccountId = $worksheet->getCell('B'.$row)->getValue();
				$ulapAccountId = $worksheet->getCell('C'.$row)->getValue();
				
				#############
				
				$criteria = new CDbCriteria;
				$criteria->compare('account_number', $managerAccountId);
				$duplicateAccount = Customer::model()->find($criteria);
		
				$criteria = new CDbCriteria;
				$criteria->compare('account_number', $ulapAccountId);
				$ulapAccount = Customer::model()->find($criteria);
				
		
				if($duplicateAccount !== null && $ulapAccount !== null)
				{
					// echo $ctr++;
					// echo $customerName;
					// echo $managerAccountId;
					// echo $ulapAccountId;
					// echo '<br>';
					// echo '<br>';
					
					$this->duplicateBillingInfo($duplicateAccount, $ulapAccout);
				}
				else 
				{
					echo '<br>';
					echo '####### NO MATCH FOUND ######';
					echo '<br>';
					echo $customerName;
					echo $managerAccountId;
					echo $ulapAccountId;
					echo '<br>';
					echo '<br>';
				}
				
			}
		
			$transaction->commit();
			echo 'Success';
			
		}
		catch(Exception $e)
		{
			print_r($e);
		}
			
	}
	
	public function actionDuplicateBillingInfo($dId, $uId)
	{
		$criteria = new CDbCriteria;
		$criteria->compare('account_number', $dId);
		$duplicateAccount = Customer::model()->find($criteria);

		$criteria = new CDbCriteria;
		$criteria->compare('account_number', $uId);
		$ulapAccount = Customer::model()->find($criteria);
		
		if($duplicateAccount !== null && $ulapAccount !== null)
		{
			print_r($duplicateAccount->attributes);
			
			echo '<br><br>';
			print_r($ulapAccount->attributes);
			// echo $ctr++;
			// echo $customerName;
			// echo $managerAccountId;
			// echo $ulapAccountId;
			// echo '<br>';
			// echo '<br>';
			
			$transaction = Yii::app()->db->beginTransaction();
			try
			{
				$this->duplicateBillingInfo($duplicateAccount, $ulapAccount);
				$transaction->commit();
				echo 'Success';
			}
			catch(Exception $e)
			{
				$transaction->rollback();
				print_r($e);
			}
		}
		else 
		{
		}
	}
	
	public function duplicateBillingInfo($duplicateAccount, $ulapAccount)
	{
		$creditCards = CustomerCreditCard::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $ulapAccount->id,
			),
			'order'=> 'date_created DESC',
		));
		
		
		foreach($creditCards as $creditCard)
		{
			$nccc = new CustomerCreditCard;
			$nccc->attributes = $creditCard->attributes;
			$nccc->customer_id = $duplicateAccount->id;
			
			if($nccc->save(false))
			{
				##find all billing from this credit Card
				$customerBillings = CustomerBilling::model()->findAll(array(
					'condition' => 'customer_id = :customer_id AND credit_card_id = :credit_card_id',
					'params' => array(
						':customer_id' => $ulapAccount->id,
						':credit_card_id' => $creditCard->id,
					),
					'order'=> 'date_created DESC',
				));
			
				foreach($customerBillings as $customerBilling)
				{
					$ncb = new CustomerBilling;
					$ncb->attributes = $customerBilling->attributes;
					$ncb->customer_id = $duplicateAccount->id;
					$ncb->credit_card_id = $nccc->id;
					$ncb->save(false);
					$ncb->date_created = $customerBilling->date_created;
					$ncb->save(false);
				}
			}
		
		}
		
		$echecks = CustomerEcheck::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND status=1',
			'params' => array(
				':customer_id' => $ulapAccount->id,
			),
			'order'=> 'date_created DESC',
		));
		
		foreach($echecks as $echeck)
		{
			$nce = new CustomerEcheck;
			$nce->attributes = $echeck->attributes;
			$nce->customer_id = $duplicateAccount->id;
			$nce->save(false);
		}
	}
	
	
	public function actionSetDelete()
	{
	
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
	
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		
		// $inputFileName = 'csv/';
		$inputFileName = 'csv/Customer Billing Info.csv';

		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
		
		$worksheet = $objPHPExcel->getActiveSheet();

		$highestRow         = $worksheet->getHighestRow(); // e.g. 10
		$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
		$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
		$nrColumns = ord($highestColumn) - 64;
		
		$transaction = Yii::app()->db->beginTransaction();
		try
		{
			$ctr = 1;
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				$customerName = $worksheet->getCell('A'.$row)->getValue();
				$managerAccountId = $worksheet->getCell('B'.$row)->getValue();
				$ulapAccountId = $worksheet->getCell('C'.$row)->getValue();
				
				#############
				
				$criteria = new CDbCriteria;
				$criteria->compare('account_number', $managerAccountId);
				$duplicateAccount = Customer::model()->find($criteria);
		
				$criteria = new CDbCriteria;
				$criteria->compare('account_number', $ulapAccountId);
				$ulapAccount = Customer::model()->find($criteria);
				
		
				if($duplicateAccount !== null && $ulapAccount !== null)
				{
					$ulapAccount->is_deleted = 1;
					$ulapAccount->save(false);
				}
				else 
				{
					echo '<br>';
					echo '####### NO MATCH FOUND ######';
					echo '<br>';
					echo $customerName;
					echo $managerAccountId;
					echo $ulapAccountId;
					echo '<br>';
					echo '<br>';
				}
				
			}
		
			$transaction->commit();
			echo 'Success';
			
		}
		catch(Exception $e)
		{
			print_r($e);
		}
			
	}
}

?>