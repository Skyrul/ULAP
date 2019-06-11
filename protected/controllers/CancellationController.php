<?php 

class CancellationController extends Controller
{

	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}

	public function actionIndex()
	{
		if( isset( $_POST['CustomerCancellation']) )
		{
			$model = CustomerCancellation::model()->find(array(
				'condition' => 'token = :token AND status=2',
				'params' => array(
					':token' => $_POST['CustomerCancellation']['token']
				),
			));
					
			if( $model )
			{
				$model->attributes = $_POST['CustomerCancellation'];
				$model->scenario = 'customerCancellation';
				
				if( $model->validate() )
				{
					$model->status = 1;
					
					if( $model->save() )
					{
						if( $model->reason == 'Other*' )
						{
							$content = $model->agent_signature.' - '.$model->other_reason;
						}
						else
						{
							$content = $model->agent_signature.' - '.$model->reason;
						}
						
						
						$history = new CustomerHistory;
						
						$history->setAttributes(array(
							'model_id' => $model->id, 
							'customer_id' => $model->customer_id,
							'page_name' => 'Cancellation',
							'content' => $content,
							'type' => $history::TYPE_UPDATED,
						));
							
						if( $history->save(false) )
						{
							$this->redirect(array('submitted'));
						}
					}
				}
				else
				{
					$modelErrors = $model->getErrors();
					
					if( $model->reason == 'Other*' && empty($model->other_reason) )
					{
						if( !in_array('other_reason', $modelErrors) )
						{
							$modelErrors['other_reason'] = 'Other is required.';
						}
					}
					
					$this->layout = 'blank';
					$this->render('index', array(
						'model' => $model,
						'modelErrors' => $modelErrors,
					));
				}
			}
		}
		
		if( !isset($_GET['token']) )
		{
			$this->pageTitle = 'Engagex - Cancellation Page Expired';
			$this->layout = 'main-no-navbar';
			$this->render('expired');
		}
		else
		{
			$model = CustomerCancellation::model()->find(array(
				'condition' => 'token = :token AND status=2',
				'params' => array(
					':token' => $_GET['token']
				),
			));
			
			if( $model )
			{
				$this->layout = 'blank';
				$this->render('index', array(
					'model' => $model,
					'modelErrors' => array()
				));
			}
			else
			{
				$this->pageTitle = 'Engagex - Cancellation Page Expired';
				$this->layout = 'main-no-navbar';
				$this->render('expired');
			}
		}
	}

	public function actionSubmitted()
	{
		$this->pageTitle = 'Engagex - Cancellation Form Submitted';
		$this->layout = 'main-no-navbar';
		$this->render('submitted');
	}
}

?>