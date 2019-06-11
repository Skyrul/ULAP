<?php
	class EmployeeSideMenu extends CWidget {
		public $active = '';

		public function run() {
			
			$menu = array();
			
			$menu['timeKeeping'] = array('label'=>'Time Keeping', 'url'=>array('timeKeeping/index') );
			
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