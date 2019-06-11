<?php

class CompanyTierSubsidyController extends Controller
{
	
	public function actionSubsidyList($company_id, $tier_id = null)
	{
		$company = $this->loadCompanyModel($company_id);
		
		$criteria = new CDbCriteria;
		$criteria->compare('company_id', $company->id);
		
		if($tier_id !== null)
		{
			$tier = $this->loadTierModel($tier_id);
			$criteria->compare('tier_id', $tier->id);
		}
		
		$tierSubsidys = TierSubsidy::model()->findAll($criteria);
		
		$this->renderPartial('subsidyList',array(
			'tierSubsidys' => $tierSubsidys,
		));
	}
	
	public function actionAjaxAddSubsidy($company_id, $tier_id = null)
	{
		$contractOptions = array();
		
		$company = $this->loadCompanyModel($company_id);
		$model = new TierSubsidy;
		$model->company_id = $company->id;
		
		if( !empty($tier_id) )
		{
			$tier = $this->loadTierModel($tier_id);
			$model->tier_id = $tier->id;
		}
	
		if(isset($_POST['ajax']) && $_POST['ajax']==='tierSubsidy-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['TierSubsidy']))
		{
			$model->attributes = $_POST['TierSubsidy'];
			
			if($model->save())
			{
				$this->processCompanySubsidyLevels($company, $tier, $model);
				$response = array(
					'success' => true,
					'message' => 'Adding Tier Subsidy successful!',
					'scenario' => 'add',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Adding Tier Subsidy error!',
					'scenario' => 'add',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('subsidyForm',array(
			'model' => $model,
			'company' => $company,
			'actionController' => Yii::app()->createUrl('/admin/companyTierSubsidy/ajaxAddSubsidy',array('company_id' => $company->id, 'tier_id' => $tier_id)),
			'contractOptions' => $contractOptions,
		),false,true);
	}
	
	public function processCompanySubsidyLevels($company, $tier, $subsidy)
	{
		$deleteNotSubsidyLevels = array();
			
		if(isset($_POST['TierSubsidyLevel']))
		{
			foreach($_POST['TierSubsidyLevel'] as $key => $tierSubsidyLevel)
			{
				if (strpos($key,'new') !== false) {
					
					$csl = new TierSubsidyLevel;
					$csl->attributes = $tierSubsidyLevel;
					$csl->company_id = $company->id;
					$csl->tier_id = $tier->id;
					$csl->tier_subsidy_id = $subsidy->id;
					
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
					
					$csl = TierSubsidyLevel::model()->find(array(
						'condition'=> 'company_id = :company_id AND tier_id = :tier_id AND tier_subsidy_id = :tier_subsidy_id AND id = :id_key',
						'params'=>array(
							':company_id' => $company->id,
							':tier_id' => $tier->id,
							':tier_subsidy_id' => $subsidy->id,
							':id_key' => $key,
						),
					));
					
					if($csl === null)
					{
						$csl = new TierSubsidyLevel;
					}
					
					$csl->attributes = $tierSubsidyLevel;
					$csl->company_id = $company->id;
					$csl->tier_id = $tier->id;
					$csl->tier_subsidy_id = $subsidy->id;
					
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
			$criteria->compare('tier_id',$tier->id);
			$criteria->compare('tier_subsidy_id',$subsidy->id);
			$criteria->addNotInCondition('id', $deleteNotSubsidyLevels);
			
			$subsidyLevelToBeDeleted = TierSubsidyLevel::model()->findAll($criteria);
			
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
		$model = TierSubsidy::model()->findByPk($_REQUEST['tiersubsidy_id']);
		
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
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='tierSubsidy-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['TierSubsidy']))
		{
			$model->attributes = $_POST['TierSubsidy'];
			
			if($model->save())
			{
				
				$this->processCompanySubsidyLevels($model->company, $model->tier, $model);
				
				$response = array(
					'success' => true,
					'message' => 'Editing Tier Subsidy successful!',
					'tiersubsidy_id' => $model->id,
					'scenario' => 'edit',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Editing Tier Subsidy error!',
					'scenario' => 'edit',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('subsidyForm',array(
			'model' => $model,
			'company' => $model->company,
			'actionController' => Yii::app()->createUrl('/admin/companyTierSubsidy/ajaxEditSubsidy',array('tiersubsidy_id' => $model->id)),
			'contractOptions' => $contractOptions,
		),false,true);
	}
	
	public function actionAjaxRemoveSubsidy()
	{
		$model = TierSubsidy::model()->findByPk($_REQUEST['tiersubsidy_id']);
		if($model !== null)
			$model->delete();
		
	}
	
	public function actionAddNewSubsidyLevel($ctr)
	{
		$nameCtr = 'new-'.$ctr;
		
		$this->renderPartial('_subsidyLevels',array(
			'tierSubsidyLevel' => new TierSubsidyLevel,
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
	public function loadCompanyModel($id)
	{
		$model=Company::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}

	public function loadTierModel($id)
	{
		$model=Tier::model()->findByPk($id);
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