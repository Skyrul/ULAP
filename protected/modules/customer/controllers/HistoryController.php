<?php 

ini_set('memory_limit', '5000M');
set_time_limit(0);

class HistoryController extends Controller
{
	
	public function actionIndex($customer_id, $filter='')
	{
		if( !Yii::app()->user->isGuest && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( $authAccount->getIsCustomer() )
			{
				$customer = Customer::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customer && $customer->id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customer->id));
				}
			}
			
			if( $authAccount->getIsCustomerOfficeStaff() )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$notes = array();
		
		$customer = Customer::model()->findByPk($customer_id);
		
		if( Yii::app()->user->account->getIsCustomer() )
		{
			$models = CustomerHistory::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status=1 AND model_id is NOT NULL',
				// 'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $customer->id,
				),
				'order' => 'date_created DESC',
			));
		}
		elseif( Yii::app()->user->account->getIsCustomerOfficeStaff() )
		{
			$models = CustomerHistory::model()->findAll(array(
				'condition' => 'customer_id = :customer_id AND status=1 AND model_id is NOT NULL',
				// 'condition' => 'customer_id = :customer_id AND status=1',
				'params' => array(
					':customer_id' => $customer->id,
				),
				'order' => 'date_created DESC',
			));
		}
		else 
		{
			if( $filter == 'user' )
			{
				if( Yii::app()->user->account->checkPermission('customer_history_manual_notes','visible') )
				{
					$models = CustomerHistory::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND status=1 AND model_id IS NULL',
						// 'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer->id,
						),
						'order' => 'date_created DESC',
					));
				}
				else
				{
					$models = array();
				}
			}
			else
			{
				if( Yii::app()->user->account->checkPermission('customer_history_manual_notes','visible') )
				{
					$models = CustomerHistory::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND status=1',
						'params' => array(
							':customer_id' => $customer->id,
						),
						'order' => 'date_created DESC',
					));
				}
				else
				{
					$models = CustomerHistory::model()->findAll(array(
						'condition' => 'customer_id = :customer_id AND status=1 AND model_id is NOT NULL',
						'params' => array(
							':customer_id' => $customer->id,
						),
						'order' => 'date_created DESC',
					));
				}
			}
		}
		
		$dataProvider=new CArrayDataProvider($models, array(
			'pagination'=>array(
	            'pageSize'=>1000,
	        ),
		));
		
		$this->render('index', array(
			'notes' => $notes,
			'customer' => $customer,
			'dataProvider' => $dataProvider,
		));
		
	}
	
	
	public function actionCreate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['CustomerHistory']) )
		{
			$authAccount = Yii::app()->user->account;
			
			$model = new CustomerHistory;
			$model->user_account_id = $authAccount->id;
			
			$model->attributes = $_POST['CustomerHistory'];
			
			if( $model->save() )
			{
				if( isset($_POST['fileUploads']) )
				{
					foreach( $_POST['fileUploads'] as $fileId )
					{
						$attachedFile = new CustomerHistoryFile;
						
						$attachedFile->setAttributes(array(
							'customer_history_id' => $model->id,
							'fileupload_id' => $fileId,
						));
						
						$attachedFile->save(false);
					}
				}
				
				$result['status'] = 'success';
				$result['message'] = 'Database has been updated.';
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionUpload()
	{
		// Settings
		$targetDir = 'fileupload';

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
			
			// $file = CUploadedFile::getInstanceByName('FileUpload[filename]');
			 
			$getFileExtension = explode('.', $fileName);
			
			// $generatedFilename = rand(0,9999).strtotime(date("Y-m-d H:i:s")).'.'.$getFileExtension;

			if(count($getFileExtension > 1))
			{
				$manFileExt = $getFileExtension[count($getFileExtension) - 1];
			}
			
			// Rename file to use generated unique filename
			rename($filePath, $targetDir . DIRECTORY_SEPARATOR . $fileName);
			
			$fileUpload = new Fileupload;
			$fileUpload->original_filename = $fileName;
			$fileUpload->generated_filename = $fileName;
			
			if( $fileUpload->save(false) )
			{
				die('{"jsonrpc" : "2.0", "generatedFileUploadId": "'.$fileUpload->id.'", "generatedFilename": "' . $fileName . '", "fileExtension": "' . $manFileExt. '"}');
			}
		}
		
		// Return JSON-RPC response
		die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
	}

	public function actionLeadReport($customer_id, $list_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		$list = Lists::model()->findByPk($list_id);
		
		
		//note: status 3 means lead was moved to the active list
		
		$leadJunkBad = LeadJunk::model()->findAll(array(
			'condition' => 'list_id = :list_id AND is_bad_number = 1 AND status = 1',
			'params'=>array(
				':list_id' => $list_id,
			),
		));
		
		$dataProviderBad = new CArrayDataProvider($leadJunkBad, array(
			'pagination' => array(
				'pageSize' => 1000,
			),
		));
		
		
		$leadJunkDuplicate = LeadJunk::model()->findAll(array(
			'condition' => 'list_id = :list_id AND is_duplicate = 1 AND status = 1',
			'params'=>array(
				':list_id' => $list_id,
			),
		));
		
		$dataProviderDuplicate = new CArrayDataProvider($leadJunkDuplicate, array(
			'pagination' => array(
				'pageSize' => 1000,
			),
		));
		
		$this->render('leadReport',array(
			'customer'=>$customer,
			'list'=>$list,
			'dataProviderBad'=>$dataProviderBad,
			'dataProviderDuplicate'=>$dataProviderDuplicate,
		));
	}
	
}

?>