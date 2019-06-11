<?php 

class TrainingLibraryController extends Controller
{

	public function actionIndex()
	{
		$videos = TrainingLibraryFile::model()->findAll(array(
			'condition' => 'status=1 AND type=1',
			'order' => 'date_created DESC',
		));
		
		$videosDataProvider=new CArrayDataProvider($videos, array(
			'pagination'=>array(
	            'pageSize'=>50,
	        ),
		));
		
		$audios = TrainingLibraryFile::model()->findAll(array(
			'condition' => 'status=1 AND type=2',
			'order' => 'date_created DESC',
		));
		
		$audiosDataProvider=new CArrayDataProvider($audios, array(
			'pagination'=>array(
	            'pageSize'=>50,
	        ),
		));
		
		$documents = TrainingLibraryFile::model()->findAll(array(
			'condition' => 'status=1 AND type=3',
			'order' => 'date_created DESC',
		));
		
		$documentsDataProvider=new CArrayDataProvider($documents, array(
			'pagination'=>array(
	            'pageSize'=>50,
	        ),
		));
		
		$links = TrainingLibraryFile::model()->findAll(array(
			'condition' => 'status=1 AND type=4',
			'order' => 'date_created DESC',
		));
		
		$linksDataProvider=new CArrayDataProvider($links, array(
			'pagination'=>array(
	            'pageSize'=>50,
	        ),
		));

		
		if(isset($_GET['forward']))
		{
			$this->renderPartial('index', array(
				'videosDataProvider' => $videosDataProvider,
				'audiosDataProvider' => $audiosDataProvider,
				'documentsDataProvider' => $documentsDataProvider,
				'linksDataProvider' => $linksDataProvider,
			));
		}
		else
		{
			$this->render('index', array(
				'videosDataProvider' => $videosDataProvider,
				'audiosDataProvider' => $audiosDataProvider,
				'documentsDataProvider' => $documentsDataProvider,
				'linksDataProvider' => $linksDataProvider,
			));
		}
	}
	
