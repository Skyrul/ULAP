<?php
	class AccountingSideMenu extends CWidget {
		public $active = '';

		public function run() {
			
			$menu  = array();
			
			if( Yii::app()->user->account->checkPermission('accounting_payroll_file_tab','visible') )
			{
				$menu['accounting'] = array('label'=>'Payroll File', 'url'=>array('/accounting/accounting/index'));
			}
			
			if( Yii::app()->user->account->checkPermission('accounting_billing_windows_tab','visible') )
			{
				$menu['billingWindow'] = array('label'=>'Billing Windows', 'url'=>array('/accounting/accounting/billingWindow'));
			}
			
			if( Yii::app()->user->account->checkPermission('accounting_enrollment_listing_tab','visible') )
			{
				$menu['enrollmentListing'] = array('label'=>'Enrollment Listing', 'url'=>array('/salesManagement'));
			}
			
			if( Yii::app()->user->account->checkPermission('accounting_sales_goals_tab','visible') )
			{
				$menu['salesGoals'] = array('label'=>'Sales Goals', 'url'=>array('/salesManagement/goals'));
			}
			
			if( Yii::app()->user->account->checkPermission('accounting_exception_punches_tab','visible') )
			{
				$menu['timeKeeping'] = array('label'=>'Exception Punches', 'url'=>array('/accounting/accounting/timeKeeping'));
			}
			
			// if( Yii::app()->user->account->checkPermission('accounting_exception_punches_tab','visible') )
			// {
				$menu['promos'] = array('label'=>'Promos', 'url'=>array('/accounting/promos/index'));
			// }
			
			
			
			// $menu = array(
				// 'timeKeeping' => array('label'=>'Exception Punches', 'url'=>array('accounting/timeKeeping') ),
				// 'accounting' => array('label'=>'Payroll File', 'url'=>array('accounting/index') ),
				// 'enrollment' => array('label'=>'Enrollment', 'url'=>'#' ),
				// 'billingWindow' => array('label'=>'Billing Windows', 'url'=>array('accounting/billingWindow') ),
			// );
			
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