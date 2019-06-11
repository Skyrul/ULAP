<?php 

class PermissionController extends Controller
{
	
	public function actionIndex($company_id = null, $filter='')
	{
		if(Yii::app()->user->account->getIsCompany())
			$company_id = Yii::app()->user->account->company->id;
		
		$notes = array();
		
		$company = Company::model()->findByPk($company_id);
	
		
		if(isset($_GET['forward']))
		{
			$this->renderPartial('index', array(
				'notes' => $notes,
				'company' => $company,
				// 'dataProvider' => $dataProvider,
			));
		}
		else
		{
			$this->render('index', array(
				'notes' => $notes,
				'company' => $company,
				// 'dataProvider' => $dataProvider,
			));
		}
	}
	
	public function actionUpdate()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
		);
		
		$id = $_REQUEST['id'];
		
		$authAccount = Yii::app()->user->account;
		
		if( isset($_POST['ajax']) && isset($_POST['company_id']) && isset($_POST['permission_key']) && isset($_POST['permission_type']) && isset($_POST['value']) )
		{
			$permissionConfig = CompanyPermission::model()->find(array(
				'condition' => '
					company_id = :company_id
					AND permission_key = :permission_key
					AND permission_type = :permission_type
				',
				'params' => array(
					'company_id' => $_POST['company_id'],
					':permission_key' => $_POST['permission_key'],
					':permission_type' => $_POST['permission_type'],
				),
			));
			
			if( $_POST['value'] == 1 )
			{
				if( empty($permissionConfig) )
				{
					$permissionConfig = new CompanyPermission;
					
					$permissionConfig->company_id = $_POST['company_id'];
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
		
		// $securityGroups = Account::listAccountType();
		
		// $securityGroupPermissionSwitch = CompanyPermission::model()->find(array(
			// 'condition' => 'permission_key = :permission_key AND permission_type = "master_switch"',
			// 'params' => array(
				// ':permission_key' => 'security_group_'.strtolower($securityGroups[$id]).'_master',
			// ),
		// ));
		
		$this->render('update', array(
			'id' => $id,
			'authAccount' => $authAccount,
			// 'securityGroups' => $securityGroups,
			// 'securityGroupPermissionSwitch' => $securityGroupPermissionSwitch,
		));
	}
	
}

?>