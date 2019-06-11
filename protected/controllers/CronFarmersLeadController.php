<?php 

ini_set('memory_limit', '512M');
set_time_limit(0);

class CronFarmersLeadController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{
		$string = '
		CronFarmersLeadController<br><br>
		Step1. CronFarmersLead/GenerateLeads<br>
		mag generate siya ng Filename -- "exscrub'.date("mdy").'.csv sa ulap/inbox<br>
		and then.. i-upload dun sa scrubbing "/EngageX/Inbox", <br>
		wait for a while to process yung inupload, then kunin na ung na scrub na file - "/EngageX/EBRScrub" (similar sa filename na inupload mo)<br>
		step2..
		
		<br><br>
		For Step 2: upload sa engagex sa inbox/EBRScrub ung scrubbed file, rename it to exscrub'.date("mdy").'_callable.csv<br>
		then run CronFarmersLead/ReadLeads<br>
		kapag wala namang error,<br><br>
		Step 3 : Recertification process, CronFarmersLead/RecertifyPossibleLeads<br><br>
		Tapos na.<br><br>
		for DNC run lang ung action na GenerateDncLeads<br>
		tas upload din sa scrubbing inbox.<br>
		';
		
		echo $string;
	}
	
	public function getCustomerIds()
	{
		$customerSkills = CustomerSkill::model()->findAll(array(
			'condition' => 't.status=1 
				AND customer.company_id = 9 
					AND customer.status = 1 
					AND customer.is_deleted = 0 ',
			'with'=> array('customer'=>array('joinType'=>'INNER JOIN'),'contract'=>array('joinType'=>'INNER JOIN')),
			'params' => array(
			),
		));
		
		$customerIds = array();
		
		if(!empty($customerSkills))
		{
			foreach($customerSkills as $customerSkill)
			{
				if( $customerSkill )
				{
					$customerIsCallable = false;
					
					$status = 1;					

					
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$customerIsCallable = true;
						
					}
					
					
					if( $customerSkill->is_contract_hold == 1 )
					{
						if( !empty($customerSkill->is_contract_hold_start_date) && !empty($customerSkill->is_contract_hold_end_date) )
						{
							if( time() >= strtotime($customerSkill->is_contract_hold_start_date) && time() <= strtotime($customerSkill->is_contract_hold_end_date) )
							{
								$customerIsCallable = false;
								
								$nextAvailableCallingTime = 'On Hold';
							}
						}
					}
					
					if( $customerSkill->is_hold_for_billing == 1 )
					{
						$customerIsCallable = false;
								
						$nextAvailableCallingTime = 'Decline Hold';
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
							
							$nextAvailableCallingTime = 'Cancelled';
						}
					}
					
					if($customerIsCallable === true)
					{
						$customerIds[$customerSkill->customer->id] = $customerSkill->customer->id;
					}
				}
			}
		}
		
		return $customerIds;
	}
	
	public function getCustomerIdsActiveAndOnHold()
	{
		$customerSkills = CustomerSkill::model()->findAll(array(
			'condition' => 't.status=1 
				AND customer.company_id = 9 
					AND customer.status = 1 
					AND customer.is_deleted = 0 ',
			'with'=> array('customer'=>array('joinType'=>'INNER JOIN'),'contract'=>array('joinType'=>'INNER JOIN')),
			'params' => array(
			),
		));
		
		$customerIds = array();
		
		if(!empty($customerSkills))
		{
			foreach($customerSkills as $customerSkill)
			{
				if( $customerSkill )
				{
					$customerIsCallable = false;
					
					$status = 1;					

					
					if( isset($customerSkill->contract) && isset($customerSkill->customer) && $customerSkill->customer->status == 1 && $customerSkill->customer->is_deleted == 0 && time() >= strtotime($customerSkill->start_month) && $customerSkill->start_month != '0000-00-00' )
					{
						$customerIsCallable = true;
					}
					
					if( !empty($customerSkill->end_month) && date('Y', strtotime($customerSkill->end_month)) >= 2015 )
					{
						if( time() >= strtotime($customerSkill->end_month) )
						{
							$customerIsCallable = false;
							
							$nextAvailableCallingTime = 'Cancelled';
						}
					}
					
					if($customerIsCallable === true)
					{
						$customerIds[$customerSkill->customer->id] = $customerSkill->customer->id;
					}
				}
			}
		}
		
		return $customerIds;
	}
	
	public function actionGenerateLeads($overwrite = 0)
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
			
		$criteria = new CDbCriteria;
		$criteria->compare('date',date('Y-m-d'));
		$pncp = PossibleNowCronProcess::model()->find($criteria);
		
		if(empty($pncp) || !empty($overwrite))
		{
			if(empty($pncp))
			{
				$PossibleNowCronProcess = new PossibleNowCronProcess;
			}
			else
			{
				$PossibleNowCronProcess = $pncp;
			}
		
			$PossibleNowCronProcess->date = date('Y-m-d');
			$PossibleNowCronProcess->date_created = date('Y-m-d H:i:s');
			$PossibleNowCronProcess->status = 'startup';
			$PossibleNowCronProcess->save(false);
			
			$customerIds = $this->getCustomerIds();
			
			
			###is_possible_now_pending = 0 ## cannot be called in lead hopper
			Yii::app()->db->createCommand('UPDATE {{lead}} lead 
			INNER JOIN {{customer}} customer
			ON 
				customer.id = lead.customer_id AND
				customer.id IN ('.implode(',',$customerIds).')
			SET lead.is_possible_now_pending = 0
			WHERE lead.status = 1 
				AND lead.type = 1
				AND lead.list_id IS NOT NULL')->execute();
				
			$criteria = new CDbCriteria;
			$criteria->compare('t.status', 1); //status active
			$criteria->compare('t.type', 1); //type = valid
			$criteria->addCondition('t.list_id IS NOT NULL'); 
			
			$criteria->with = array('customer' => array(
				'condition' => 'customer.id IN ('.implode(',',$customerIds).')'
			));
			
			
				
			// unregister Yii's autoloader
			spl_autoload_unregister(array('YiiBase', 'autoload'));
			
			// register PHPExcel's autoloader ... PHPExcel.php will do it
			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
			require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
			
			// register Yii's autoloader again
			spl_autoload_register(array('YiiBase', 'autoload'));
			 
			// This requires Yii's autoloader
			
			$objPHPExcel = new PHPExcel();
			
			$ctr = 1;

			$headers = array(
				'A' => 'Phone',
				'B' => 'MMDDYYYY',
			);
			
			foreach($headers as $column => $val)
			{		
				$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
			}
			
			$ctr++;
			
			
			$leads = Lead::model()->findAll($criteria);
			
			$PossibleNowCronProcess->status = 'lead processing';
			$PossibleNowCronProcess->save(false);
			
			foreach($leads as $lead)
			{
				if(!empty($lead->home_phone_number))
				{
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $lead->home_phone_number );
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('m/d/Y') );
					$ctr++;
				}
				
				if(!empty($lead->mobile_phone_number))
				{
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $lead->mobile_phone_number );
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('m/d/Y') );
					$ctr++;
				}
				
				if(!empty($lead->office_phone_number))
				{
					$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $lead->office_phone_number );
					$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('m/d/Y') );
					$ctr++;
				}
			}
			
			$PossibleNowCronProcess->status = 'file generation';
			$PossibleNowCronProcess->save(false);
			
			
			$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
			$objWriter->setDelimiter(',');

			$objWriter->setEnclosure('');

			$objWriter->setLineEnding("\r\n");
			$objWriter->setSheetIndex(0);
			
			$filename= 'exscrub'.date("mdy");
			$webroot = Yii::getPathOfAlias('webroot');
			$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox';
			$objWriter->save($folder. DIRECTORY_SEPARATOR .$filename.'.csv');
			
			$PossibleNowCronProcess->status = 'File Completed';
			$PossibleNowCronProcess->save(false);
			
			Yii::app()->end();
		}
		else
		{
			echo 'The Scrub file "exscrub'.date("mdy").'" has already been process.
			<br><b>Status</b>: '.$pncp->status.'
			<br>Do you want to overwrite? '.CHtml::link('Yes',array('CronFarmersLead/GenerateLeads','overwrite'=> 1),array('confirm'=>'Are you sure?'));
		}
		
		if(!empty($overwrite))
		{
			echo 'The Scrub file "exscrub'.date("mdy").'" has been overwritten.';
		}
	}
	
	public function actionUploadGeneratedLeads($overwrite = 0)
	{
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox';
		$source = $folder. DIRECTORY_SEPARATOR .$filename.'.csv';
			
		$target = '/EngageX/Inbox/exportfile.csv'; 
		$username = 'farmers_engagex';
		$password = 'tra5atHu';
		$host = 'xfer.dncsolution.com'; //162.253.104.39
		
		try
		{
			$sftp = new SFTPConnection("localhost", 22);
			$sftp->login($username, $password);
			$sftp->uploadFile("/tmp/to_be_sent", "/tmp/to_be_received");
		}
		catch (Exception $e)
		{
			echo $e->getMessage() . "\n";
		}
		
		
	}

	public function actionReadLeads()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
			
		//get the data in the original file (INBOX)
		$dateFile = 'exscrub'.date("mdy");
		
		//get the data in the original file (INBOX)
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox';
		$filename= $folder. DIRECTORY_SEPARATOR . $dateFile.'.csv';
		
		if (file_exists($filename))
		{
			$i=0; $keys=array();$output=array(); $outputPhoneKey = array();
			$duplicateCtr = 0;
			$handle=fopen($filename, "r");
			if ($handle){
				 while(($line = fgetcsv($handle)) !== false) {
					$i++;
					if ($i==1) {
					   $keys=$line;
					}
					elseif ($i>1){ 
						$attr=array();
						
						if(!isset($outputPhoneKey[$line[0]]))
							$outputPhoneKey[$line[0]] = array();
						else
						{
							// echo '<br>';
							// echo $duplicateCtr++;
							// echo ' '.$line[0].'<br>';
						}
						foreach($line as $k=>$v){
							$attr[$keys[$k]]=$v;
							
							$outputPhoneKey[$line[0]][$keys[$k]]=$v;
						}
						$output[]=$attr;
					}    
				 }
				fclose($handle);
			}
			
			$originalData = $output;
			
		}
		else
		{
			
			echo $filename."<br>File does not exist.";
			exit;
		}

		//get the data in the callable file (EBR Scrub)
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox' . DIRECTORY_SEPARATOR . 'EBRScrub';
		$filename= $folder. DIRECTORY_SEPARATOR .$dateFile.'_callable.csv';
		
		if (file_exists($filename))
		{
			$i=0; $keys=array();$output=array();
			$handle=fopen($filename, "r");
			if ($handle){
				 while(($line = fgetcsv($handle)) !== false) {
					$i++;
					
					$callablePhoneKey[$line[0]]=$line;
						
				 }
				fclose($handle);
			}
			
			$callableData = $callablePhoneKey;
			
			
		}
		else
		{
			echo $filename."<br>File does not exist.";
			exit;
		}
		
		// echo '<pre>';
		// print_r($callableData);
		// exit;
		
		//compare 'original' and 'callable' data to get the phone numbers that has been removed.
		$excludedPhoneNumbers = $outputPhoneKey;
			
		$callablePhoneArray = array();
		
		foreach($callableData as $lead)
		{
			if(isset($outputPhoneKey[$lead[0]]))
			{
				$callablePhoneArray[] = $lead[0];
				unset($excludedPhoneNumbers[$lead[0]]);
				
			}
		}
		
		
		
		$customerIds = $this->getCustomerIds();
		
		if(!empty($callablePhoneArray))
		{
			
			Yii::app()->db->createCommand('UPDATE {{lead}} lead 
			INNER JOIN {{customer}} customer
			ON 
				customer.id = lead.customer_id AND
				customer.id IN ('.implode(',',$customerIds).')
			SET lead.is_possible_now_pending = 1
			WHERE lead.status = 1 
				AND lead.type = 1
				AND lead.list_id IS NOT NULL
				AND (
					home_phone_number IN ("'.implode('","',$callablePhoneArray).'") 
					|| mobile_phone_number IN ("'.implode('","',$callablePhoneArray).'") 
					|| office_phone_number IN ("'.implode('","',$callablePhoneArray).'")
				)')->execute();
		}
		else
		{
			echo 'No phone number has been included.';
		}
		
		$phoneArray = array();
		
		foreach($excludedPhoneNumbers as $excludedPhoneNumber)
		{
			$phoneArray[] = $excludedPhoneNumber['Phone'];
		}
		
		
		
		//Save excluded Phone Numbers in the Possible now leads table
		if(!empty($phoneArray))
		{
			// echo $excludedPhoneNumber['Phone'].'<br>';
			$criteria = new CDbCriteria;
			$criteria->compare('t.status', 1); //status active
			$criteria->compare('t.type', 1); //type = valid
			$criteria->addCondition('t.list_id IS NOT NULL'); 
		
			$criteria->with = array('customer' => array(
				'condition' => 'customer.id IN ('.implode(',',$customerIds).')'
			));
		
			$criteria->addCondition('home_phone_number IN ("'.implode('","',$phoneArray).'") || mobile_phone_number IN ("'.implode('","',$phoneArray).'") || office_phone_number IN ("'.implode('","',$phoneArray).'")');
			
			
			$leads = Lead::model()->findAll($criteria); //findAll incase phone has been used in multiple leads
			if(!empty($leads))
			{
				foreach($leads as $lead)
				{
					$historyHolder = array();
					if(in_array($lead->home_phone_number,$phoneArray ) )
					{
						$possibleNowLead = new PossibleNowLead;
						$possibleNowLead->company_id = $lead->customer->company_id;
						$possibleNowLead->customer_id = $lead->customer->id;
						$possibleNowLead->lead_id = $lead->id;
						$possibleNowLead->phone_number = $lead->home_phone_number;
						$possibleNowLead->phone_number_type = 'home_phone_number';
						$possibleNowLead->date_created = date('Y-m-d H:i:s');
						$possibleNowLead->save(false);
						
						
						$historyHolder[] = Lead::model()->getAttributeLabel('home_phone_number').' "'.$lead->home_phone_number.'" removed per Possible Now Scrub.';
						
						$lead->home_phone_number = '';
						
						
					}
					
					if(in_array($lead->mobile_phone_number,$phoneArray ) )
					{
						$possibleNowLead = new PossibleNowLead;
						$possibleNowLead->company_id = $lead->customer->company_id;
						$possibleNowLead->customer_id = $lead->customer->id;
						$possibleNowLead->lead_id = $lead->id;
						$possibleNowLead->phone_number = $lead->mobile_phone_number;
						$possibleNowLead->phone_number_type = 'mobile_phone_number';
						$possibleNowLead->date_created = date('Y-m-d H:i:s');
						$possibleNowLead->save(false);
						
						$historyHolder[] = Lead::model()->getAttributeLabel('mobile_phone_number').' "'.$lead->home_phone_number.'" removed per Possible Now Scrub.';
						
						$lead->mobile_phone_number = '';
					}
					
					if(in_array($lead->office_phone_number,$phoneArray ) )
					{
						$possibleNowLead = new PossibleNowLead;
						$possibleNowLead->company_id = $lead->customer->company_id;
						$possibleNowLead->customer_id = $lead->customer->id;
						$possibleNowLead->lead_id = $lead->id;
						$possibleNowLead->phone_number = $lead->office_phone_number;
						$possibleNowLead->phone_number_type = 'office_phone_number';
						$possibleNowLead->date_created = date('Y-m-d H:i:s');
						$possibleNowLead->save(false);
						
						$historyHolder[] = Lead::model()->getAttributeLabel('office_phone_number').' "'.$lead->home_phone_number.'" removed per Possible Now Scrub.';
						
						$lead->office_phone_number = '';
					}
					
					if(empty($lead->home_phone_number) && empty($lead->mobile_phone_number) && empty($lead->office_phone_number))
					{
						$lead->status = 3; // completed
					}
					
					$lead->is_possible_now_pending = 1; ## can be called in lead hopper
					
					if($lead->save(false))
					{
						##removed per Possible Now Scrub
						$historyString = implode(', ',$historyHolder); 
						$history = new LeadHistory;
						$history->setAttributes(array(
							'content' => $lead->getFullName().' | '.$historyString, 
							'lead_id' => $lead->id,
							'agent_account_id' => 1,
							'type' => 6,
						));
						
						$history->save(false);
						
						if(empty($lead->home_phone_number) && empty($lead->mobile_phone_number) && empty($lead->office_phone_number))
						{
							$historyString = 'Lead completed per Possible Now Scrub'; 
							$history = new LeadHistory;
							$history->setAttributes(array(
								'content' => $lead->getFullName().' | '.$historyString, 
								'lead_id' => $lead->id,
								'agent_account_id' => 1,
								'type' => 6,
							));
						}
						
					}
				}
			}
		}
		else
		{
			echo 'No phone number has been excluded.';
		}
	}

	public function actionRecertifyPossibleLeads()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
		
		##$customerIds = $this->getCustomerIds();
		
		$leadRecertifys = Lead::model()->findAll(array(
			'condition' => '
				is_possible_now_pending = 1 
				AND 
				(
					recertify_date = "0000-00-00" 
					OR recertify_date IS NULL 
					OR NOW() >= recertify_date
				)'
		));
		
		
		if( !empty($leadRecertifys) )
		{ 
			$dataHolder = array();
			foreach($leadRecertifys as $leadRecertify)
			{
				$leadRecertify->recertify_date = $this->_computeForSkillMaxLeadLifeBeforeRecertify($leadRecertify->list);
				// $leadRecertify->number_of_dials = 0;
				// $leadRecertify->status = 1;
				if( $leadRecertify->save(false) )
				{
					// $leadIds[] = $leadRecertify->id;
					
					$dataHolder[$leadRecertify->customer_id][$leadRecertify->list_id][] = $leadRecertify->id;
				}
			}
			
			foreach($dataHolder as $customer_id => $list)
			{
				foreach($list as $list_id => $leadIds)
				{
					$history = new CustomerHistory;
										
					$history->setAttributes(array(
						'model_id' => $list_id, 
						'customer_id' => $customer_id,
						'user_account_id' => 1,
						'page_name' => 'List',
						'content' => count($dataHolder[$customer_id][$list_id]) . ' leads were recertified from Possible Now.',
						'old_data' => implode(', ', $dataHolder[$customer_id][$list_id]),
						'type' => $history::TYPE_UPDATED,
					));
				}
				
				$history->save(false); 
			}
		
		}
	}
	
	public function _computeForSkillMaxLeadLifeBeforeRecertify($model)
	{
		//recycle - recertify  module
		if(!empty($model->skill->max_lead_life_before_recertify))
		{
			$time = strtotime(date("Y-m-d"));
			$finalDate = date("Y-m-d", strtotime("+".($model->skill->max_lead_life_before_recertify)." day", $time));
			return $finalDate;
		}
		else
			return date("Y-m-d");
	}

	public function actionGenerateDncLeads()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
		
		$customerIdsActiveAndOnHold = $this->getCustomerIdsActiveAndOnHold();
		
		$date = date('Y-m-d');
		
		$addedCondition = '';
					

		$addedCondition .= ' AND DATE(t.date_created) = "'.$date.'"';
		
			
		$models = LeadCallHistory::model()->findAll(array(
			'with' => array('lead'=> array('joinType'=>'INNER JOIN','condition'=>'lead.customer_id IN ('.implode(',', $customerIdsActiveAndOnHold).')')),
			'condition' => '
				t.disposition = "Do Not Call"
				AND t.lead_id IS NOT NULL
				AND t.company_id IN ("9")
				AND t.status = 1' . $addedCondition,
			'order' => 't.date_created DESC',
		));
		
		// unregister Yii's autoloader
		spl_autoload_unregister(array('YiiBase', 'autoload'));
		
		// register PHPExcel's autoloader ... PHPExcel.php will do it
		$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
		require_once($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');
		
		// register Yii's autoloader again
		spl_autoload_register(array('YiiBase', 'autoload'));
		 
		// This requires Yii's autoloader
		
		$objPHPExcel = new PHPExcel();
		
		$ctr = 1;

		$headers = array(
			'A' => 'Phone',
			'B' => 'MMDDYYYY',
		);
		
		foreach($headers as $column => $val)
		{		
			$objPHPExcel->getActiveSheet()->SetCellValue($column.$ctr, $val);
		}
		
		$ctr++;
			
		if(!empty($models))
		{
			foreach( $models as $model )
			{
				// $dateTime = new DateTime($model->date_created, new DateTimeZone('America/Chicago'));
				// $dateTime->setTimezone(new DateTimeZone('America/Denver'));
				
				$objPHPExcel->getActiveSheet()->SetCellValue('A'.$ctr, $model->lead_phone_number);
				$objPHPExcel->getActiveSheet()->SetCellValue('B'.$ctr, date('m/d/Y', strtotime($model->date_created) ));
				
				// $objPHPExcel->getActiveSheet()->SetCellValue('C'.$ctr, $model->lead->getFullName());
				// $objPHPExcel->getActiveSheet()->SetCellValue('D'.$ctr, $model->lead->list->skill->skill_name);
				// $objPHPExcel->getActiveSheet()->SetCellValue('E'.$ctr, $subDispositionName);
				// $objPHPExcel->getActiveSheet()->SetCellValue('F'.$ctr, $model->customer->company->company_name);

				$ctr++;
			}
		}
		
		$filename= 'exdncrequest'.date("mdy");
		
		$objWriter = new PHPExcel_Writer_CSV($objPHPExcel);
		$objWriter->setDelimiter(',');

		$objWriter->setEnclosure('');

		$objWriter->setLineEnding("\r\n");
		$objWriter->setSheetIndex(0);
			
		$webroot = Yii::getPathOfAlias('webroot');
		$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox';
		$objWriter->save($folder. DIRECTORY_SEPARATOR .$filename.'.csv');
		
		Yii::app()->end();
	}

	public function actionUndoReadFile()
	{
			ini_set('memory_limit', '2048M');
			set_time_limit(0); 
				
			//get the data in the original file (INBOX)
			$dateFile = 'exscrub'.date("mdy");
			
			//get the data in the original file (INBOX)
			$webroot = Yii::getPathOfAlias('webroot');
			$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox';
			$filename= $folder. DIRECTORY_SEPARATOR . $dateFile.'.csv';
			
			if (file_exists($filename))
			{
				$i=0; $keys=array();$output=array(); $outputPhoneKey = array();
				$duplicateCtr = 0;
				$handle=fopen($filename, "r");
				if ($handle){
					 while(($line = fgetcsv($handle)) !== false) {
						$i++;
						if ($i==1) {
						   $keys=$line;
						}
						elseif ($i>1){ 
							$attr=array();
							
							if(!isset($outputPhoneKey[$line[0]]))
								$outputPhoneKey[$line[0]] = array();
							else
							{
								// echo '<br>';
								// echo $duplicateCtr++;
								// echo ' '.$line[0].'<br>';
							}
							foreach($line as $k=>$v){
								$attr[$keys[$k]]=$v;
								
								$outputPhoneKey[$line[0]][$keys[$k]]=$v;
							}
							$output[]=$attr;
						}    
					 }
					fclose($handle);
				}
				
				$originalData = $output;
				
			}
			else
			{
				
				echo $filename."<br>File does not exist.";
				exit;
			}

			//get the data in the callable file (EBR Scrub)
			$webroot = Yii::getPathOfAlias('webroot');
			$folder =  $webroot . DIRECTORY_SEPARATOR . 'inbox' . DIRECTORY_SEPARATOR . 'EBRScrub';
			$filename= $folder. DIRECTORY_SEPARATOR .$dateFile.'_callable.csv';
			
			if (file_exists($filename))
			{
				$i=0; $keys=array();$output=array();
				$handle=fopen($filename, "r");
				if ($handle){
					 while(($line = fgetcsv($handle)) !== false) {
						$i++;
						
						$callablePhoneKey[$line[0]]=$line;
							
					 }
					fclose($handle);
				}
				
				$callableData = $callablePhoneKey;
			}
			else
			{
				echo $filename."<br>File does not exist.";
				exit;
			}
			
			//compare 'original' and 'callable' data to get the phone numbers that has been removed.
			$excludedPhoneNumbers = $outputPhoneKey;
			
			$callablePhoneArray = array();
			
			foreach($callableData as $lead)
			{
				if(isset($outputPhoneKey[$lead[0]]))
				{
					$callablePhoneArray[] = $lead[0];
					unset($excludedPhoneNumbers[$lead[0]]);
				}
			}
			
			$customerIds = $this->getCustomerIds();
			
			
			
			$phoneArray = array();
			
			foreach($excludedPhoneNumbers as $excludedPhoneNumber)
			{
				$phoneArray[] = $excludedPhoneNumber['Phone'];
			}
			
			
			echo count($excludedPhoneNumbers); exit;
			
	}

	public function actionFixedPatch()
	{
		ini_set('memory_limit', '2048M');
		set_time_limit(0); 
		
		$psl = PossibleNowLead::model()->findAll(array('condition' =>'date(date_created) = "2017-10-24"'));
		
		// if(!empty($psl))
		// {
			// foreach($psl as $ps)
			// {
				// $ps->lead->{$ps->phone_number_type} = $ps->phone_number;
				// $ps->lead->status = 1;
				// $ps->lead->save(false);
			// }
		// }
	}
}

?>