<?php
/**
 * Controller is the customized base controller class.
 * All controller classes for this application should extend from this base class.
 */
class Controller extends CController
{
	/**
	 * @var string the default layout for the controller view. Defaults to '//layouts/column1',
	 * meaning using a single column layout. See 'protected/views/layouts/column1.php'.
	 */
	public $layout='//layouts/column_sidebar';
	/**
	 * @var array context menu items. This property will be assigned to {@link CMenu::items}.
	 */
	public $menu=array();
	/**
	 * @var array the breadcrumbs of the current page. The value of this property will
	 * be assigned to {@link CBreadcrumbs::links}. Please refer to {@link CBreadcrumbs::links}
	 * for more details on how to specify this property.
	 */
	public $breadcrumbs=array();
	
	public function filters()
	{
		return array(
			'accessControl', // perform access control for CRUD operations
			// 'postOnly + delete', // we only allow deletion via POST request
		);
	}
	
	public function accessRules(){

        $accessRules = array(
			array(
				'allow',
				'actions' => UserAccess::rules( isset($this->module) ? $this->module->name : 'site', str_replace("Controller", "", get_class($this))),
				'users' => array('@')
			),
			array(
				'deny',  // deny all users
				'users'=>array('*'),
			),
        );

        return $accessRules;
    }
	
	public function beforeAction($action) 
	{
		if( strtolower($action->id) != 'logout' && !isset($_GET['passwordExpired']) && !Yii::app()->user->isGuest && !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_AGENT, Account::TYPE_COMPANY)) )
		{
			$account = Yii::app()->user->account;
			
			if( $account->date_last_password_change == null || ($account->date_last_password_change != null && strtotime($account->date_last_password_change) < strtotime('-90 days')) ) 
			{		
				if( $_SERVER['HTTP_HOST'] == 'portal.engagexapp.com' )
				{
					$url = 'https://' . Yii::app()->getRequest()->serverName.'/index.php/changePassword?passwordExpired=1';
					Yii::app()->request->redirect($url);
					return true;
				}
				else
				{
					$url = 'http://' . Yii::app()->getRequest()->serverName.'/ulap/index.php/changePassword?passwordExpired=1';
					Yii::app()->request->redirect($url);
					return true;
				}
			}
		}

		//used to force https on portal.engagexapp.com
		if( $_SERVER['HTTP_HOST'] == 'portal.engagexapp.com' )
		{
			if( ! Yii::app()->getRequest()->isSecureConnection ) 
			{
				$url = 'https://' . Yii::app()->getRequest()->serverName . Yii::app()->getRequest()->requestUri;
				Yii::app()->request->redirect($url);
				
				return true;
			}
		}
		
		return true;
	}
}