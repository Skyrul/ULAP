<?php 

class LearningCenterController extends Controller
{
	public function actionIndex($customer_id)
	{
		if( !Yii::app()->user->isGuest && in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$authAccount = Yii::app()->user->account;
			
			if( $authAccount->getIsCustomer() )
			{
				$customer = Customer::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customer && $customer->id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customer->id));
				}
			}
			
			if( $authAccount->getIsCustomerOfficeStaff() )
			{
				$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
					'condition' => 'account_id = :account_id',
					'params' => array(
						':account_id' => $authAccount->id,
					),
				));
				
				if( $customerOfficeStaff && $customerOfficeStaff->customer_id != $customer_id )
				{
					$this->redirect(array('index', 'customer_id'=>$customerOfficeStaff->customer_id));
				}
			}
		}
		
		
		$customer = Customer::model()->findByPk($customer_id);
		$company = $customer->company;
		
		$categories = CompanyLearningCenterCategory::model()->findAll(array(
			'condition' => 'company_id = :company_id AND status=1',
			'params' => array(
				':company_id' => $company->id,
			),
			'order' => 'sort_order ASC',
		));
		
		$this->render('index', array(
			'customer' => $customer,
			'company' => $company,
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

		$model = CompanyLearningCenterFile::model()->findByPk($_POST['id']);
		
		if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF)) )
		{
			$customer = Customer::model()->findByPk($_POST['customer_id']);

			$usage = new CompanyLearningCenterFileUsage;
			
			$usage->setAttributes(array(
				'customer_id' => $customer->id,
				'company_id' => $customer->company_id,
				'company_learning_center_file_id' => $model->id,
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