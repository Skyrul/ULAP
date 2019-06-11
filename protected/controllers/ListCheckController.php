<?php 

ini_set('memory_limit', '10000M');
set_time_limit(0);

class ListCheckController extends Controller
{
	
	public function actionIndex()
	{
		$this->render('index');
	}
	
}

?>