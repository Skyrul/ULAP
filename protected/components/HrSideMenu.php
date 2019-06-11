<?php
	class HrSideMenu extends CWidget {
		public $active = '';

		public function run() {
			$menu = array(
				'user' => array('label'=>'Users', 'url'=> array('accountUser/index')),
			);
			
			foreach($menu as $moduleController => $items)
			{
				if($moduleController == $this->active)
				{
					$menu[$moduleController]['active'] = true;
				}
			}
			
			Yii::app()->controller->menu = $menu;
		}
	}
?>