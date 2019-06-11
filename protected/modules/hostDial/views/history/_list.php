<div class="profile-activity clearfix">
	<div>
		<?php 
			$date = new DateTime($data->date_created, new DateTimeZone('America/Chicago'));

			$date->setTimezone(new DateTimeZone('America/Denver'));

			echo $date->format('m/d/Y g:i A') . ' | ';
			
			if( in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_PORTAL, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_COMPANY)) )
			{
				// echo $data->account->customer->firstname.' '.$data->account->customer->lastname . ' | ';
			}
			else
			{
				if ( isset($data->account->accountUser) )
				{
					echo $data->account->accountUser->getFullName() . ' | ';
				}
				else
				{
					if( isset($data->account) )
					{
						echo $data->account->username . ' | ';
					}
					else
					{
						if( $data->page_name == 'Leads' && (strpos($data->content, 'leads in list') !== false ) )
						{
							$listsCronProcessQueue = ListsCronProcess::model()->find(array(
								'condition' => 'list_id = :list_id',
								'params' => array(
									':list_id' => $data->model_id,
								),
							));
							
							if( $listsCronProcessQueue )
							{
								if( isset($listsCronProcessQueue->account->accountUser) )
								{
									echo $listsCronProcessQueue->account->accountUser->getFullName() . ' | ';
								}
								else
								{
									echo $listsCronProcessQueue->account->username . ' | ';
								}
							}
							else
							{
								echo ' System | ';
							}
						}
						else
						{
							echo ' System | ';
						}
					}
				}
			}
			
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
			
			if( $data->content != '' )
			{
				if( $data->page_name == 'Customer Setup' && (strpos($data->content, 'Customer Notes to Agent') !== false ) )
				{
					echo ' - Customer Notes to Agent';
				}
				elseif( stristr($data->content, 'Is Received Email changed from') !== false )
				{
					$content = $data->content;
					
					$content = str_replace('Is Received Email', 'Receieves Emails', $content);
					
					$content = str_replace('0', 'No', $content);
					$content = str_replace('2', 'No', $content);
					
					$content = str_replace('1', 'Individual Calendars', $content);
					$content = str_replace('3', 'All Calendars', $content);
				
					
					if( !empty($data->page_name) )
					{
						echo ' | ';
					}
					
					echo $content;
				}
				elseif( stristr($data->content, 'Status changed from') !== false && stristr($data->content, 'Active') === false && stristr($data->content, 'Inactive') === false )
				{
					$content = $data->content;
					
					$content = str_replace('1', 'Active', $content);
					$content = str_replace('2', 'Inactive', $content);
					
					if( !empty($data->page_name) )
					{
						echo ' | ';
					}
					
					echo $content;
				}
				elseif( stristr($data->content, 'Tier changed from') !== false )
				{
					$content = $data->content;
					
					$tierID = filter_var($content, FILTER_SANITIZE_NUMBER_INT);
					
					$model = Tier::model()->findByPk($tierID);
					
					if( $model )
					{
						$tierName = $model->tier_name;
					}
					else
					{
						$tierName = $tierID;
					}
					
					$content = str_replace($tierID, $tierName, $content);
					
					if( !empty($data->page_name) )
					{
						echo ' | ';
					}
					
					echo $content;
				}
				elseif( stristr($data->content, 'Possible Now') !== false )
				{
					$content = $data->content;
					
					$content = str_replace('Possible Now', 'the automatic Farmers DNC scrub. Please check recycle count for any leads that could be recycled', $content);
					
					echo $content;
				}
				else
				{
					$content = str_replace('12/31/1969', '', $data->content);
					$content = str_replace('11/30/-0001', '', $content);
					
					if( !empty($data->page_name) )
					{
						echo ' | ';
					}
					
					if( $data->page_name == 'Credit' )
					{
						$model = CustomerCredit::model()->findByPk($data->model_id);
						
						if( $model )
						{
							echo $model->description . ' | ';
						}
					}
					
					echo $content;
				}
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
			
			if( $data->type == $data::TYPE_REMOVED )
			{
				echo ' - Removed';
			}
		?>
		
		<?php 
			$fileAttachments = CustomerHistoryFile::model()->findAll(array(
				'condition' => 'customer_history_id = :customer_history_id',
				'params' => array(
					':customer_history_id' => $data->id,
				),
			));
			
			if( $fileAttachments )
			{
				$fileAttachmentHtml = '';
				
				echo ' | ';
					
				foreach( $fileAttachments as $fileAttachment )
				{
					$fileAttachmentHtml .= CHtml::link($fileAttachment->fileUpload->original_filename, array('/site/download', 'file'=>$fileAttachment->fileUpload->original_filename, 'customerHistoryFileId' => $fileAttachment->id), array('target'=>'_blank')) . ', ';
					
					if($fileAttachment->is_enrolment_file == 1)
					{ 
						$account = $fileAttachment->customerHistory->customer->account;
						
						$criteria = new CDbCriteria;
						$criteria->compare('account_id', $account->id);
						$customerEnrolment = CustomerEnrollment::model()->find($criteria);
						
						$fileName = $account->customer->firstname.'_'.$account->customer->lastname.'_'.$customerEnrolment->id.'.pdf';
						$url = Yii::getPathOfAlias('webroot') . '/enrollmentPdf/' . $fileName;
						$fileAttachmentHtml .= CHtml::link($fileName, array('/site/download', 'file'=>$fileName, 'isEnrolmentFile'=>1, array('target'=>'_blank'))) . ', ';
					}
				}
				
				echo rtrim($fileAttachmentHtml, ', ');
				
				
			}
		?>
	</div>
</div>