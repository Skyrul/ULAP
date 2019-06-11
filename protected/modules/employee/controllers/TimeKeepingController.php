<?php

class TimeKeepingController extends Controller
{
	
	public $layout='//layouts/employee/main_employee';
	
	public function actionIndex()
	{
		$this->render('index');
	}
	
	public function actionList()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('is_deleted',0, true);
		$criteria->compare('account_id',Yii::app()->user->account->id, true);
		// if(!empty($_GET['AccountPtoForm']['category_id']))
		// {
			// $criteria->compare('category_id',$_GET['AccountPtoForm']['category_id'], true);
		// }
		
		$model = new CActiveDataProvider('AccountPtoForm',array(			
			'pagination'=>array(				
				'pageSize'=>40,			
			),		
		));
		
		$model->criteria->mergeWith($criteria);
		
		$this->renderPartial('_list',array(
			'model'=>$model,
		));
	}
	
	public function actionCreate()
	{
		$model=new AccountPtoForm;
		$model->account_id = Yii::app()->user->id;
		$model->is_full_shift = 1;
		$model->is_make_time_up = 2;
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['AccountPtoForm']))
		{
			$model->attributes=$_POST['AccountPtoForm'];
			
			if(isset($_POST['AccountPtoForm']['date_of_request_start']) && ($_POST['AccountPtoForm']['date_of_request_start'] != '' && $_POST['AccountPtoForm']['date_of_request_start'] != '0000-00-00'))
			{
				$model->date_of_request_start = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_request_start']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_request_end']) && ($_POST['AccountPtoForm']['date_of_request_end'] != '' && $_POST['AccountPtoForm']['date_of_request_end'] != '0000-00-00'))
			{
				$model->date_of_request_end = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_request_end']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_make_time_up_start']) && ($_POST['AccountPtoForm']['date_of_make_time_up_start'] != '' && $_POST['AccountPtoForm']['date_of_make_time_up_start'] != '0000-00-00'))
			{
				$model->date_of_make_time_up_start = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_make_time_up_start']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_make_time_up_end']) && ($_POST['AccountPtoForm']['date_of_make_time_up_end'] != '' && $_POST['AccountPtoForm']['date_of_make_time_up_end'] != '0000-00-00'))
			{
				$model->date_of_make_time_up_end = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_make_time_up_end']));
			}
			
			$model->computed_off_hour = $this->computedOffHours($model);
			
			if($model->save())
			{
				Yii::app()->user->setFlash('success', 'Schedule Change Request has been created successfully!');
				$this->redirect(array('update','id'=>$model->id));
			}
		}

		$this->render('create',array(
			'model'=>$model,
		));
	}
	
	public function computedOffHours($model)
	{
		$startTime = $model->off_hour_from.':'.$model->off_min_from.' '.$model->off_md_from;
		$endTime = $model->off_hour_to.':'.$model->off_min_to.' '.$model->off_md_to;

		$totalScheduledHours = 0;
		
		$startDate = strtotime($model->date_of_request_start);
		$endDate = strtotime($model->date_of_request_end);
		
		while( $startDate <= $endDate ) 
		{
			$startDateRequest = date("Y-m-d", $startDate);
			
			$schedules = AccountLoginSchedule::model()->findAll(array(
				'condition' => 'account_id = :account_id AND day_name = :day_name AND type=1',
				'params' => array(
					':account_id' => $model->account_id,
					':day_name' => date('l', $startDate),
				),
			));
			
			
			
			if( $schedules )
			{
				foreach( $schedules as $schedule )
				{
					$scheduleStart = strtotime($startDateRequest.' '.$schedule->start_time);
					$scheduleEnd = strtotime($startDateRequest.' '.$schedule->end_time);
					
					
					##increment schedule per 30 mins to get the computed off hours
					while($scheduleStart < $scheduleEnd)
					{
						$user_ts = $scheduleStart;
						
						if($model->is_full_shift == 1)
						{
							$start_ts = strtotime($startDateRequest.' '.$schedule->start_time);
							$end_ts = strtotime($startDateRequest.' '.$schedule->end_time);
							
						}
						else
						{
							$start_ts = strtotime($startDateRequest.' '.$startTime);
							$end_ts = strtotime($startDateRequest.' '.$endTime);
						}
						
						
						if(($user_ts >= $start_ts) && ($user_ts <= $end_ts))
						{
							$totalScheduledHours += 0.5;
						}
						
						$scheduleStart = strtotime('+30 minutes', $scheduleStart);
					}
				}
			}
			
			$startDate = strtotime('+1 day', $startDate);
		}
		
		// echo $totalScheduledHours;
		// exit;
		return $totalScheduledHours;
	}
	
	public function actionUpdate($id)
	{
		$model=$this->loadModel($id);
		
		$model->date_of_request_start = date("m/d/Y",strtotime($model->date_of_request_start));
		$model->date_of_request_end = date("m/d/Y",strtotime($model->date_of_request_end));
		
		$model->date_of_make_time_up_start = date("m/d/Y",strtotime($model->date_of_make_time_up_start));
		$model->date_of_make_time_up_end = date("m/d/Y",strtotime($model->date_of_make_time_up_end));
		
		// Uncomment the following line if AJAX validation is needed
		// $this->performAjaxValidation($model);

		if(isset($_POST['AccountPtoForm']))
		{
			$model->attributes=$_POST['AccountPtoForm'];
			
			
			if(isset($_POST['AccountPtoForm']['date_of_request_start']) && ($_POST['AccountPtoForm']['date_of_request_start'] != '' && $_POST['AccountPtoForm']['date_of_request_start'] != '0000-00-00'))
			{
				$model->date_of_request_start = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_request_start']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_request_end']) && ($_POST['AccountPtoForm']['date_of_request_end'] != '' && $_POST['AccountPtoForm']['date_of_request_end'] != '0000-00-00'))
			{
				$model->date_of_request_end = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_request_end']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_make_time_up_start']) && ($_POST['AccountPtoForm']['date_of_make_time_up_start'] != '' && $_POST['AccountPtoForm']['date_of_make_time_up_start'] != '0000-00-00'))
			{
				$model->date_of_make_time_up_start = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_make_time_up_start']));
			}
			
			if(isset($_POST['AccountPtoForm']['date_of_make_time_up_end']) && ($_POST['AccountPtoForm']['date_of_make_time_up_end'] != '' && $_POST['AccountPtoForm']['date_of_make_time_up_end'] != '0000-00-00'))
			{
				$model->date_of_make_time_up_end = date("Y-m-d",strtotime($_POST['AccountPtoForm']['date_of_make_time_up_end']));
			}
			
			
			$dateStart = $model->date_of_request_start.' '.$model->off_hour_from.':'.$model->off_min_from.' '.$model->off_md_from;
			$dateEnd = $model->date_of_request_start.' '.$model->off_hour_to.':'.$model->off_min_to.' '.$model->off_md_to;
			
			
			if(strtotime($dateStart) > strtotime($dateEnd))
				$totalScheduledHours = 24 - (round( (strtotime($dateStart) - strtotime($dateEnd) )/3600, 1));
			else
				$totalScheduledHours = round( (strtotime($dateEnd) - strtotime($dateStart) )/3600, 1);
			
			$model->computed_off_hour = $totalScheduledHours;
			
			if($model->save())
			{	
				Yii::app()->user->setFlash('success', 'Schedule Change Request has been updated successfully!');
				$this->redirect(array('index','id'=>$model->id));
			}
		}

		$this->render('update',array(
			'model'=>$model,
		));
	}
	
	
	public function actionTest()
	{
			$this->email('markjuan169@gmail.com');
			// $this->email('jim.campbell@engagex.com');
	}
	
	public function email($emailAddress)
	{
		$employeeName = 'Employee Name';
		
		$yiiName = Yii::app()->name;
				$emailSubject= $employeeName." - Time off Request";
				
				$emailContent = '';
				$emailContent .= '<h1>Time off request</h1>';
				
				$emailContent .= '<strong>Name: '.$employeeName.'</strong><br><br>';
				
				$emailContent .= '<strong>Date Request</strong><br>';
				$emailContent .= 'Yes <br><br>';
				
				$emailContent .= '<strong>Date Request</strong><br>';
				$emailContent .= 'Yes <br><br>';
				
				$emailContent .= '<strong>Requesting Time Off for Full Shift?</strong><br>';
				$emailContent .= 'Yes <br><br>';
				
				$emailContent .= '<strong>If no, How many hours?</strong><br>';
				$emailContent .= 'Yes <br><br>';
				
				$emailContent .= '<strong>Reason for Request</strong><br>';
				$emailContent .= 'Yes <br><br>';
				
				$emailContent .= '<strong>Are you requesting to use PTO?</strong><br>';
				$emailContent .= 'Yes <br><br>';
				
				$emailContent .= '<br><br><br>';
				
				$emailContent .= '<a style="background-color:#87b87f; border-color:#87b87f; color:#FFF; font-size:18px;padding: 10px 15px;" href="http://system.engagexapp.com/ulap/index.php/hr/accountUser/timeKeeping/id/1">Approve</a>';
				$emailContent .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
				
				$emailContent .= '<a style="background-color:#d15b47; border-color:#d15b47; color:#FFF; font-size:18px;padding: 10px 15px;" href="http://system.engagexapp.com/ulap/index.php/hr/accountUser/timeKeeping/id/1">Deny</a>';
				
				$emailTemplate = '<table width="80%" align="center">
					<tr>
						<td>
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" bgcolor="#0068B1" height="10px;"></td>
								</tr>
								<tr>
									<td align="center" bgcolor="#FCB245">&nbsp;</td>
								</tr>
							</table>
							
							<br />
							
							'.$emailContent.'
							
							<br /><br/>
							
							<table width="100%" border="0" cellpadding="0" cellspacing="0">
								<tr>
									<td align="center" bgcolor="#FCB245">&nbsp;</td>
								</tr>
								<tr>
									<td align="left" bgcolor="#0068B1" height="10px;" style="font-size:18px; padding:5px;">&copy; Engagex, 2015</td>
								</tr>
							</table>
						</td>
					</tr>
				</table>';
				
				
				$name='=?UTF-8?B?'.base64_encode($yiiName).'?=';
				$subject='=?UTF-8?B?'.base64_encode($emailSubject).'?=';
				
	
				
			// return	mail($account->email_address,$subject,$emailTemplate,$headers);
			
			//Send Invoice Email
			Yii::import('application.extensions.phpmailer.JPhpMailer');
	
			$mail = new JPhpMailer;
			// $mail->SMTPDebug = true;
			// $mail->Host = "mail.engagex.com";
			// $mail->Port = 25;
		
			$mail->SMTPAuth = true;		
			$mail->SMTPSecure = 'tls';   		
			$mail->SMTPDebug = 2; 
			$mail->Port = 25;      
			$mail->Host = 'mail.engagex.com';	
			$mail->Username = 'service@engagex.com';  
			$mail->Password = "_T*8c>ja";            											
	
			$mail->SetFrom('service@engagex.com');
			
			$mail->Subject = $emailSubject;
			
			$mail->AddAddress($emailAddress);
				
			$mail->MsgHTML( $emailTemplate);
									
			return $mail->Send();
	}
	
	public function loadModel($id)
	{
		$model=AccountPtoForm::model()->findByPk($id);
		if($model===null)
			throw new CHttpException(404,'The requested page does not exist.');
		return $model;
	}
	
	public function actionDelete($id)
	{
		$model = $this->loadModel($id);
		$model->is_deleted = 1;
		$model->save(false);
		
		// if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
		if(!isset($_GET['ajax']))
		{
			Yii::app()->user->setFlash('success', "Schedule Change Request has been deleted!");
			$this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('index'));
		}
	}
}