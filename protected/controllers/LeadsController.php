<?php 

class LeadsController extends Controller
{
	// public $layout='//layouts/column2';
	
	public function actionIndex($id=null)
	{
		if($id == null)
		{
			$model = Lists::model()->find();
			
			if(!$model)
			{
				$model = new Lists; 
			}
		}
		else
		{
			$model = Lists::model()->findByPk($id);
		}
		
		$lists = Lists::model()->findAll();
		
		$leads = Lead::model()->findAll(array(
			'condition' => 'list_id = :list_id',
			'params' => array(
				':list_id' => $model->id
			),
		));
		
		$this->render('index', array(
			'lists' => $lists,
			'leads' => $leads,
			'model' => $model,
		));
	}
	
	public function actionView()
	{
		$html = '';
		
		$result = array(
			'status' => '',
			'message' => '',
			'html' => $html,
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = Lists::model()->findByPk($_POST['id']);
			
			$html = $this->renderPartial('ajax_view', array(
				'model' => $model,
			), true);
			
			$result['status'] = 'success';
			$result['html'] = $html;
			
			echo json_encode($result);
			Yii::app()->end();
		}
	}


	public function actionCreate()
	{
		
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => 'Database error.',
			'html' => $html,
		);
		
		$model = new Lead;
		
		if( isset($_POST['Lead']) )
		{	
			$model->attributes = $_POST['Lead'];
	
			if($model->save(false))
			{
				$html = $this->renderPartial('ajax_lead_row', array(
					'model' => $model,
				), true);
				
				$result['status'] = 'success';
				$result['html'] = $html;
			}
		}
		
		echo json_encode($result);
		Yii::app()->end();
	}
}

?>