<?php 

class PermissionController extends Controller
{
	public $layout='//layouts/column2';

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
	
	public function actionIndex()
	{
		$this->render('index', array(
		));
		
	}
	
	public function actionUpdate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$id = $_REQUEST['id'];
		
		$authAccount = Yii::app()->user->account;
		
		if( isset($_POST['ajax']) && isset($_POST['account_type_id']) && isset($_POST['permission_key']) && isset($_POST['permission_type']) && isset($_POST['value']) )
		{
			$permissionConfig = AccountPermission::model()->find(array(
				'condition' => '
					account_type_id = :account_type_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'account_type_id' => $_POST['account_type_id'],
					':permission_key' => $_POST['permission_key'],
					':permission_type' => $_POST['permission_type'],
				),
			));
			
			if( $_POST['value'] == 1 )
			{
				if( empty($permissionConfig) )
				{
					$permissionConfig = new AccountPermission;
					
					$permissionConfig->account_type_id = $_POST['account_type_id'];
					$permissionConfig->permission_key = $_POST['permission_key'];
					$permissionConfig->permission_type = $_POST['permission_type'];

					if( $permissionConfig->save(false) )
					{
						$result['status'] = 'success';
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
		
		$securityGroups = Account::listAccountType();
		
		$securityGroupPermissionSwitch = AccountPermission::model()->find(array(
			'condition' => 'permission_key = :permission_key AND permission_type = "master_switch"',
			'params' => array(
				':permission_key' => 'security_group_'.strtolower($securityGroups[$id]).'_master',
			),
		));
		
		$this->render('update', array(
			'id' => $id,
			'authAccount' => $authAccount,
			'securityGroups' => $securityGroups,
			'securityGroupPermissionSwitch' => $securityGroupPermissionSwitch,
		));
	}
	
}

?>