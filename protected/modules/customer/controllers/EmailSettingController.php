<?php

class EmailSettingController extends Controller
{
	public function accessRules()
	{
		return array(
			// array('allow',  // allow all users to perform 'index' and 'view' actions
				// 'actions'=>array('index','view'),
				// 'users'=>array('*'),
			// ),
			array('allow', // allow authenticated user to perform 'create' and 'update' actions
				'actions'=>array('index','create','update'),
				'users'=>array('@'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex($customer_id, $customer_skill_id)
	{
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		$skillEmailTemplate = new SkillEmailTemplate;
		
		$attachments = CustomerSkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'customer_skill_id = :customer_skill_id',
			'params' => array(
				':customer_skill_id' => $customerSkill->id,
			),
		));
		
		$customerSkillEmailTemplate = new CustomerSkillEmailTemplate;
		$customerSkillEmailTemplate->customer_skill_id = $customerSkill->id;
		
		$this->renderPartial('index', array(
			'customer' => $customer,
			'customerSkill' => $customerSkill,
			'customerSkillEmailTemplate' => $customerSkillEmailTemplate,
			'attachments' => $attachments,
		));
	}
	
	
	public function actionCreate($customer_id, $customer_skill_id, $tab)
	{
		
		$customer = Customer::model()->findByPk($customer_id);
		$customerSkill = CustomerSkill::model()->findByPk($customer_skill_id);
		
		if($customer === null || $customerSkill === null)
			throw new CHttpException("403", "Page not found");
		
		
		$attachments = null;
		$customerSkillEmailTemplate = new CustomerSkillEmailTemplate;
		$customerSkillEmailTemplate->customer_skill_id = $customerSkill->id;
		
		// if(!empty($cset))
		// {
			// $attachments = CustomerSkillEmailTemplateAttachment::model()->findAll(array(
				// 'condition' => 'customer_skill_email_template_id = :customer_skill_email_template_id',
				// 'params' => array(
					// ':customer_skill_email_template_id' => $cset->id,
				// ),
			// ));
			
			// $customerSkillEmailTemplate = $cset;
		// }
		
		
		if( isset($_POST['CustomerSkillEmailTemplate']) )
		{
			if($_POST['CustomerSkillEmailTemplate']['customer_skill_id'] == $customerSkill->id)
			{
				$customerSkillEmailTemplate->customer_id = $customer->id;
				$customerSkillEmailTemplate->attributes = $_POST['CustomerSkillEmailTemplate'];
				
				// echo '<pre>';
				// print_r($_GET); 
				// print_r($_POST['CustomerSkillEmailTemplate']); 
				// echo '</pre>';
				// exit;
				if($customerSkillEmailTemplate->save())
				{
					/* if( isset($_POST['fileUploads']) )
					{
						foreach( $_POST['fileUploads'] as $fileUploadId)
						{
							$emailAttachment = new CustomerSkillEmailTemplateAttachment;
							
							$emailAttachment->setAttributes(array(
								'customer_skill_id' => $customerSkill->id,
								'customer_skill_email_template_id' => $customerSkillEmailTemplate->id,
								'fileupload_id' => $fileUploadId,
							));
							
							$emailAttachment->save(false);
						}
					} */
					
					Yii::app()->user->setFlash('success', "Email Template saved");
					$this->redirect(array('customerSkill/index','customer_id'=>$customer->id,'tab'=>'emailSetting'));
					
				}
			}
			
			
		}
			
		$this->renderPartial('emailSettingCreate', array(
			'tab' => $tab,
			'customer' => $customer,
			'customerSkill' => $customerSkill,
			'customerSkillEmailTemplate' => $customerSkillEmailTemplate,
		));
	} 
	
	public function actionDeleteEmailAttachment($attachment_id)
	{
		$seta = SkillEmailTemplateAttachment::model()->findByPK($attachment_id);
		$seta->delete();
		
		// $this->redirect(array('update','id'=>$seta->skill_id,'tab'=>'emailSettingAttachment'));
	}
}
