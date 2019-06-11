<?php 

class TrainingLibraryController extends Controller
{
	public function actionIndex()
	{
		if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$this->redirect(array('customer/data/index'));
		}
		
		$authAccount = Yii::app()->user->account;
		
		$categories = TrainingLibraryCategory::model()->findAll(array(
			'condition' => 'status=1',
			'order' => 'sort_order ASC',
		));
		
		$this->render('index', array(
			'categories' => $categories,
		));
	}
	
	public function actionView()
	{
		$html = '';
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => $html,
		);

		$model = TrainingLibraryFile::model()->findByPk($_POST['id']);
		
		if( !Yii::app()->user->isGuest )
		{
			$authAccount = Yii::app()->user->account;

			$usage = new TrainingLibraryFileUsage;
			
			$usage->setAttributes(array(
				'account_id' => $authAccount->id,
				'security_group' => $authAccount->account_type_id,
				'training_library_file_id' => $model->id,
			));
			
			$usage->save(false);
		}

		$result['status'] = 'success';
			
		$html = $this->renderPartial('view', array(
			'model' => $model,
		), true);
		
		$result['html'] = $html;

		echo json_encode($result);
	}
}

?>