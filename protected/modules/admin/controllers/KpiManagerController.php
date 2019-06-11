<?php 

class KpiManagerController extends Controller
{
	
	public function actionIndex()
	{
		$models = CustomerSuccessKpi::model()->findAll(array(
			'condition' => 'status=1',
		));
		
		$this->render('index', array(
			'models' => $models
		));
	}
	
	public function actionAddTask()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html
		);
		
		if( isset($_POST['ajax']) && isset($_POST['kpi_id']) )
		{
			$result['status'] = 'success';
			
			$model = new CustomerSuccessKpiTask;
			
			if( isset($_POST['CustomerSuccessKpiTask']) )
			{
				$model->attributes = $_POST['CustomerSuccessKpiTask'];
				
				if( $model->save() )
				{
					$result['status'] = 'success';
				}
				else
				{
					$result['status'] = 'error';
				}
				
				echo json_encode($result);
				Yii::app()->end();
			}
			
			$html = $this->renderPartial('addTask', array(
				'model' => $model
			), true);
			
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionEmailSettings()
	{
		$this->render('emailSettings');
	}
}

?>