<?php 

class ImportCustomerFilesController extends Controller
{
	
	public function actionIndex()
	{
		$authAccount = Yii::app()->user->account;
		
		ini_set('memory_limit', '512M');
		set_time_limit(0);
		
		$inputFileName =  Yii::app()->basePath.'/../fileupload/import';

		
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
				
				## ONLY WORKS IN WINDOWS SERVER ##
				// $pathDirs = end($exploded);
				// $fileDirs = explode('/',$pathDirs);
				
				$fileDirs= $exploded;
				$customerPrimaryKey = $fileDirs[9];
				$filePath = $fileDirs[9].'/'.$fileDirs[10];
				$file = $fileDirs[10];
				
				$customerKeyHolder[$customerPrimaryKey][$filePath] = $file;
			}
			
			
			foreach($customerKeyHolder as $customerPk => $customerFileData)
			{
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
						echo $file; echo '<br>';
						
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
								$customerFile = new CustomerFile;
								
								$customerFile->setAttributes(array(
									'customer_id' => $customer->id,
									'fileupload_id' => $fileUpload->id,
									'user_account_id' => $authAccount->id,
								));
								
								if( $customerFile->save(false) )
								{
									$history = new CustomerHistory;
									
									$history->setAttributes(array(
										'model_id' => $customerFile->id, 
										'customer_id' => $customer->id,
										'user_account_id' => $authAccount->id,
										'page_name' => 'Customer File',
										'type' => $history::TYPE_ADDED,
									));

									$history->save(false);
								}
							}
						}
						else
						{
							echo '[Existed] <br>';
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
		}
		catch(Exception $e)
		{
			print_r($model->attributes);
			print_r($e);
			
		}	
	}

}

?>