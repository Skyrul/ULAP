<?php

class UserAccess
{
	private $moduleActions = array();
	
	public function __construct()
	{
		$this->moduleActions = $this->getModuleActionsByUser();
	}
	
	public static function rules($moduleName, $currentController)
    {
		$userAccess = new UserAccess;
		
		if(isset($userAccess->moduleActions[$moduleName]))
		{
			$moduleControllers = $userAccess->moduleActions[$moduleName];
			
			if(isset($moduleControllers[$currentController]))
			{
				$controllerActions = $moduleControllers[$currentController];
				
				if(isset($controllerActions))
				{
					// print_r($controllerActions);
					return $controllerActions;
				}
			}
		
		}
		
		return array();
	}
	
	public static function hasRule($moduleName, $currentController, $action)
	{
		$controllerActions = self::rules($moduleName, $currentController);
		
		if(in_array($action, $controllerActions))
		{
			return true;
		}
		
		return false;
	}
	
	public function getModuleActionsByUser()
	{
		if(empty($this->moduleActions))
		{
			$modules = array(
				'admin' => array(
					'Campaign' => array('view', 'create','update', 'delete', 'index', 'list'), 
					'Company' => array('view', 'create', 'update', 'delete', 'index', 'list', 'ajaxLoadChild', 'addTier', 'regenerateToken', 'didList', 'ajaxAddDid', 'ajaxEditDid', 'ajaxRemoveDid', 'upload', 'flyerUpload', 'resetPopupLogins', 'redactorUpload'), 
					'Contract' => array('view', 'create', 'update', 'delete', 'index', 'list', 'addGoalVolume', 'addLeadVolume'), 
					'Default' => array('index'), 
					'SkillChild' => array('view', 'create', 'update', 'delete', 'index', 'download'), 
					'SkillChildDisposition' => array('view', 'create', 'update', 'delete', 'index', 'admin'), 
					'SkillChildSchedule' => array('update'), 
					'Skill' => array('view', 'create', 'update', 'delete', 'index', 'list', 'removeSkillAccount', 'addSkillAccount','clone', 'download', 'history','emailSetting','emailSettingCreate','emailSettingUpdate', 'emailSettingDelete','deleteEmailAttachment'), 
					'SkillDisposition' => array('view', 'create', 'update', 'delete', 'index', 'admin', 'ajaxUpdateRetryIntervalOptions'), 
					'SkillDispositionDetail' => array('index', 'view', 'create','update', 'delete'), 
					'SkillSchedule' => array('update', 'periodAssignment', 'addNewSchedule'), 
					'Tier' => array('ajaxLoadChild', 'addTier', 'ajaxAddTier', 'ajaxEditTier'), 
					'Survey' => array('view', 'create', 'update', 'delete', 'index', 'list'), 
					
				),
				'customer' => array(
					'Billing' => array('index','createCreditCard', 'updateCreditCard', 'viewCreditCard', 'deleteCreditCard', 'setDefaultCreditCard', 'processTransaction', 'voidTransaction', 'refundTransaction', 'partialRefundTransaction'),
					'Calendar' => array(),
					'CustomerOffice' => array('view', 'create', 'update', 'delete', 'index', 'list', 'admin'),
					'CustomerOfficeStaff' => array('view', 'create', 'update', 'delete', 'index', 'list', 'admin', 'AddExistingStaff', 'regenerateToken'),
					'CustomerSkill' => array('index','ajaxAddSkill', 'delete', 'toggleSkillChild','toggleCustomerSkillIsCustomSchedule','customScheduleUpdate','dialingSetting', 'startEndDate', 'addNewSchedule', 'getContractByCompanyAndSkill', 'toggleCustomerSkillLevel', 'toggleCustomerSkillSubsidyLevel', 'toggleCustomerSkillSubsidy', 'toggleCustomerSkillIsContractHold', 'customerContractSubsidy', 'cancel', 'promo', 'download'),
					'Data' => array('view', 'create', 'update', 'delete', 'index', 'list', 'customerSummary', 'fileupload', 'TiersJsonSearch', 'tiersModalSearch','upload', 'uploadVoice', 'voiceRecord', 'account', 'regenerateToken', 'checkPhoneTimeZone', 'releaseLock'),
					'Default' => array('index'),
					'History' => array(),
					'Insight' => array('index', 'recycleLeads', 'recycle', 'recertify', 'staffList', 'recertifyRemoveLead', 'recycleRemoveLead', 'cancel'),
					'EmailSetting' => array('index','deleteEmailAttachment'),
				),
				'hr' => array(
					'AccountUser' => array('view', 'create', 'update', 'delete', 'index', 'list', 'fileupload', 'releaseLock'),
					'Default' => array('index'),
				),
			);
		}
		
		if(!Yii::app()->user->isGuest)
		{
			if(Yii::app()->user->account->getIsCustomer())
			{
				foreach($modules['admin'] as $controller => $actions)
				{
					$modules['admin'][$controller] = array('');
				}
				
				foreach($modules['hr'] as $controller => $actions)
				{
					$modules['hr'][$controller] = array('');
				}
				
				
				$modules['customer']['Billing'] = array('index','createCreditCard', 'viewCreditCard', 'setDefaultCreditCard', 'voidTransaction', 'refundTransaction', 'partialRefundTransaction');
				$modules['customer']['CustomerSkill'] = array('index','customScheduleUpdate', 'dialingSetting', 'startEndDate', 'toggleSkillChild', 'toggleCustomerSkillIsCustomSchedule', 'toggleCustomerSkillLevel', 'toggleCustomerSkillSubsidy', 'toggleCustomerSkillIsContractHold', 'customerContractSubsidy'); 
				// print_r($modules['CustomerSkill']);
			}
			
			if(Yii::app()->user->account->getIsAdmin())
			{
				$modules['customer']['Billing'] = array('index','createCreditCard', 'updateCreditCard', 'deleteCreditCard', 'setDefaultCreditCard', 'processTransaction', 'voidTransaction', 'refundTransaction', 'partialRefundTransaction');
			}
		}
		
		$this->moduleActions = $modules;
		
		return $this->moduleActions;
	}
}

