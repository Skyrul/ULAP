<div class="widget-header">
	<h4 class="widget-title lighter smaller">
		<?php 
			if( $customer->company->is_host_dialer == 1)
			{
				echo 'PROPERTY INFORMATION - ' . $customer->company->company_name;
			}
			else
			{
				echo 'CUSTOMER INFORMATION - ' . $customer->company->company_name;
			}
		?>
	</h4>
</div>
<div class="widget-body">
	<div class="widget-main">
		<div class="row">
		
			<div class="col-xs-12 col-sm-3 center">
				<!-- CUSTOMER PICTURE -->
				<div class="row">
					<div class="col-md-12">
						<?php 
							$borderStyle = '';
							
							if( !empty($customer->gender) )
							{
								$borderStyle = strtolower($customer->gender) == 'male' ? 'border:3px solid #337ab7;' : 'border:3px solid #c6699f;';
							}
						?>
					
						<span class="profile-picture" style="<?php echo $borderStyle; ?>">
							<?php 
								if( !empty($customer) && $customer->getImage() != null )
								{
									echo CHtml::image($customer->getImage(), '', array('class'=>'responsive'));
								}
								else
								{
									echo '<div style="height:180px; border:1px dashed #ccc; text-align:center; line-height: 180px;">No Image Uploaded.</div>';
								}
							?>
						</span>
						
						<?php 
							if( $customer->company->is_host_dialer == 0 )
							{
								if( !empty($customer) && $customer->getVoice() != null )
								{
									echo '<div class="space-12"></div> <audio controls src="'.$customer->getVoice().'" id="audio" style="width:100%"></audio>';
								}
								else
								{
									echo '<div class="space-12"></div> No voice file.'; 
								}
							}
						?>
					</div>
				</div>
				
				<?php if( $customer->company->is_host_dialer == 0): ?>
				
				<!-- COMPANY PICTURE -->
				<div class="row">
					<div class="col-md-12">									
						<span class="profile-picture">
							<?php 
								if( !empty($customer->company) && $customer->company->getImage() != null )
								{
									echo CHtml::image($customer->company->getImage(), '', array('class'=>'responsive'));
								}
								else
								{
									echo '<div style="height:130px; border:1px dashed #ccc; text-align:center; line-height: 55px;">No Company Image Uploaded.</div>';
								}
							?>
						</span>
					</div>
				</div>
				<?php endif; ?>
				
			</div><!-- /.col -->

			<div class="col-xs-12 col-sm-9">	
				<div class="row-fluid office-info-wrapper">
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'HOST NAME';
									}
									else
									{
										echo 'CUSTOMER NAME';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span><?php echo $customer->firstname.' '.$customer->lastname; ?></span>
							</div>
						</div>
						
						<?php if( $customer->company->is_host_dialer != 1 ): ?>
						<div class="profile-info-row">
							<div class="profile-info-name"> ALSO KNOWN AS </div>

							<div class="profile-info-value">
								<span><?php echo $customer->name_alias; ?></span>
							</div>
						</div>
						
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo '';
									}
									else
									{
										echo 'OFFICE';
									}
								?>
								 
							</div>

							<div class="profile-info-value">
								<span>
									<?php echo CHtml::dropDownList('', $office->id, $officeOptions, array('id'=>'office-select', 'prompt'=>'- SELECT -')); ?>
								</span>
							</div>
						</div>
						<?php endif; ?>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'PROPERTY ADDRESS';
									}
									else
									{
										echo 'OFFICE ADDRESS';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span><?php echo $office->address; ?></span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'CITY';
									}
									else
									{
										echo 'OFFICE CITY';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span><?php echo $office->city; ?></span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'STATE';
									}
									else
									{
										echo 'OFFICE STATE';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span>
									<?php 
										$state = State::model()->findByPk($office->state);
										
										if( $state )
										{
											echo $state->name;
										}
									?>
								</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								ZIP CODE
							</div>

							<div class="profile-info-value">
								<span>
									<?php echo $customer->zip; ?>
								</span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'HOST PHONE #';
									}
									else
									{
										echo 'OFFICE PHONE #';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span><?php echo $office->phone; ?></span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'HOST EMAIL';
									}
									else
									{
										echo 'OFFICE EMAIL';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span><?php echo $office->email_address; ?></span>
							</div>
						</div>
						
						<!--<div class="profile-info-row">
							<div class="profile-info-name"> CUSTOMER WEBSITE </div>

							<div class="profile-info-value">
								<span></span>
							</div>
						</div>-->
						
						<div class="profile-info-row">
							<div class="profile-info-name"> 
								<?php 
									if( $customer->company->is_host_dialer == 1 )
									{
										echo 'HOST NOTES';
									}
									else
									{
										echo 'CUSTOMER NOTES';
									}
								?>
							</div>

							<div class="profile-info-value">
								<span>
									<?php echo $customer->notes; ?>
								</span>
							</div>
						</div>
					</div>
				</div>
		
			</div><!-- /.col -->
		</div>
	</div>
</div>