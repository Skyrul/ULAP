<?php

class CustomerCronController extends Controller
{
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionCronDeleteCustomerFiles()
	{
		$models = CustomerFile::model()->nonDeleted()->olderThan90Das()->findAll();
		
		if( $models )
		{
			foreach( $models as $model )
			{
				if( isset($model->fileUpload) )
				{
					$filePath = Yii::getPathOfAlias('webroot') . '/fileupload/' . $model->fileUpload->generated_filename;
					
					if( file_exists($filePath) && unlink($filePath) )
					{
						$model->status = 3;
						$model->save();
					}
				}
			}
		}
	}
	
	public function actionCronDeleteCustomerLists()
	{
		$customerSkills = CustomerSkill::model()->findAll(array(
			'condition' => '
				t.status = 1
				AND t.cancel_date IS NOT NULL
				AND t.cancel_date < DATE_SUB(NOW(), INTERVAL 90 DAY)
			',
		));
		
		if( $customerSkills )
		{
			$transaction = Yii::app()->db->beginTransaction();
			
			try
			{			
				foreach( $customerSkills as $customerSkill )
				{
					$customerSkill->status = 3;
					
					if( $customerSkill->save() )
					{
						if( $customerSkill->lists )
						{
							foreach( $customerSkill->lists as $list )
							{
								if( $list->fileUploads )
								{
									foreach( $list->fileUploads as $fileUpload )
									{
										$filePath = Yii::getPathOfAlias('webroot') . '/leads/' . $fileUpload->fileUpload->generated_filename;
									
										if( file_exists($filePath) && unlink($filePath) )
										{
											$fileUpload->status = 3;
											$fileUpload->save();
										}
									}
								}
							}
						}
					}
				}
			
				$transaction->commit();
			}
			catch(Exception $e)
			{
				$transaction->rollback();
			}
		}
	}	
	
}

?> 