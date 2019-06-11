<?php 

class CompanyFileController extends Controller
{
	
	public function actionIndex($company_id = null)
	{
		$notes = array();
			

		$company = Company::model()->findByPk($company_id);
		
		$models = CompanyFile::model()->findAll(array(
			'condition' => 'company_id = :company_id AND status=1',
			'params' => array(
				':company_id' => $company->id,
			),
			'order' => 'date_created DESC',
		));
	
		
		$dataProvider=new CArrayDataProvider($models, array(
			// 'pagination'=>array(
	            // 'pageSize'=>10,
	        // ),
		));
		
		
		if(isset($_GET['forward']))
		{
			$this->renderPartial('index', array(
				'notes' => $notes,
				'company' => $company,
				'dataProvider' => $dataProvider,
			));
		}
		else
		{
			$this->render('index', array(
				'notes' => $notes,
				'company' => $company,
				'dataProvider' => $dataProvider,
			));
		}
		
		
	}

	
	public function actionDelete($id, $companyId)
	{
		$authAccount = Yii::app()->user->account;
		
		$model = CompanyFile::model()->findByPk($id);
		
		if( $model )
		{
			$model->status = 3;
			
			if( $model->save(false) )
			{
				Yii::app()->user->setFlash('success', 'File has been deleted successfully.');
				
				$history = new CompanyHistory;
						
				$history->setAttributes(array(
					'model_id' => $model->id, 
					'company_id' => $model->company_id,
					'user_account_id' => $authAccount->id,
					'page_name' => 'Company File',
					'type' => $history::TYPE_DELETED,
					'ip_address' => $_SERVER['REMOTE_ADDR'],
				));

				$history->save(false);	
			}
		}
		
		// $this->redirect(array('index', 'company_id'=>$companyId));
		$this->redirect(array('company/update', 'id'=>$model->company_id));
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
					$companyFile = new CompanyFile;
					
					$companyFile->setAttributes(array(
						'company_id' => $_GET['company_id'],
						'fileupload_id' => $fileUpload->id,
						'user_account_id' => $authAccount->id,
					));
					
					if( $companyFile->save(false) )
					{
						$history = new CompanyHistory;
						
						$history->setAttributes(array(
							'model_id' => $companyFile->id, 
							'company_id' => $_GET['company_id'],
							'user_account_id' => $authAccount->id,
							'page_name' => 'Company File',
							'type' => $history::TYPE_ADDED,
							'ip_address' => $_SERVER['REMOTE_ADDR'],
						));

						$history->save(false);
					}
				}
			}
		}
	}

}

?>