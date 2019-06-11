<?php
    class MyActiveRecord extends CActiveRecord 
	{
		private static $externalDB = null;
		
		protected static function getExternalDbConnection()
		{	 
			if (self::$externalDB !== null)
			{
				return self::$externalDB;
			}
			else
			{
				// self::$externalDB = Yii::createComponent(array(
					// 'class' => 'CDbConnection',
					// other config properties...
					// 'connectionString' => "mysql:host=64.251.27.8;dbname=asterisk",
					// 'emulatePrepare' => true,
					// 'username'=>'cron',
					// 'password'=> 'enggex1234', 
					// 'charset'=>'utf8',
					// 'tablePrefix' => 'vicidial_',
				// ));
				
				// self::$externalDB = Yii::createComponent(array(
					// 'class' => 'CDbConnection',
					// other config properties...
					// 'connectionString' => "mysql:host=64.251.27.8;dbname=asterisk",
					// 'emulatePrepare' => true,
					// 'username'=>'cdr',
					// 'password'=> 'cdr1243!', 
					// 'charset'=>'utf8',
					// 'tablePrefix' => 'vicidial_',
				// ));
				
				self::$externalDB = Yii::createComponent(array(
					'class' => 'CDbConnection',
					// other config properties...
					'connectionString' => "mysql:host=64.251.27.8;dbname=asterisk",
					'emulatePrepare' => true,
					'username'=>'cron',
					'password'=> 'enggex1234', 
					'charset'=>'utf8',
					'tablePrefix' => 'vicidial_',
				));
				
				Yii::app()->setComponent('externalDB', self::$externalDB);
	 
				if (self::$externalDB instanceof CDbConnection)
				{   
					Yii::app()->db->setActive(false);
					Yii::app()->externalDB->setActive(true);
					
					return self::$externalDB;
				}
				else
				{
					throw new CDbException(Yii::t('yii', 'Active Record requires a "db" CDbConnection application component.'));
				}
			}
		}
	}
?>