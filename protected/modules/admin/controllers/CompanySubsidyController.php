<?php

class CompanySubsidyController extends Controller
{
	
	public function actionSubsidyList($company_id)
	{
		$company = $this->loadModel($company_id);
		
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company->id);
		$companySubsidys = CompanySubsidy::model()->findAll($criteria);
		
		$this->renderPartial('subsidyList',array(
			'companySubsidys' => $companySubsidys,
		));
	}
	
	public function actionAjaxAddSubsidy($company_id)
	{
		$contractOptions = array();		
		
		$company = $this->loadModel($company_id);

		$model = new CompanySubsidy;
		$model->company_id = $company->id;
	
		if(isset($_POST['ajax']) && $_POST['ajax']==='companySubsidy-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['CompanySubsidy']))
		{			
			$model->attributes = $_POST['CompanySubsidy'];
			
			if($model->save())
			{
				
				$this->processCompanySubsidyLevels($company, $model);
				
				
				$response = array(
					'success' => true,
					'message' => 'Adding Subsidy successful!',
					'scenario' => 'add',
				);
			}
			else
			{
				print_r($model->getErrors());
				$response = array(
					'success' => false,
					'message' => 'Adding Subsidy error!',
					'scenario' => 'add',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('subsidyForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/admin/companySubsidy/ajaxAddSubsidy',array('company_id' => $company->id)),
			'contractOptions' => $contractOptions,
		),false,true);
	}
	
	public function processCompanySubsidyLevels($company, $subsidy)
	{
		$deleteNotSubsidyLevels = array();
			
		if(isset($_POST['CompanySubsidyLevel']))
		{
			foreach($_POST['CompanySubsidyLevel'] as $key => $companySubsidyLevel)
			{
				if (strpos($key,'new') !== false) {
					
					$csl = new CompanySubsidyLevel;
					$csl->attributes = $companySubsidyLevel;
					$csl->company_id = $company->id;
					$csl->subsidy_id = $subsidy->id;
					
					if(!$csl->save(false))
					{
						print_r($csl->getErrors());
						exit;
					}
					
					$deleteNotSubsidyLevels[$csl->id] = $csl->id;
				}
				else
				{
					$deleteNotSubsidyLevels[$key] = $key;
					
					$csl = CompanySubsidyLevel::model()->find(array(
						'condition'=> 'company_id = :company_id AND subsidy_id = :subsidy_id AND id = :id_key',
						'params'=>array(
							':company_id' => $company->id,
							':subsidy_id' => $subsidy->id,
							':id_key' => $key,
						),
					));
					
					if($csl === null)
					{
						$csl = new CompanySubsidyLevel;
					}
					
					$csl->attributes = $companySubsidyLevel;
					$csl->company_id = $company->id;
					$csl->subsidy_id = $subsidy->id;
					
					if(!$csl->save(false))
					{
						print_r($csl->getErrors());
						exit;
					}
				}
			}
		}
		
		
		if(!empty($deleteNotSubsidyLevels))
		{
			$criteria = new CDbCriteria;
			$criteria->compare('company_id',$company->id);
			$criteria->compare('subsidy_id',$subsidy->id);
			$criteria->addNotInCondition('id', $deleteNotSubsidyLevels);
			
			$subsidyLevelToBeDeleted = CompanySubsidyLevel::model()->findAll($criteria);
			
			if(!empty($subsidyLevelToBeDeleted))
			{
				foreach($subsidyLevelToBeDeleted as $sltbd)
				{
					$sltbd->delete();
				}
			}
		}
	}
	
	public function actionAjaxEditSubsidy()
	{
		$model = CompanySubsidy::model()->findByPk($_REQUEST['companysubsidy_id']);
		
		$contractOptions = array();
		
		$contracts = Contract::model()->findAll(array(
			'condition' => 'company_id = :company_id AND skill_id = :skill_id',
			'params' => array(
				':skill_id' => $model->skill_id,
				':company_id' => $model->company_id,
			),
		));
		
		if( $contracts )
		{
			foreach( $contracts as $contract )
			{
				$contractOptions[$contract->id] = $contract->contract_name; 
			}
		}
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='companySubsidy-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['CompanySubsidy']))
		{
			$model->attributes = $_POST['CompanySubsidy'];
			
			if($model->save())
			{
				$this->processCompanySubsidyLevels($model->company, $model);
				
				$response = array(
					'success' => true,
					'message' => 'Editing Subsidy successful!',
					'companydid_id' => $model->id,
					'scenario' => 'edit',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Editing Subsidy error!',
					'scenario' => 'edit',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('subsidyForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/admin/companySubsidy/ajaxEditSubsidy',array('companysubsidy_id' => $model->id)),
			'contractOptions' => $contractOptions,
		),false,true);
	}
	
	public function actionAjaxRemoveSubsidy()
	{
		$model = CompanySubsidy::model()->findByPk($_REQUEST['companysubsidy_id']);
		if($model !== null)
			$model->delete();
		
		
	}
	
	public function actionAddNewSubsidyLevel($ctr)
	{
		$nameCtr = 'new-'.$ctr;
		
		$this->renderPartial('_subsidyLevels',array(
			'companySubsidyLevel' => new CompanySubsidyLevel,
			'name' => $nameCtr,
		));
		
		if(isset($_REQUEST['ajax']))
			Yii::app()->end();
	}
	
	/**
	 * Returns the data model based on the primary key given in the GET variable.
	 * If the data model is not found, an HTTP exception will be raised.
	 * @param integer $id the ID of the model to be loaded
	 * @return Company the loaded model
	 * @throws CHttpException
	 */
	public function loadModel($id)
	{
		$model=Company::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	/**
	 * Performs the AJAX validation.
	 * @param Company $model the model to be validated
	 */
	protected function performAjaxValidation($model)
	{
		if(isset($_POST['ajax']) && $_POST['ajax']==='company-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
	}
}