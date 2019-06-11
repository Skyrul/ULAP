<?php 

class ImportCustomerReportFilesController extends Controller
{
	
	public function actionIndex()
	{
		$authAccount = Yii::app()->user->account;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		$inputFileName =  Yii::app()->basePath.'/../fileupload/report';

		
		$transaction = Yii::app()->db->beginTransaction();
		$customerNotAdded = array();
		try
		{
			
			$fileHelper = CFileHelper::findFiles($inputFileName,$options=array ( ));
			
			echo '<pre>';
			
			$customerKeyHolder = array();
			foreach($fileHelper as $files)
			{
				
				$exploded = explode('/',$files);
				
				// ## ONLY WORKS IN WINDOWS SERVER ##
				// $pathDirs = end($exploded);
				// print_r($pathDirs); exit;
				// $fileDirs = explode('\\',$pathDirs);
				// $customerPrimaryKey = $fileDirs[1];
				// $filePath = $fileDirs[1].'/'.$fileDirs[2];
				// $file = $fileDirs[2];
				// exit;
				
				// ## ONLY WORKS IN LINUX SERVER ##
				$fileDirs= $exploded;
				// print_r($fileDirs); exit;
				$customerPrimaryKey = $fileDirs[9];
				$filePath = $fileDirs[9].'/'.$fileDirs[10];
				$file = $fileDirs[10];
				
				$explodedFileName = explode('_',$file, 2);
				$fileName = end($explodedFileName);
				
				$customerKeyHolder[$customerPrimaryKey][$filePath] = $fileName;
			}
			
			
			foreach($customerKeyHolder as $customerPk => $customerFileData)
			{
				if($customerPk != '7647600c-3a61-43e8-84a9-39755db94817')
					continue;
				
				echo '<br>';
				echo '--- '.$customerPk.' ---';
				echo '<br>';
				
				$criteria = new CDbCriteria;
				$criteria->compare('import_customer_primary_key', $customerPk);
				$customer = Customer::model()->find($criteria);
				
				if($customer !== null)
				{
					foreach($customerFileData as $path => $file)
					{
						echo $path; echo '<br>';
						// echo $file; echo '<br>';
						
						$criteria = new CDbCriteria;
						$criteria->compare('original_filename', $file);
						$criteria->compare('generated_filename', $path);
						
						$fileUpload = Fileupload::model()->find($criteria);
						
						if($fileUpload === null)
						{
							$fileUpload = new Fileupload;
							$fileUpload->original_filename = $file;
							$fileUpload->generated_filename = $path;
							
							if( $fileUpload->save(false) )
							{
								$customerReport = new CustomerReport;
				
								$customerReport->setAttributes(array(
									'customer_id' => $customer->id,
									'fileupload_id' => $fileUpload->id,
									'user_account_id' => null,
									'status' => 1,
								));
								
								if($customerReport->save(false))
								{
									echo '[Customer Report File created...] <br>';
									
									$explodedVar = explode(' - ',$file);
									$explodedFileName = end($explodedVar);
									$explodedFileName = explode('.',$explodedFileName);
									
									$dateCreated = date("Y-m-d 00:00:00", strtotime(str_replace('-','/',$explodedFileName[0])) ); 
									$customerReport->date_created = $dateCreated;
									$customerReport->save(false);
								}
							}
						}
						else
						{
							echo '[Existed] <br>';
							
							$criteria = new CDbCriteria;
							$criteria->compare('fileupload_id', $fileUpload->id);
							$customerReport = CustomerReport::model()->find($criteria);
							
							$explodedFileName = end(explode(' - ',$file));
							$explodedFileName = explode('.',$explodedFileName);
							
							$dateCreated = date("Y-m-d 00:00:00", strtotime(str_replace('-','/',$explodedFileName[0])) );
							$customerReport->date_created = $dateCreated;
							$customerReport->save(false);
						}
					}
				}
				else
				{
					echo '[Not found] <br>';
					$customerNotFound[$customerPk] = 'No of Files: '.count($customerKeyHolder[$customerPk]);
				}
			}
		
			$transaction->commit();
			echo 'Success';
			echo '<br><br>';
			echo '<pre>';
			
			print_r($customerNotFound);
			// foreach($customerNotFound as $notAdded)
			// {
				// print_r($notAdded);
			// }
			echo '</pre>';
			exit;
		}
		catch(Exception $e)
		{
			print_r($e);
			
		}	
	}

}

?>