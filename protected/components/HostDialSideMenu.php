<?php
	class HostDialSideMenu extends CWidget {
		public $active = '';
		public $customer;
		
		public function run() {
			
			$customer = $this->customer;
			
			$menu = array();
			
			if( empty($customer) )
			{
				$menu = array(
					'hostDial' => array('label'=> 'Host Dial', 'url'=>array('data/index')),
				);
			}
			else
			{
				$menu = array();
				
				// if( Yii::app()->user->account->checkPermission('customer_dashboard_tab','visible') )
				// {
					$menu['insight'] = array('label'=>'Dashboard', 'url'=> array('/hostDial/insight/index', 'customer_id'=>$customer->id)); 
				// }
				
				// if( Yii::app()->user->account->checkPermission('customer_my_files_tab','visible') )
				// {
					$menu['customerFile'] = array('label'=>'My Files', 'url'=> array('/hostDial/customerFile/index', 'customer_id'=>$customer->id)); 
				// }
				
				// if( Yii::app()->user->account->checkPermission('customer_reports_tab','visible') )
				// {
					$menu['report'] = array('label'=>'Reports', 'url'=> array('/hostDial/reports/index', 'customer_id'=>$customer->id)); 
				// }
				
				// if( Yii::app()->user->account->checkPermission('customer_skills_tab','visible') )
				// {
					// $menu['customerSkill'] = array('label'=>'Skills', 'url'=> array('/hostDial/customerSkill/index', 'customer_id'=>$customer->id));  
				// }
				
				// if( Yii::app()->user->account->checkPermission('customer_setup_tab','visible') )
				// {
					$menu['customer'] = array('label'=>'Setup', 'url'=> array('/hostDial/data/update', 'id'=>$customer->id)); 
				// }
				
				// if( Yii::app()->user->account->checkPermission('customer_leads_tab','visible') )
				// {
					$menu['lead'] = array('label'=>'Leads', 'url'=> array('/hostDial/leads/index', 'customer_id'=>$customer->id)); 
				// }
				
				// if( Yii::app()->user->account->checkPermission('customer_history_tab','visible') )  
				// {
					$menu['history'] = array('label'=>'History', 'url'=> array('/hostDial/history/index', 'customer_id'=>$customer->id)); 
				// }
				
				$menu['calendar'] = array('label'=>'Users', 'url'=> array('/hostDial/calendar/index', 'customer_id'=>$customer->id)); 
				
			
				
				// $menu = array(
					// 'insight' => array('label'=>'Dashboard', 'url'=> array('/customer/insight/index','customer_id'=>$customer->id)),
					// 'calendar_page' => array('label'=>'Calendar', 'url'=> array('/calendar/index','customer_id'=>$customer->id)),
					// 'lead' => array('label'=>'Leads', 'url'=> array('/customer/leads/index','customer_id'=>$customer->id)),
					// 'report' => array('label'=>'Reports', 'url'=> array('/customer/reports/index','customer_id'=>$customer->id)),
					// 'billing' => array('label'=>'Billing', 'url'=> array('/customer/billing/index', 'customer_id'=>$customer->id)),
					// 'customerSkill' => array('label'=> 'Skills', 'url'=>array('/customer/customerSkill/index','customer_id' => $customer->id)),
					// 'customer' => array('label'=> 'Setup', 'url'=>array('/customer/data/view','id' => $customer->id)),
					// 'calendar' => array('label'=>'Offices', 'url'=> array('/customer/calendar/index','customer_id'=>$customer->id)),
					// 'customerFile' => array('label'=>'My Files', 'url'=> array('/customer/customerFile/index', 'customer_id'=>$customer->id)),
					// 'history' => array('label'=>'History', 'url'=> array('/customer/history/index', 'customer_id'=>$customer->id)),
				// );
				
				// if( isset($customer->company) && $customer->company->display_tab_on_customer == 1 )
				// {
					// $menu['company'] = array('label'=>'Company', 'url'=> array('/customer/company/index', 'customer_id'=>$customer->id)); 
				// }
				
			}
			
			
			if( $menu )
			{
				foreach($menu as $moduleController => $items)
				{
					if($moduleController == $this->active )
					{
						$menu[$moduleController]['active'] = 1;
					}
				}
			}
			
			Yii::app()->controller->menu = $menu;
		}
	}
?>