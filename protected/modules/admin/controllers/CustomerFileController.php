<?php 

class CustomerFileController extends Controller
{
	
	public function actionIndex($company_id = null)
	{
		$customerAffected1 = array();
		$customerAffected2 = array();
		$company = Company::model()->findByPk($company_id);
		
		//import from excel list file
		if( isset( $_POST['fileUploadIdCustomerFile']) )
		{
			### check if the company have subsidy level setup ###
			$criteria = new CDbCriteria;
			$criteria->compare('company_id', $company->id);
			$criteria->compare('exclude_from_company_file_update', 0);
			$companySubsidys = CompanySubsidy::model()->findAll($criteria);
			
			$companySubsidyLevelHolder = array();
			if(!empty($companySubsidys))
			{ 
				foreach($companySubsidys as $companySubsidy)
				{ 
					foreach($companySubsidy->companySubsidyLevels as $companySubsidyLevel)
					{
						if(!empty($companySubsidyLevel->tier_link))
							$companySubsidyLevelHolder[$companySubsidy->skill_id][$companySubsidy->contract_id][$companySubsidyLevel->tier_link] = $companySubsidyLevel->attributes;
					}
				}
			}
			
			
			$fileupload = Fileupload::model()->findByPk($_POST['fileUploadIdCustomerFile']);
			
			
			ini_set('memory_limit', '512M');
			set_time_limit(0);
			
			// unregister Yii's autoloader
			spl_autoload_unregister(array('YiiBase', 'autoload'));
		
			$phpExcelPath = Yii::getPathOfAlias('ext.phpexcel');
			include($phpExcelPath . DIRECTORY_SEPARATOR . 'Classes' . DIRECTORY_SEPARATOR . 'PHPExcel.php');

			spl_autoload_register(array('YiiBase', 'autoload'));
			 
			
			$inputFileName = 'fileupload/customerFile/' . $fileupload->generated_filename;

			$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			
			$worksheet = $objPHPExcel->getActiveSheet();

			$highestRow         = $worksheet->getHighestRow(); // e.g. 10
			$highestColumn      = $worksheet->getHighestColumn(); // e.g 'F'
			$highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);
			$nrColumns = ord($highestColumn) - 64;
			
			for ($row = 2; $row <= $highestRow; ++$row) 
			{
				#STATE_AGENTCODE
				#AGENT_NAME
				#FUNDING_TIER
				
				$agentCode = $worksheet->getCell('A'.$row)->getValue();
				$agentPrevCode = $worksheet->getCell('B'.$row)->getValue();
				$agentName = $worksheet->getCell('C'.$row)->getValue();
				$fundingTier = $worksheet->getCell('D'.$row)->getValue();
				
				
				#### UPDATE CUSTOMER TABLE ####
				$criteria = new CDbCriteria;
				$criteria->compare('custom_customer_id', $agentPrevCode);
				$customer = Customer::model()->find($criteria);
				
				//try to search from latest agent code
				if($customer === null)
				{
					$criteria = new CDbCriteria;
					$criteria->compare('custom_customer_id', $agentCode);
					$customer = Customer::model()->find($criteria);
				}
				
				if($customer !== null && !empty($customer->custom_customer_id))
				{
					// echo $customer->id;
					// echo '<br>';
					// echo '------';
					// echo '<br>';
					
					$fullNameSplit = explode(',', $agentName);
					
					$createHistory = false;
					$historyContent = '';
					
					if(isset($fullNameSplit[1]))
					{
						$lastName = trim($fullNameSplit[0]);
						$firstName = trim($fullNameSplit[1]);
						
						
						if($agentCode != $customer->custom_customer_id)
						{
							$historyContent .= Customer::model()->getAttributeLabel('custom_customer_id').' changed from '.$customer->custom_customer_id.' to '.$agentCode.'. ';
							$customer->custom_customer_id = $agentCode;
							$createHistory = true;
						}
						
						/*  Functionality DISABLED FOR NOW regarding LASTNAME, and FIRSTNAME
						if($lastName != $customer->lastname)
						{
							$historyContent .= Customer::model()->getAttributeLabel('lastname').' changed from '.$customer->lastname.' to '.$lastName.'. ';
							$customer->lastname = $lastName;
							$createHistory = true;
						}
						
						if($firstName != $customer->firstname)
						{
							$historyContent .= Customer::model()->getAttributeLabel('firstname').' changed from '.$customer->firstname.' to '.$firstName.'. ';
							$customer->firstname = $firstName;
							$createHistory = true;
						} 
						*/
						
						if($createHistory)
						{
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => '', 
								'customer_id' => $customer->id,
								'user_account_id' => null,
								'page_name' => 'Funding Tier',
								'content' =>  $historyContent,
								'type' => $history::TYPE_UPDATED,
							));
							
							$customerAffected1[] = array(
								'customer_id' => $customerSkill->customer_id,
								'customer_name' => $customerSkill->customer->getFullName(),
								'contract_id' => $customerSkill->contract_id,
								'contract_name' => $customerSkill->contract->contract_name,
								'skill_id' => $customerSkill->skill_id,
								'skill_name' => $customerSkill->skill->skill_name,
								'start_month' => $customerSkill->start_month,
								'end_month' => $customerSkill->end_month,
								'content' => $historyContent,
							);
							
							$history->save(false);
						}
					}
					
					
								

					$criteria = new CDbCriteria;
					$criteria->compare('customer_id', $customer->id);
					$customerSkills = CustomerSkill::model()->findAll($criteria);
					
					foreach($customerSkills as $customerSkill)
					{
						
						### check customer subsidy level from the customer skill setup ###
						$criteria = new CDbCriteria;
						$criteria->compare('customer_id', $customer->id);
						$criteria->compare('customer_skill_id', $customerSkill->id);
						$cksl = CustomerSkillSubsidyLevel::model()->find($criteria); 
					
						$fundingTierId = 0;
						$fundingTierName = '';
						
						// echo '<pre>';
						// print_r($companySubsidyLevelHolder);
						if(isset($companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$fundingTier]))
						{
							$fundingTierId = $companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$fundingTier]['id'];
							$fundingTierName = $companySubsidyLevelHolder[$customerSkill->skill_id][$customerSkill->contract_id][$fundingTier]['name'];
						}
					
