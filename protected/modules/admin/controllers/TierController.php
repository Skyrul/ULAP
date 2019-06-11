<?php

class TierController extends Controller
{

	/**
	 * @return array action filters
	 */
	public function filters()
	{
		return array(
			// 'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
		);
	}

	/**
	 * Specifies the access control rules.
	 * This method is used by the 'accessControl' filter.
	 * @return array access control rules
	 */
	public function accessRules()
	{
		return array(
			// array('allow',  // allow all users to perform 'index' and 'view' actions
				// 'actions'=>array('index','view'),
				// 'users'=>array('*'),
			// ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('ajaxLoadChild', 'addTier', 'ajaxAddTier', 'ajaxEditTier'),
				'users'=>array('@'),
			),
			// array('allow', // allow admin user to perform 'admin' and 'delete' actions
				// 'actions'=>array('admin','delete'),
				// 'users'=>array('admin'),
			// ),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}

	public function actionAjaxLoadChild()
	{
		$tier_ParentSubTier_Id = $_POST['tier_ParentSubTier_Id'];
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);

		$criteria = new CDbCriteria;
		$criteria->compare('parent_tier_id', $tier_ParentSubTier_Id);
		$criteria->compare('status', Tier::STATUS_ACTIVE);
		$childTiers = Tier::model()->findAll($criteria);
		
		if(!empty($childTiers))
		{	
			$result['status'] = 'success';
			
			foreach($childTiers as $childTier)
			{
				$html .= '<li id="'.$childTier->id.'" class="tree-branch">';
					
					$html .= '<div class="tree-branch-header">';
						
						$html .= '<span class="tree-branch-name">';
							$html .= '<i class="icon-folder ace-icon tree-plus"></i>';
							
							$html .= '<span class="tree-label">';
							$html .= $childTier->tier_name;
							$html .= '</span>';
						$html .= '</span>';
						
						$html .= ' <a id="parentTier-'.$childTier->tier_name.'" class="btn btn-minier add-child-tier" tier_ParentTier_Id="'.$childTier->id.'" tier_ParentSubTier_Id="'.$childTier->id.'" tier_Company_Id="'.$childTier->company_id.'" tier_Level="'.$childTier->tier_level.'" tier_Name="'.$childTier->tier_name.'">Add</a>';
						$html .= ' <a class="btn btn-minier edit-tier" id="'.$childTier->id.'" tier_Company_Id="'.$childTier->company_id.'" tier_Name="'.$childTier->tier_name.'">Edit</a>';
					$html .= '</div>';
					
					$html .= '<ul class="tree-branch-children"></ul>';
					
			
				$html .= '</li>';
			}
			
			$result['html'] = $html;
		}
		else
		{			
			$html .= '<li id="" class="tree-branch">';
					
				$html .= '<div class="tree-branch-header">';
					
					$html .= '<span class="tree-branch-name">';
						// $html .= '<i class="icon-folder ace-icon tree-plus"></i>';
						
						$html .= '<span class="tree-label">';
						$html .= ' No tiers available.';
						$html .= '</span>';
					$html .= '</span>';
					
					// $html .= ' <a class="btn btn-minier select-tier" id="" tier_Company_Id="" tier_Name="">Select</a>';
				$html .= '</div>';
				
				$html .= '<ul class="tree-branch-children"></ul>';
				
		
			$html .= '</li>';	
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionAddTier($companyId = null, $parentTierId = null)
	{
		$model = new Tier;
		$model->company_id = $companyId;
		$model->parent_tier_id = $parentTierId;
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='tier-tierForm-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['Tier']))
		{
			$model->attributes = $_POST['Tier'];
			if($model->save())
				$this->redirect(array('view','id'=>$model->company_id));
		}
		
		$this->render('tierForm',array(
			'model' => $model
		));
	}
	
	public function actionAjaxAddTier()
	{ 
		$model = new Tier;
		
		if(isset($_REQUEST['tier_ParentTier_Id']))
			$model->parent_tier_id = $_REQUEST['tier_ParentTier_Id'];
		
		if(isset($_REQUEST['tier_ParentSubTier_Id']))
			$model->parent_sub_tier_id = $_REQUEST['tier_ParentSubTier_Id'];
		
		if(isset($_REQUEST['tier_Company_Id']))
			$model->company_id = $_REQUEST['tier_Company_Id'];
		
		if(isset($_REQUEST['tier_Level']))
			$model->tier_level = $_REQUEST['tier_Level'];		
						
		if(isset($_POST['ajax']) && $_POST['ajax']==='tier-tierForm-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		
		if(isset($_POST['Tier']))
		{
			$model->attributes = $_POST['Tier'];
			$model->status = Tier::STATUS_ACTIVE;
			
			if($model->save())
			{
				// if(empty($model->parent_sub_tier_id))
				// {
					// $model->parent_sub_tier_id = $model->id;
					// $model->parent_tier_id = $model->id;
					// $model->save(false);
				// }
				
				$response = array(
					'success' => true,
					'message' => 'Adding Tier successful!',
					'tier_ParentTier_Id' => $model->parent_sub_tier_id,
					'last_insert_id' => $model->id,
					'html' => $this->renderPartial('_treeBranch',array('tier' => $model), true, false),
					'scenario' => 'add',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Adding Tier error!',
					'scenario' => 'add',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('tierForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/admin/tier/ajaxAddTier'),
		),false,true);
	}
	
	public function actionAjaxEditTier()
	{
		$model = Tier::model()->findByPk($_REQUEST['tier_Id']);
		
		if(isset($_POST['ajax']) && $_POST['ajax']==='tier-tierForm-form')
		{
			echo CActiveForm::validate($model);
			Yii::app()->end();
		}
		
		if(isset($_POST['Tier']))
		{
			$model->attributes = $_POST['Tier'];
			$model->status = Tier::STATUS_ACTIVE;
			
			if($model->save())
			{
				// if(empty($model->parent_sub_tier_id))
				// {
					// $model->parent_sub_tier_id = $model->id;
					// $model->parent_tier_id = $model->id;
					// $model->save(false);
				// }
				
				$response = array(
					'success' => true,
					'message' => 'Editing Tier successful!',
					'tier_Id' => $model->id,
					'tier_Name' => $model->tier_name,
					'scenario' => 'edit',
				);
			}
			else
			{
				$response = array(
					'success' => false,
					'message' => 'Editing Tier error!',
					'scenario' => 'edit',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('tierForm',array(
			'model' => $model,
			'actionController' => Yii::app()->createUrl('/admin/tier/ajaxEditTier',array('tier_Id' => $model->id)),
		),false,true);
	}

	public function actionAjaxCustomerLoadChild()
	{
		$tier_ParentSubTier_Id = $_POST['tier_ParentSubTier_Id'];
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);

		$criteria = new CDbCriteria;
		$criteria->compare('parent_tier_id', $tier_ParentSubTier_Id);
		$criteria->compare('status', Tier::STATUS_ACTIVE);
		$childTiers = Tier::model()->findAll($criteria);
		
		if(!empty($childTiers))
		{	
			$result['status'] = 'success';
			
			foreach($childTiers as $childTier)
			{
				$html .= '<li id="'.$childTier->id.'" class="tree-branch">';
					
					$html .= '<div class="tree-branch-header">';
						
						$html .= '<span class="tree-branch-name">';
							$html .= '<i class="icon-folder ace-icon tree-plus"></i>';
							
							$html .= '<span class="tree-label"> ';
							$html .= $childTier->tier_name;
							$html .= '</span>';
						$html .= '</span>';
						
						$html .= ' <a class="btn btn-minier select-tier" id="'.$childTier->id.'" tier_Company_Id="'.$childTier->company_id.'" tier_Name="'.$childTier->tier_name.'">Select</a>';
					$html .= '</div>';
					
					$html .= '<ul class="tree-branch-children"></ul>';
					
			
				$html .= '</li>';
			}
			
			$result['html'] = $html;
		}
		else
		{			
			$html .= '<li id="" class="tree-branch">';
					
				$html .= '<div class="tree-branch-header">';
					
					$html .= '<span class="tree-branch-name">';
						// $html .= '<i class="icon-folder ace-icon tree-plus"></i>';
						
						$html .= '<span class="tree-label">';
						$html .= ' No tiers available.';
						$html .= '</span>';
					$html .= '</span>';
					
					// $html .= ' <a class="btn btn-minier select-tier" id="" tier_Company_Id="" tier_Name="">Select</a>';
				$html .= '</div>';
				
				$html .= '<ul class="tree-branch-children"></ul>';
				
		
			$html .= '</li>';	
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
}
