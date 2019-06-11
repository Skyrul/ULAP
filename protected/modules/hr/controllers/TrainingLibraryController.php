<?php 

class TrainingLibraryController extends Controller
{

	public function actionIndex()
	{
		$categories = TrainingLibraryCategory::model()->findAll(array(
			'condition' => 'status != 3',
			'order' => 'sort_order ASC',
		));
		
		$this->render('index', array(
			'categories' => $categories,
		));
	}
	
	public function actionAddCategory()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$model = new TrainingLibraryCategory;
		
		if( isset($_POST['TrainingLibraryCategory']) )
		{
			$result['status'] = 'error';
			
			$model->attributes = $_POST['TrainingLibraryCategory'];
			
			if( $model->save(false) )
			{
				$categories = TrainingLibraryCategory::model()->findAll(array(
					'condition' => 'status != 3',
					'order' => 'sort_order ASC',
				));
				
				$result['status'] = 'success';
				$result['message'] =  'Category was sucessfully added.';
				
				$html = $this->renderPartial('ajaxIndex', array(
					'categories' => $categories,
				), true);

				$result['html'] = $html;
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
			
			$html = $this->renderPartial('addCategory', array(
				'model' => $model,
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionEditCategory()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);
		
		$modelId = isset($_POST['id']) ? $_POST['id'] : $_POST['TrainingLibraryCategory']['id'];
		
		$model = TrainingLibraryCategory::model()->findByPk($modelId);
		
		if( isset($_POST['TrainingLibraryCategory']) )
		{
			$result['status'] = 'error';
			
			$model->attributes = $_POST['TrainingLibraryCategory'];
			
			if( $model->save(false) )
			{
				$categories = TrainingLibraryCategory::model()->findAll(array(
					'condition' => 'status != 3',
					'order' => 'sort_order ASC',
				));
				
				$result['status'] = 'success';
				$result['message'] =  'Category was sucessfully updated.';
				
				$html = $this->renderPartial('ajaxIndex', array(
					'categories' => $categories,
				), true);

				$result['html'] = $html;
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
			
			$html = $this->renderPartial('editCategory', array(
				'model' => $model
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionCreate()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'category' => '',
			'event' => array(),
			'html' => $html,
		);
		
		$model = new TrainingLibraryFile;

		if( isset($_POST['TrainingLibraryFile']) )
		{
			$result['status'] = 'error';
			
			$model->attributes = $_POST['TrainingLibraryFile'];
			 
			if( isset($_FILES['file']) ) 
			{
				$tempFile = $_FILES['file']['tmp_name'];         

				$targetFile =  'trainingLibraryFiles/' . $_FILES['file']['name'];
			
				if( move_uploaded_file($tempFile, $targetFile) )
				{
					$fileUpload = new Fileupload;
					$fileUpload->original_filename = $_FILES['file']['name'];
					$fileUpload->generated_filename = $_FILES['file']['name'];
					
					if( $fileUpload->save(false) )
					{
						$model->fileupload_id = $fileUpload->id;
						$model->account_id = Yii::app()->user->account->id;
						
						if( isset($_FILES['thumbnailFile']) ) 
						{
							$thumbTempFile = $_FILES['thumbnailFile']['tmp_name'];         

							$thumbTargetFile =  'trainingLibraryFiles/thumbnails/' . $_FILES['thumbnailFile']['name'];
						
							if( move_uploaded_file($thumbTempFile, $thumbTargetFile) )
							{
								$fileUpload = new Fileupload;
								$fileUpload->original_filename = $_FILES['thumbnailFile']['name'];
								$fileUpload->generated_filename = $_FILES['thumbnailFile']['name'];
								
								if( $fileUpload->save(false) )
								{
									$model->thumbnail_fileupload_id = $fileUpload->id;
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
						
						if( $model->save(false) )
						{
							$result['status'] = 'success';
							$result['message'] =  'File was sucessfully added.';
							
							$result['category'] = ucfirst($model->category->name);
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
				$result['message'] = 'File is required.';
			}
		}
		else
		{
			$result['status'] = 'success';
			
			$model->category_id = $_POST['category_id'];
			
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
			'category' => '',
			'event' => array(),
			'html' => $html,
		);
		

		if( isset($_POST['TrainingLibraryFile']) )
		{
			$result['status'] = 'error';
			
			$model = TrainingLibraryFile::model()->findByPk($_POST['TrainingLibraryFile']['id']);
			$model->attributes = $_POST['TrainingLibraryFile'];
			
			if( isset($_FILES['file']) ) 
			{
				$tempFile = $_FILES['file']['tmp_name'];         

				$targetFile =  'trainingLibraryFiles/' . $_FILES['file']['name'];
			
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
			
			if( isset($_FILES['thumbnailFile']) ) 
			{
				$thumbTempFile = $_FILES['thumbnailFile']['tmp_name'];         

				$thumbTargetFile =  'trainingLibraryFiles/thumbnails/' . $_FILES['thumbnailFile']['name'];
			
				if( move_uploaded_file($thumbTempFile, $thumbTargetFile) )
				{
					$fileUpload = new Fileupload;
					$fileUpload->original_filename = $_FILES['thumbnailFile']['name'];
					$fileUpload->generated_filename = $_FILES['thumbnailFile']['name'];
					
					if( $fileUpload->save(false) )
					{
						$model->thumbnail_fileupload_id = $fileUpload->id;
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

			if( $model->save(false) )
			{
				$result['status'] = 'success';
				$result['message'] =  'File was sucessfully updated.';
				
				$result['category'] = ucfirst($model->category->name);
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
	
	public function actionDelete()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'category' => '',
		);
		
		if( isset($_POST['ajax']) && $_POST['id'] )
		{
			$model = TrainingLibraryFile::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				$result['category'] = ucfirst($model->category->name);
				
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
	
	public function actionToggleLearningCenterCategory()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['value']) && isset($_POST['category_id']) )
		{
			$category = TrainingLibraryCategory::model()->findByPk($_POST['category_id']);
			
			if( $category )
			{
				$category->status = $_POST['value'];
				
				if( $category->save(false) )
				{
					$result['status'] = 'success';
				}
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionDeleteCategory()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['category_id']) )
		{
			$category = TrainingLibraryCategory::model()->findByPk($_POST['category_id']);
			
			if( $category )
			{
				$category->status = 3;
				
				if( $category->save(false) )
				{
					$result['status'] = 'success';
				}
			}
		}
		
		echo json_encode($result);
	}

}

?>