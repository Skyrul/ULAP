<?php 

class PermissionController extends Controller
{
	// public $layout='//layouts/column2';

	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
		);
	}

	public function accessRules()
	{
		return array(
			array('allow',  // allow all users to perform 'index' and 'view' actions
				'actions'=>array('index', 'update'),
				'users'=>array('*'),
			),
			array('deny',  // deny all users
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex($customer_id)
	{
		$customer = Customer::model()->findByPk($customer_id);
		
		if($customer === null)
			throw CHttpException("403", "Customer not found.");
		
		$officeStaffs = CustomerOfficeStaff::model()->findAll(array(
			'condition' => 'customer_id = :customer_id AND is_deleted=0',
			'params' => array(
				':customer_id' => $customer->id,
			),
		));
							
		$this->render('index', array(
			'customer' => $customer,
			'officeStaffs' => $officeStaffs,
		));
		
	}
	
	public function actionUpdate($customer_id = null, $account_id = null)
	{
		if(!empty($_POST['customer_id']))
			$customer_id = $_POST['customer_id'];
		
		if(!empty($_POST['account_id']))
			$account_id = $_POST['account_id'];
		
		$customer = Customer::model()->findByPk($customer_id);
		
		if($customer === null)
			throw new CHttpException("403", "Customer not found.");
		
		$customerOfficeStaff = CustomerOfficeStaff::model()->find(array(
			'condition' => 'account_id = :account_id',
			'params' => array(
				':account_id' => $account_id,
			),
		));
				
		if($customerOfficeStaff === null)
			throw new CHttpException("403", "Office Staff not found.");
		
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		// $id = $_REQUEST['id'];
		
		$authAccount = Yii::app()->user->account;
		
		if( isset($_POST['ajax']) && isset($_POST['account_id']) && isset($_POST['permission_key']) && isset($_POST['permission_type']) && isset($_POST['value']) )
		{
			$permissionConfig = CustomerAccountPermission::model()->find(array(
				'condition' => '
					account_id = :account_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					':account_id' => $_POST['account_id'],
					':permission_key' => $_POST['permission_key'],
					':permission_type' => $_POST['permission_type'],
				),
			));
			
			
			if( $_POST['value'] == 1 )
			{
				
				if( empty($permissionConfig) )
				{
					$permissionConfig = new CustomerAccountPermission;
					
					$permissionConfig->account_id = $_POST['account_id'];
					$permissionConfig->permission_key = $_POST['permission_key'];
					$permissionConfig->permission_type = $_POST['permission_type'];

					if( $permissionConfig->save(false) )
					{
						$result['status'] = 'success';
					}
					else
					{
						print_r($permissionConfig->getErrors());
					}
				}
			}
			else
			{
				if( $permissionConfig )
				{
					if( $permissionConfig->delete() )
					{
						$result['status'] = 'success';
					}
				}
			}

			echo json_encode($result);
			Yii::app()->end();
		}
		
		// $securityGroups = Account::listAccountType();
		
		// $securityGroupPermissionSwitch = AccountPermission::model()->find(array(
			// 'condition' => 'permission_key = :permission_key AND permission_type = "master_switch"',
			// 'params' => array(
				// ':permission_key' => 'security_group_'.strtolower($securityGroups[$id]).'_master',
			// ),
		// ));
		
		$this->render('update', array(
			// 'id' => $id,
			'customer' => $customer,
			'authAccount' => $authAccount,
			'customerOfficeStaff' => $customerOfficeStaff,
			'securityGroups' => $securityGroups,
			// 'securityGroupPermissionSwitch' => $securityGroupPermissionSwitch,
		));
	}
	
}

?>