						if(empty($cksl))
							$cksl = new CustomerSkillSubsidyLevel;
						
						if($fundingTierId != $cksl->subsidy_level_id)
						{
							$oldFundingTierName = isset($cksl->companySubsidyLevel) ? $cksl->companySubsidyLevel->name : '';
							$content = 'Funding Tier changed from '.$oldFundingTierName.' to '.$fundingTierName;
							
							
							$criteria = new CDbCriteria;
							$criteria->compare('customer_id', $customer->id);
							$criteria->compare('customer_skill_id', $customerSkill->id);
							
							CustomerSkillSubsidyLevel::model()->deleteAll($criteria);
							
							if($fundingTierId != 0)
							{
								$cssl = CustomerSkillSubsidyLevel::model()->find($criteria);
								if($cssl === null)
								{
									$cssl = new CustomerSkillSubsidyLevel;
									$cssl->customer_id = $customer->id;
									$cssl->customer_skill_id = $customerSkill->id;
									$cssl->subsidy_level_id = $fundingTierId;
								}
								
								//STATIC FOR NOW
								$cssl->status = CustomerSkillSubsidyLevel::STATUS_ACTIVE;
								$cssl->type = 1; 
								
								
								if(!$cssl->save(false))
								{
									print_r($cssl->getErrors());
								}
								
								$cssl->save(false);
							}
		
							$history = new CustomerHistory;
							
							$history->setAttributes(array(
								'model_id' => '', 
								'customer_id' => $customer->id,
								'user_account_id' => null,
								'page_name' => 'Funding Tier',
								'content' =>  $content,
								'type' => $history::TYPE_UPDATED,
							));
							
							$customerAffected2[] = array(
								'customer_id' => $customerSkill->customer_id,
								'customer_name' => $customerSkill->customer->getFullName(),
								'contract_id' => $customerSkill->contract_id,
								'contract_name' => $customerSkill->contract->contract_name,
								'skill_id' => $customerSkill->skill_id,
								'skill_name' => $customerSkill->skill->skill_name,
								'content' => $content,
							);
								
							$history->save(false);
						}
					
					}
					
					$customer->save(false);
					
				}
				
				
				#### CREATE/UPDATE COMPANY CUSTOMER FUNDING TIER DATABASE ####
				$criteria = new CDbCriteria;
				$criteria->compare('agent_code', $agentPrevCode);
				$ccft = CompanyCustomerFundingTier::model()->find($criteria);
				
				//try to search from latest agent code
				if($ccft === null)
				{
					$criteria = new CDbCriteria;
					$criteria->compare('agent_code', $agentCode);
					$ccft = CompanyCustomerFundingTier::model()->find($criteria);
				}
				
				
				if($ccft === null)
					$ccft = new CompanyCustomerFundingTier;
				
				$ccft->company_id = $company->id;
				$ccft->agent_code = $agentCode;
				$ccft->funding_tier = $fundingTier;
				$fullNameSplit = explode(',', $agentName);
				
				$createHistory = false;
				$historyContent = '';
				
				if(isset($fullNameSplit[1]))
				{
					$ccft->agent_lastname = trim($fullNameSplit[0]);
					$ccft->agent_firstname = trim($fullNameSplit[1]);
				}
				
				if(!$ccft->save())
				{
					print_R($ccft->getErrors()); exit;
				}
			}
			
			echo '<br>starts here<br><br>';
			foreach($customerAffected1 as $clone)
			{
				echo '"'.$clone['customer_id'].'", ';
				echo '"'.$clone['customer_name'].'", ';
				echo '"'.$clone['contract_id'].'", ';
				echo '"'.$clone['contract_name'].'", ';
				echo '"'.$clone['skill_id'].'", ';
				echo '"'.$clone['skill_name'].'", ';
				echo '"'.$clone['start_month'].'", ';
				echo '"'.$clone['end_month'].'"';
				
				
				echo '<br>';
			}
			
			foreach($customerAffected2 as $clone)
			{
				echo '"'.$clone['customer_id'].'", ';
				echo '"'.$clone['customer_name'].'", ';
				echo '"'.$clone['contract_id'].'", ';
				echo '"'.$clone['contract_name'].'", ';
				echo '"'.$clone['skill_id'].'", ';
				echo '"'.$clone['skill_name'].'", ';
				echo '"'.$clone['start_month'].'", ';
				echo '"'.$clone['end_month'].'"';
				
				
				echo '<br>';
			}
			
			exit;
		}
		
		if(isset($_GET['forward']))
		{
			$this->renderPartial('index', array(
				'company' => $company
			));
		}
		else
		{
			$this->render('index', array(
				'company' => $company
			));
		}
	}
	
	public function actionUpload()
	{
		// Settings
		$targetDir = 'fileupload/customerFile';

		$cleanupTargetDir = true; // Remove old files
		$maxFileAge = 5 * 3600; // Temp file age in seconds

		// 5 minutes execution time
		@set_time_limit(5 * 60);

		// Get parameters
		$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
		$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;
		// $fileName = isset($_REQUEST["name"]) ? $_REQUEST["name"] : '';
		$fileName = $_FILES['FileUpload']['name']['filename'];
		
		$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

		// Create target dir
		if (!file_exists($targetDir))
			@mkdir($targetDir);

		// Remove old temp files	
		if ($cleanupTargetDir && is_dir($targetDir) && ($dir = opendir($targetDir))) {
			while (($file = readdir($dir)) !== false) {
				$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

				// Remove temp file if it is older than the max age and is not the current file
				if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge) && ($tmpfilePath != "{$filePath}.part")) {
					@unlink($tmpfilePath);
				}
			}

			closedir($dir);
		} else
			die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
		
			

		// Look for the content type header
		if (isset($_SERVER["HTTP_CONTENT_TYPE"]))
			$contentType = $_SERVER["HTTP_CONTENT_TYPE"];

		if (isset($_SERVER["CONTENT_TYPE"]))
			$contentType = $_SERVER["CONTENT_TYPE"];
		
		
		
		
		// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
		if (strpos($contentType, "multipart") !== false) {
			if (isset($_FILES['FileUpload']['tmp_name']['filename']) && is_uploaded_file($_FILES['FileUpload']['tmp_name']['filename'])) {
				// Open temp file
				$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
				if ($out) {
					// Read binary input stream and append it to temp file
					$in = fopen($_FILES['FileUpload']['tmp_name']['filename'], "rb");

					if ($in) {
						while ($buff = fread($in, 4096))
							fwrite($out, $buff);
					} else
						die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
					fclose($in);
					fclose($out);
					//@unlink($_FILES['file']['tmp_name']);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
		} else {
			// Open temp file
			$out = fopen("{$filePath}.part", $chunk == 0 ? "wb" : "ab");
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen("php://input", "rb");

				if ($in) {
					while ($buff = fread($in, 4096))
						fwrite($out, $buff);
				} else
					die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');

				fclose($in);
				fclose($out);
			} else
				die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
		}
		
		
		// Check if file has been uploaded
		if (!$chunks || $chunk == $chunks - 1)
		{
			// Strip the temp .part suffix off 
			rename("{$filePath}.part", $filePath);
			
			$file = CUploadedFile::getInstanceByName('FileUpload[filename]');
			
			$getFileExtension = explode('.', $fileName);

			if(count($getFileExtension > 1))
			{
				$manFileExt = $getFileExtension[count($getFileExtension) - 1];
			}
			
			$rnd = rand(0,9999).strtotime(date("Y-m-d H:i:s")); 
			$fileName = "{$rnd}-{$fileName}";
		
			// Rename file to use generated unique filename
			rename($filePath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
			
			
			$fileupload = new Fileupload;
			
			
			$fileupload->setAttributes(array(
				'generated_filename' => $fileName,
			));
			
			if( $fileupload->save(false) )
			{
				die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileupload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}
	
}

?>