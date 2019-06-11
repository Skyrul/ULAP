<div class="profile-activity clearfix">
	<div>
		<?php 
			$date = new DateTime($data->date_created, new DateTimeZone('America/New_York'));

			$date->setTimezone(new DateTimeZone('America/Denver'));

			echo $date->format('m/d/Y h:i A') . ' | ';
			
			echo empty($data->ip_address) ? 'xxx.xxx.xxx.xxx' : $data->ip_address;
			echo ' | ';
			
			// if( !Yii::app()->user->account->getIsAdmin() ) 
			// {
				// echo $data->account->customer->firstname.' '.$data->account->customer->lastname . ' | ';
			// }
			// else
			// {
				echo isset($data->account->accountUser) ? $data->account->accountUser->getFullName() . ' | ' : $data->account->username . ' | ';
			// }
			
			echo $data->page_name;
			
			if( $data->page_name == 'Customer File' )
			{
				$customerFile = CustomerFile::model()->findByPk($data->model_id);
				
				if( $customerFile )
				{
					// echo ' | ' . CHtml::link($customerFile->fileUpload->original_filename, array('/site/download', 'file'=>$customerFile->fileUpload->original_filename), array('target'=>'_blank'));
					echo ' | ' . $customerFile->fileUpload->original_filename;
				}
			}
			
			
			if( $data->page_name == 'Company File' )
			{
				$companyFile = CompanyFile::model()->findByPk($data->model_id);
				
				if( $companyFile )
				{
					// echo ' | ' . CHtml::link($customerFile->fileUpload->original_filename, array('/site/download', 'file'=>$customerFile->fileUpload->original_filename), array('target'=>'_blank'));
					echo ' | ' . $companyFile->fileUpload->original_filename;
				}
			}
			
			if( $data->content != '' )
			{
				echo ' - ' . $data->content;
			}
			
			if( $data->type == $data::TYPE_ADDED )
			{
				echo ' - Added';
			}
			
			if( $data->type == $data::TYPE_UPDATED )
			{
				echo ' - Updated';
			}
			
			if( $data->type == $data::TYPE_DELETED )
			{
				echo ' - Deleted';
			}
			
			if( $data->type == $data::TYPE_DOWNLOADED )
			{
				echo ' - Downloaded';
			}
		?>
		
		<?php 
			// $fileAttachments = CompanyHistoryFile::model()->findAll(array(
				// 'condition' => 'company_history_id = :company_history_id',
				// 'params' => array(
					// ':company_history_id' => $data->id,
				// ),
			// ));
			
			$fileAttachments = null;
			if( $fileAttachments )
			{
				$fileAttachmentHtml = '';
				
				echo ' | ';
					
				foreach( $fileAttachments as $fileAttachment )
				{
					$fileAttachmentHtml .= CHtml::link($fileAttachment->fileUpload->original_filename, array('/site/download', 'file'=>$fileAttachment->fileUpload->original_filename), array('target'=>'_blank')) . ', ';
				}
				
				echo rtrim($fileAttachmentHtml, ', ');
			}
		?>
	</div>
</div>