	public function actionCreate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'event' => array(),
			'html' => $html,
		);
		
		$model = new TrainingLibraryFile;
		$model->type = $_POST['type'];

		if( isset($_POST['TrainingLibraryFile']) )
		{
			$result['status'] = 'error';
			
			$model->attributes = $_POST['TrainingLibraryFile'];
			
			if( empty($_POST['TrainingLibraryFile']['securityGroups']) )
			{
				$model->security_groups = null;
			}
			else
			{
				$model->security_groups = $_POST['TrainingLibraryFile']['securityGroups'];
			}
			 
			if( isset($_FILES['file']) ) 
			{
				$tempFile = $_FILES['file']['tmp_name'];         

				$targetFile =  'trainingLibraryFiles/' . $_FILES['file']['name'];
				
				$extension = pathinfo($targetFile, PATHINFO_EXTENSION);
				
				if( $model->type == 1 )
				{
					$validateFileArray = array('mp4', 'avi');
				}
				elseif( $model->type == 2 )
				{
					$validateFileArray = array('wav', 'mp3', 'aiff');
				}
				else
				{
					$validateFileArray = array('doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'jpg', 'tiff', 'bmp');
				}

				if( in_array($extension, $validateFileArray) )
				{
					if( move_uploaded_file($tempFile, $targetFile) )
					{
						$fileUpload = new Fileupload;
						$fileUpload->original_filename = $_FILES['file']['name'];
						$fileUpload->generated_filename = $_FILES['file']['name'];
						
						if( $fileUpload->save(false) )
						{
							$model->fileupload_id = $fileUpload->id;
							
							if( $model->save(false) )
							{
								$result['status'] = 'success';
								$result['message'] =  'File was sucessfully added.';
							}
							else
							{
								$result['status'] = 'error';
								$result['message'] = 'Database error.';
							}
						}
						else
						{
							$result['message'] = 'Database error.';
						}
					}
					else
					{
						$result['message'] = 'File upload error.';
					}
				}
				else
				{
					$result['message'] = 'Formats allowed are: ' . implode(', ', $validateFileArray);
				}
			}
			else
			{
				$result['message'] = 'File is required.';
			}
		}
		else
		{
			$result['status'] = 'success';
			
			$html = $this->renderPartial('create', array(
				'model' => $model,
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'event' => array(),
			'html' => $html,
		);
		

		if( isset($_POST['TrainingLibraryFile']) )
		{
			$result['status'] = 'error';
			
			$model = TrainingLibraryFile::model()->findByPk($_POST['TrainingLibraryFile']['id']);
			$model->attributes = $_POST['TrainingLibraryFile'];
			
			if( empty($_POST['TrainingLibraryFile']['securityGroups']) )
			{
				$model->security_groups = null;
			}
			else
			{
				$model->security_groups = $_POST['TrainingLibraryFile']['securityGroups'];
			}
			 
			if( isset($_FILES['file']) ) 
			{
				$tempFile = $_FILES['file']['tmp_name'];         

				$targetFile =  'trainingLibraryFiles/' . $_FILES['file']['name'];
				
				$extension = pathinfo($targetFile, PATHINFO_EXTENSION);
				
				if( $model->type == 1 )
				{
					$validateFileArray = array('avi', 'flv', 'wmv', 'mp4', 'mov');
				}
				elseif( $model->type == 2 )
				{
					$validateFileArray = array('mp3', 'wav', 'ogg');
				}
				else
				{
					$validateFileArray = array('xls', 'xlsx', 'doc', 'docx', 'pdf', 'txt', 'ppt', 'pptx');
				}

				if( in_array($extension, $validateFileArray) )
				{
					if( move_uploaded_file($tempFile, $targetFile) )
					{
						$fileUpload = new Fileupload;
						$fileUpload->original_filename = $_FILES['file']['name'];
						$fileUpload->generated_filename = $_FILES['file']['name'];
						
						if( $fileUpload->save(false) )
						{
							$model->fileupload_id = $fileUpload->id;
						}
						else
						{
							$result['message'] = 'Database error.';
						}
					}
					else
					{
						$result['message'] = 'File upload error.';
					}
				}
				else
				{
					$result['message'] = 'File extension must be in ' . implode(', ', $validateFileArray);
				}
			}

			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] =  'File was sucessfully updated.';
			}
			else
			{
				$result['status'] = 'error';
				$result['message'] = 'Database error.';
			}
		}
		else
		{
			$result['status'] = 'success';
			
			$model = TrainingLibraryFile::model()->findByPk($_POST['id']);
			
			$html = $this->renderPartial('update', array(
				'model' => $model,
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionCreateLink()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'event' => array(),
			'html' => $html,
		);
		
		$model = new TrainingLibraryFile;
		$model->type = 4;

		if( isset($_POST['TrainingLibraryFile']) )
		{
			$result['status'] = 'error';
			
			$model->attributes = $_POST['TrainingLibraryFile'];
			
			if( empty($_POST['TrainingLibraryFile']['securityGroups']) )
			{
				$model->security_groups = null;
			}
			else
			{
				$model->security_groups = $_POST['TrainingLibraryFile']['securityGroups'];
			}
			 
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] =  'Link was sucessfully added.';
			}
			else
			{
				$result['status'] = 'error';
				$result['message'] = 'Database error.';
			}
		}
		else
		{
			$result['status'] = 'success';
			
			$html = $this->renderPartial('createLink', array(
				'model' => $model,
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdateLink()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'event' => array(),
			'html' => $html,
		);
		
		if( isset($_POST['TrainingLibraryFile']) )
		{
			$result['status'] = 'error';
			
			$model = TrainingLibraryFile::model()->findByPk($_POST['TrainingLibraryFile']['id']);
			$model->attributes = $_POST['TrainingLibraryFile'];
			
			if( empty($_POST['TrainingLibraryFile']['securityGroups']) )
			{
				$model->security_groups = null;
			}
			else
			{
				$model->security_groups = $_POST['TrainingLibraryFile']['securityGroups'];
			}
			
			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] =  'Link was sucessfully updated.';
			}
			else
			{
				$result['status'] = 'error';
				$result['message'] = 'Database error.';
			}
		}
		else
		{
			$result['status'] = 'success';
			
			$model = TrainingLibraryFile::model()->findByPk($_POST['id']);
			
			$html = $this->renderPartial('updateLink', array(
				'model' => $model,
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionDelete()
	{
		$result = array(
			'status' => 'error',
			'message'
		);
		
		if( isset($_POST['ajax']) && $_POST['id'] )
		{
			$model = TrainingLibraryFile::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				$model->status = 3;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = 'File was deleted successfully.';
				}
				else
				{
					$result['message'] = 'Database error.';
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpload()
	{
		$authAccount = Yii::app()->user->account;
		
		if (!empty($_FILES)) 
		{
			$tempFile = $_FILES['file']['tmp_name'];         

			$targetFile =  'fileupload/' . $_FILES['file']['name'];

			if( move_uploaded_file($tempFile, $targetFile) )
			{
				$fileUpload = new Fileupload;
				$fileUpload->original_filename = $_FILES['file']['name'];
				$fileUpload->generated_filename = $_FILES['file']['name'];
				
				if( $fileUpload->save(false) )
				{

				}
			}
		}
	}

	public function actionDownload($id)
	{
		$model = TrainingLibraryFile::model()->findByPk($id);

		$filePath = Yii::getPathOfAlias('webroot') . '/trainingLibraryFiles/' . $model->fileUpload->generated_filename;
		
		$customerFileDownloadName = null;
		$allowDownload = false;
		
		if(file_exists($filePath))
		{
			$allowDownload = true;
		}
		
		if ( $allowDownload )
		{
			if( !Yii::app()->user->isGuest )
			{
				$authAccount = Yii::app()->user->account;

				$usage = new TrainingLibraryFileUsage;
				
				$usage->setAttributes(array(
					'account_id' => $authAccount->id,
					'security_group' => $authAccount->account_type_id,
					'training_library_file_id' => $model->id,
				));
				
				$usage->save(false);
			}
			
			// required for IE
			if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
			
			$ctype="application/force-download";
			
			header("Pragma: public"); 
			header("Expires: 0");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Cache-Control: private",false); // required for certain browsers
			header("Content-Type: $ctype");

			# change, added quotes to allow spaces in filenames, 
			
			if($customerFileDownloadName !== null)
				header("Content-Disposition: attachment; filename=\"".basename($customerFileDownloadName)."\";" );
			else
				header("Content-Disposition: attachment; filename=\"".basename($filePath)."\";" );
			
			header("Content-Transfer-Encoding: binary");
			header("Content-Length: ".filesize($filePath));

			readfile("$filePath");
		} 
		else
		{
			// Do processing for invalid/non existing files here
			echo 'File not found.';
		}
	}
	
}

?>