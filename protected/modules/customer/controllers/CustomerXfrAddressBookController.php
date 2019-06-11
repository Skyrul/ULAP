<?php 

ini_set('memory_limit', '1000M');
set_time_limit(0);

class CustomerXfrAddressBookController extends Controller
{
	
	public function actionCreate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['CustomerSkillXfrAddressBook']) )
		{
			$model = new CustomerSkillXfrAddressBook;
			
			$model->attributes = $_POST['CustomerSkillXfrAddressBook'];
			
			if( $model->validate() )
			{
				if( $model->save() )
				{
					$result['status'] = 'success';
					$result['message'] = 'Xfr address book was successfully added.';
					$result['customer_skill_id'] = $model->customer_skill_id;
				}
			}
			else
			{
				$message = '';
				
				foreach( $model->getErrors() as $error )
				{
					$message .= $error[0] . "\r\n \r\n";
				}
				
				$result['message'] = $message;
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionUpdate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = CustomerSkillXfrAddressBook::model()->findByPk($_POST['id']);
			
			if( isset($_POST['CustomerSkillXfrAddressBook']) )
			{
				$model->attributes = $_POST['CustomerSkillXfrAddressBook'];
				
				if( $model->validate() )
				{
					if( $model->save() )
					{
						$result['status'] = 'success';
						$result['message'] = 'Xfr address book was successfully updated.';
						$result['customer_skill_id'] = $model->customer_skill_id;
					}
				}
				else
				{
					$message = '';
					
					foreach( $model->getErrors() as $error )
					{
						$message .= $error[0] . "\r\n \r\n";
					}
					
					$result['message'] = $message;
				}
				
				echo json_encode($result);
				Yii::app()->end();
			}
			
			if( $model )
			{
				$html = $this->renderPartial('ajaxEditXfr', array(
					'model' => $model,
				), true);
				
				$result['html'] = $html;
			}
			else
			{
				$result['message'] = 'Record not found.';
			}
		}
		
		echo json_encode($result);
	}
	
	public function actionDelete()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		if( isset($_POST['ajax']) && isset($_POST['id']) )
		{
			$model = CustomerSkillXfrAddressBook::model()->findByPk($_POST['id']);
			
			if( $model )
			{
				$model->status = 3;
				
				if( $model->save(false) )
				{
					$result['status'] = 'success';
					$result['message'] = 'Xfr address book was deleted successfully.';
					$result['customer_skill_id'] = $model->customer_skill_id;
				}
			}
		}
		
		echo json_encode($result);
	}
	
	
	public function actionUpdateXfrTable()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		$xfrAddressBooks = CustomerSkillXfrAddressBook::model()->findAll(array(
			'condition' => 'customer_skill_id = :customer_skill_id AND status=1',
			'params' => array(
				':customer_skill_id' => $_POST['customer_skill_id']
			),
		));
		
		$html = $this->renderPartial('ajaxXfrTable', array(
			'xfrAddressBooks' => $xfrAddressBooks,
		), true);
		
		$result['html'] = $html;
		
		echo json_encode($result);
	}
	
}

?>