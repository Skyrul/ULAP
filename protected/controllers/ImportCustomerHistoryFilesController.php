<?php 

class ImportCustomerHistoryFilesController extends Controller
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
				
				// ## ONLY WORKS IN WINDOWS SERVER ##
				// $pathDirs = end($exploded);
				// $fileDirs = explode('\\',$pathDirs);
				// $customerPrimaryKey = $fileDirs[1];
				// $filePath = $fileDirs[1].'/'.$fileDirs[2];
				// $file = $fileDirs[2];
				
				// ## ONLY WORKS IN LINUX SERVER ##
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
									$history = new CustomerHistory;
									
									$history->setAttributes(array(
										'customer_id' => $customer->id,
										'user_account_id' => null,
										'content' => 'System Imported',
									));

									if($history->save(false))
									{
										$attachedFile = new CustomerHistoryFile;
						
										$attachedFile->setAttributes(array(
											'customer_history_id' => $history->id,
											'fileupload_id' => $fileUpload->id,
										));
										
										if($attachedFile->save(false))
											echo '[Customer History File created...] <br>';
									}
							}
						}
						else
						{
							$customerHistory = null;
							
							echo '[Existed] <br>';
							echo '[Checking Customer My File Entry] <br>';
							
							## delete entry from Customer File ##
							$criteria = new CDbCriteria;
							$criteria->compare('customer_id', $customer->id);
							$criteria->compare('fileupload_id', $fileUpload->id);
							
							$customerFile = CustomerFile::model()->find($criteria);
							
							if($customerFile !== null)
							{
								echo '[Customer My File found... deleting...] <br>';
								
								##get customer history to be used in creating customer history file ##
								$criteria = new CDbCriteria;
								$criteria->compare('model_id', $customerFile->id);
								$criteria->compare('customer_id', $customer->id);
								$criteria->compare('type', CustomerHistory::TYPE_ADDED);
								
								$customerHistory = CustomerHistory::model()->find($criteria);
								
								if($customerHistory !== null)
									echo '[Customer history found...] <br>';
								
								if($customerFile->delete())
									echo '[Customer My File deleted...] <br>';
							}
							
							## check if customer history was created ##
							if($customerHistory === null)
							{
								$history = new CustomerHistory;
									
								$history->setAttributes(array(
									'customer_id' => $customer->id,
									'user_account_id' => null,
									'content' => 'System Imported',
								));

								if($history->save(false))
								{
									$customerHistory = $history;
									echo '[New Customer history created...] <br>';
								}
							}
							else
							{
								// 'model_id' => $customerFile->id, 
										// 'customer_id' => $customer->id,
										// 'user_account_id' => $authAccount->id,
										// 'page_name' => 'Customer File',
										// 'type' => $history::TYPE_ADDED,
										
								$customerHistory->model_id = null;
								$customerHistory->page_name = null;
								$customerHistory->user_account_id = null;
								$customerHistory->save(false);
							}
							
							if($customerHistory !== null)
							{
								echo '[Creating entry in Customer History File...] <br>';
								
								$attachedFile = new CustomerHistoryFile;
							
								$attachedFile->setAttributes(array(
									'customer_history_id' => $customerHistory->id,
									'fileupload_id' => $fileUpload->id,
								));
								
								if($attachedFile->save(false))
								{
									echo '[Customer history file created...] <br>';
								}
							}
							else
							{
								echo '[Error: Customer history not found, File not saved!] <br>';
							}
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