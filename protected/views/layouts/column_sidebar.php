<?php /* @var $this Controller */ ?>
<?php $this->beginContent('//layouts/main'); ?>

<div class="row">
	<div class="col-sm-12">
		<div class="">
			<?php  ## query list notification 
				// $criteria = new CDbCriteria;
				// $criteria->compare('account_id', Yii::app()->user->account->id);
				// $criteria->compare('is_notified', 0);
				// $criteria->compare('on_going', 0);
				// $criteria->addCondition('date_completed != "0000-00-00" && date_completed IS NOT NULL');
				// $listCronProcess = ListsCronProcess::model()->find($criteria);
				
				$listCronProcess = ListsCronProcess::model()->find(array(
					'condition' => '
						account_id = :account_id 
						AND is_notified=0 
						AND on_going=0 
						AND date_completed != "0000-00-00" 
						AND date_completed IS NOT NULL
					',
					'params' => array(
						':account_id' => Yii::app()->user->account->id
					),
				));
			?>
			
			<?php 
				if($listCronProcess)
				{
					$result = json_decode($listCronProcess->result_data); 
					$listCronProcess->is_notified = 1; 
					$listCronProcess->save(false); 
					?>
				
					<div class="alert alert-<?php echo $result->status; ?>">
						<button data-dismiss="alert" class="close" type="button">
							<i class="ace-icon fa fa-times"></i>
						</button><?php echo $result->message; ?>
					</div>
			
				<?php 
				} 
			?>
			
			<?php if(isset($_REQUEST['customer_id'])){
				
				$selfCustomer = Customer::model()->findByPk($_REQUEST['customer_id']);
				
				if($selfCustomer !== null)
				{
					$borderStyle = '';
					
					if( !empty($selfCustomer->gender) )
					{
						$borderStyle = strtolower($selfCustomer->gender) == 'male' ? 'border:3px solid #337ab7;' : 'border:3px solid #c6699f;';
					}

					if($selfCustomer->getImage())
					{
						echo CHtml::image($selfCustomer->getImage(), '', array('style'=>'height:70px;float:left;margin-right:5px;'.$borderStyle));
					}
					
					$companyName = isset($selfCustomer->company) ? $selfCustomer->company->company_name : '(No Company)';
					
					if(isset($selfCustomer->company) && $selfCustomer->company->is_host_dialer == 1)
					{
						echo '<h3 style="margin-top:0px;position:relative;top:20px;">';
						
							echo $selfCustomer->getFullName();
							
							if( !in_array(Yii::app()->user->account->account_type_id, array(Account::TYPE_COMPANY, Account::TYPE_CUSTOMER, Account::TYPE_CUSTOMER_OFFICE_STAFF, Account::TYPE_AGENT, Account::TYPE_COMPANY, Account::TYPE_GRATON_AGENT, Account::TYPE_HOSTDIAL_AGENT, Account::TYPE_GAMING_PROJECT_MANAGER)) )
							{
								echo '&nbsp;&nbsp;&nbsp;';
								
								if( Yii::app()->controller->module->id == 'hostDial' )
								{
									echo CHtml::link('<i class="fa fa-desktop"></i> Use Standard Interface',array('/customer/insight/index','customer_id'=> $selfCustomer->id ),array('class'=>'btn btn-minier btn-primary')); 
								}
								else
								{
									echo CHtml::link('<i class="fa fa-desktop"></i> Use Host Manager Interface',array('/hostDial/insight/index','customer_id'=> $selfCustomer->id ),array('class'=>'btn btn-minier btn-primary')); 
								}
							}
						
						echo '</h3>';
					}
					else
					{
						echo '<h3 style="margin-top:0px;position:relative;top:20px;">'.$selfCustomer->getFullName().', '.$companyName.'</h3>';
					}
				}
			}
			?>
			
			<?php 
				if(!Yii::app()->user->isGuest)
				{
					if(Yii::app()->user->account->getIsCompany())
					{
						$companyName = Yii::app()->user->account->company->company_name;
						echo '<h3 style="margin-top:0px;position:relative;top:20px;">'.$companyName.'</h3>';
					}
				}
			?>
			<div style="clear:both;"></div>
		</div>
		
		<br/>
		
		<div class="tabbable tabs-left">
				<?php
					$this->widget('SideMenu', array(
						'items'=>$this->menu,
						'htmlOptions'=>array('class'=>'nav nav-tabs'),
					));
				?>
			<div class="tab-content">
				<div class="tab-pane in active">
					<?php echo $content; ?>
				</div>
			</div>
		</div>
	</div><!-- /.col -->
</div>
<?php $this->endContent(); ?>