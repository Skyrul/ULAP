<?php 

ini_set('memory_limit', '4000M');
set_time_limit(0);
	
class WebphoneVoiceController extends Controller
{
	
	public function accessRules()
	{
		return array(
			array('allow', 
				'users'=>array('*'),
			),
		);
	}
	
	public function actionIndex()
	{		
		/* 
		Notes:
			-before saving the file:
				-you should check the $_FILES['file']['error'] to check for specific issues
				-the file size should be also checked and rejected if too big ($_FILES['file']['size'])
				-you should not just blindly accept the file name sent by the client. normalize/sanitize the destination file name first ($_FILES["file"]["name"]) or generate a file name yourself		
			-you might check also the X- headers for call-id, caller, called or others (altrough these can be also part of the file name)
				foreach (getallheaders() as $name => $value)
				{
					if($name == 'X-callid')		
					{
						error_log("SIP CALL-ID:".$value\n", 0);
						break;
					}
				} 
			-more details: http://php.net/manual/en/features.file-upload.php
		*/	
					
		$target_dir = "webphone_voice_uploads/"; //target directory. make sure that PHP has write permission to this folder!!!
		$target_file = $target_dir . basename($_FILES["file"]["name"]);	//this is the file name suggested by the webhone (normalize/sanitize it or use a file name generetad by you instead of this)
		
		if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) //the most important code line
		{
			error_log("OK: The file ". basename( $_FILES["file"]["name"]). " has been uploaded successfully.");
		} 
		else 
		{
			error_log("ERROR: there was an error uploading your file tmp:".basename( $_FILES["file"]["name"])."dst:".basename( $_FILES["file"]["name"]).' error: '.$_FILES['file']['error']);
		}				
	}
}

?>