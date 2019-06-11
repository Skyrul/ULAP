<?php
	class CompanySideMenu extends CWidget {
		public $active = '';
		public $company;
		
		public function run() {
			
			$company = $this->company;
			
			$menu = array();
			
			if(empty($company))
			{
				$menu = array(
					'company' => array('label'=> 'Company', 'url'=>array('companyFile/index')),
				);
			}
			else
			{	
				$menu = array(
					// 'insight' => array('label'=>'Insight', 'url'=> array('/customer/insight/index','customer_id'=>$customer->id)),
					// 'calendar_page' => array('label'=>'Calendar', 'url'=> array('/calendar/index','customer_id'=>$customer->id)),
					// 'lead' => array('label'=>'Leads', 'url'=> array('/customer/leads/index','customer_id'=>$customer->id)),
					// 'report' => array('label'=>'Reports', 'url'=> array('/customer/reports/index','customer_id'=>$customer->id)),
					// 'billing' => array('label'=>'Billing', 'url'=> array('/customer/billing/index', 'customer_id'=>$customer->id)),
					// 'customerSkill' => array('label'=> 'Skills', 'url'=>array('/customer/customerSkill/index','customer_id' => $customer->id)),
					// 'customerOffice' => array('label'=>'Offices', 'url'=> array('customerOffice/index','customer_id'=>$customer->id)),
					// 'customerOfficeStaff' => array('label'=>'Staff', 'url'=> array('customerOfficeStaff/index','customer_id'=>$customer->id)),
					// 'customer' => array('label'=> 'Setup', 'url'=>array('/customer/data/view','id' => $customer->id)),
					// 'calendar' => array('label'=>'Offices', 'url'=> array('/customer/calendar/index','customer_id'=>$customer->id)),
					'customerFiles' => array('label'=>'My Files', 'url'=> array('/company/companyFile/index', 'company_id'=>$company->id)),
					'history' => array('label'=>'History', 'url'=> array('/company/history/index', 'company_id'=>$company->id)),
					'learningCenter' => array('label'=>'Learning Center Manager', 'url'=> array('/company/learningCenter/index', 'company_id'=>$company->id)),
				);
			}
			
			
	
			// foreach($menu as $moduleController => $items)
			// {
				
				// if($moduleController == $this->active )
				// {
					// echo $moduleController; 
				// echo $this->active; exit;
				
					// $menu[$moduleController]['active'] = true;
				// }
			// }
			
			Yii::app()->controller->menu = $menu;
		}
	}
?>