<?php

ini_set('memory_limit', '4000M');
set_time_limit(0);

class EmailerController extends Controller
{
	public $layout='//layouts/agent_dialer';
		
	public function actionLoadTab()
	{
		$email_address = '';
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			$lead = Lead::model()->findByPk($_POST['current_lead_id']);
			
			if(!empty($lead->email_address))
			{
				$email_address = $lead->email_address;
			}
				
			$html = $this->renderPartial('ajaxLoadTab', array(
				'lead' => $lead,
				'email_address' => $email_address,
				'current_lead_id' => $_POST['current_lead_id'],
				'current_skill_id' => $_POST['current_skill_id'],
			), true,true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionEmailPreview()
	{
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		$lead = Lead::model()->findByPk($_POST['current_lead_id']);
		
		$set = SkillEmailTemplate::model()->findByPk($_POST['email_template_id']);
		
		$personal_note = $_POST['personal_note'];
		
		$attachments = SkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'skill_email_template_id = :skill_email_template_id',
			'params' => array(
				// ':skill_id' => $model->id,
				':skill_email_template_id' => $set->id,
			),
		));
		
		if( isset($_POST['ajax']) )
		{
			$html = $this->renderPartial('emailPreview', array(
				'set' => $set,
				'lead' => $lead,
				'attachments' => $attachments,
				'personal_note' => $personal_note,
			), true,true); 
			
			$result['status'] = 'success';
			$result['html'] = $html;
		}
		
		echo json_encode($result);
	}
	
	public function actionEmailAttachment()
	{
		$skill_id = $_POST['current_skill_id'];
		
		$skill = Skill::model()->findByPk($skill_id);
		
		$attachments = SkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'skill_id = :skill_id',
			'params' => array(
				':skill_id' => $skill->id,
			),
		));
		
		if(isset($_POST['attachment_id']))
		{
			if($loginSuccessful)
			{
				// print_r($model->getErrors());
				$response = array(
					'success' => false,
					'message' => 'Login error!',
					'htmlObjectId' => '',
					'scenario' => 'add',
				);
			}
			
			echo CJSON::encode($response);
			Yii::app()->end();
		}
		
		Yii::app()->clientscript->scriptMap['jquery.min.js'] = false;
		$this->renderPartial('emailAttachment',array(
			'skill' => $skill,
			'attachments' => $attachments,
			//'actionController' => Yii::app()->createUrl('/site/personnelLogin'),
		),false,true);
	}

	public function actionTestSubmit()
	{
		$leadEmail = new LeadEmail;
		
		$result = array(
			'status' => 'error',
			'message' => '',
			'html' => '',
		);
		
		if( isset($_POST['ajax']) )
		{
			if(isset($_POST['current_lead_id']))
				$leadEmail->lead_id = $_POST['current_lead_id'];
			
			if(isset($_POST['email_template_id']))
				$leadEmail->skill_email_template_id = $_POST['email_template_id'];
			
			if(isset($_POST['emailTabEmailAddress']))
				$leadEmail->email_address = $_POST['emailTabEmailAddress'];
			
			if(isset($_POST['personal_note']))
				$leadEmail->personal_note = $_POST['personal_note'];
			
			$leadEmail->is_sent = 0;
			
			$valid = true;
			if($leadEmail->save(false))
			{
				if(isset($_POST['otherAttachment']))
				{
					foreach($_POST['otherAttachment'] as $otherAttachment)
					{
						$lea = new LeadEmailAttachment;
						$lea->lead_email_id = $leadEmail->id;
						$lea->fileupload_id = $otherAttachment;
						
						if(!$lea->save(false))
							$valid = false;
					}
				}
			}
			else
			{
				$valid = false;
			}
			
			if($valid)
			{
				$this->sendEmail($leadEmail);
				$result['status'] = 'success';
			}
		}
		
		echo json_encode($result);
	}
	
	public function sendEmail($leadEmail)
	{
		$set = $leadEmail->skillEmailTemplate;
		
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
		
		if($set->is_sending_option_default)
		{
			$mail->Host = 'mail.engagex.com';	
			$mail->Username = 'service@engagex.com';  
			$mail->Password = "_T*8c>ja";     
		}
		else
		{
			$mail->Host = 'mail.engagex.com';	
			$mail->Username = 'service@engagex.com';  
			$mail->Password = "_T*8c>ja";     
		}
		     				
		
		
		$emails = !empty($leadEmail->email_address) ? explode(',', $leadEmail->email_address) : array();
			// $ccs = !empty($disposition->cc) ?  $disposition->cc : '';
			
			if( $emails )
			{
				foreach( $emails as $email )
				{
					$mail->AddBcc($email);
				}
			}
			
		$mail->SetFrom($set->from, $set->from, 0);
			
		$mail->AddReplyTo($set->from);

		$set->getReplacementCodeValues($lead, $set->subject, $personal_note);
		
		$mail->Subject = $set->getReplacementCodeValues($leadEmail->lead, $set->subject);
			
		$mail->MsgHTML($set->getReplacementCodeValues($leadEmail->lead, $set->htmlContent,$leadEmail->personal_note));
		
		$attachments = SkillEmailTemplateAttachment::model()->findAll(array(
			'condition' => 'skill_email_template_id = :skill_email_template_id',
			'params' => array(
				// ':skill_id' => $model->id,
				':skill_email_template_id' => $set->id,
			),
		));
				
		if( $attachments )
		{
			foreach( $attachments as $attachment )
			{
				$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename );
			}
		}
		
		if(!empty($leadEmail->leadEmailAttachment))
		{
			foreach( $leadEmail->leadEmailAttachment as $attachment )
			{
				$mail->AddAttachment( Yii::getPathOfAlias('webroot') . '/fileupload/' . $attachment->fileUpload->original_filename );
			}
			
		}
		
		return $mail->Send();
	}
}

