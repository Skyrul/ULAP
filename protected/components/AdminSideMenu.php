<?php
	class AdminSideMenu extends CWidget {
		public $active = '';

		public function run() {
			
			$menu = array();
			
			if( Yii::app()->user->account->checkPermission('structure_companies_tab','visible') )
			{
				$menu['company'] = array('label'=>'Companies', 'url'=>array('company/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_skills_tab','visible') )
			{
				$menu['skill'] = array('label'=>'Skills', 'url'=>array('skill/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_campaign_tab','visible') )
			{
				$menu['campaign'] = array('label'=>'Campaigns', 'url'=>array('campaign/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_contract_tab','visible') )
			{
				$menu['contract'] = array('label'=>'Contracts', 'url'=>array('contract/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_enrollment_tab','visible') )
			{
				$menu['enrollment'] = array('label'=>'Enrollment', 'url'=>array('enrollment/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_dnc_holidays_tab','visible') )
			{
				$menu['federalHolidays'] = array('label'=>'DNC Federal Holidays', 'url'=>array('dncHoliday/federalHolidays') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_dnc_holidays_tab','visible') )
			{
				$menu['stateHolidays'] = array('label'=>'DNC State Holidays', 'url'=>array('dncHoliday/stateHolidays') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_state_cellphone_dnc_tab','visible') )
			{
				$menu['stateCellPhoneDnc'] = array('label'=>'State Cellphone DNC', 'url'=>array('stateCellPhoneDnc/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_state_schedule_tab','visible') )
			{
				$menu['stateSchedule'] = array('label'=>'State Schedule', 'url'=>array('stateSchedule/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_survey_tab','visible') )
			{
				$menu['survey'] = array('label'=>'Surveys', 'url'=>array('survey/index') );
			}
			
			if( Yii::app()->user->account->checkPermission('structure_phone_search_tab','visible') )
			{
				$menu['phoneSearch'] = array('label'=>'Search Phone Number', 'url'=>array('phoneSearch/index') );
			}
			
			if( $menu )
			{
				foreach($menu as $moduleController => $items)
				{
					if($moduleController == $this->active)
					{
						$menu[$moduleController]['active'] = true;
					}
				}
			}
			
			Yii::app()->controller->menu = $menu;
		} 
	}
?>