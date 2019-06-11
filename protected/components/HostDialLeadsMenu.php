<?php
	class HostDialLeadsMenu extends CWidget {
		public $active = '';
		public $customer;
		
		public function run() {
			
			$customer = $this->customer;
			
			$this->render('hostDialLeadsMenu',array(
				'customer_id'=>$customer->id,
			));
		}
	}
?>