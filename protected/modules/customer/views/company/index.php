<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	
?>

<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> Yii::app()->controller->id,
		'customer' => $customer,
	));
?>

<div class="row" style="min-height:400px;">
	<div class="col-xs-12 col-sm-4">
		<div class="widget-box transparent">
			<div class="center">
				<span>
					<?php 
						if($company->getImage())
						{
							echo CHtml::image($company->getImage(), '', array('style'=>'/* width:100%; */'));
						}
						else
						{
							echo 'No image uploaded yet.'; 
						}
					?>
				</span>
				
				<div class="space-4"></div>
				
				<?php/*<div class="width-20 label label-primary label-xlg arrowed-in arrowed-in-right">
					<div class="inline position-relative">
						<a href="#" class="user-title-label">
							<span class="white"><?php echo $company->company_name; ?></span>
						</a>
					</div>
				</div>*/ ?>
			</div>
			
			<div class="space-6"></div>
		
			<div class="widget-header widget-header-small">
				<h4 class="widget-title blue smaller">
					<i class="ace-icon fa fa-files-o orange"></i>
					Company Files
				</h4>
			</div>
			
			<div class="widget-body">
				<div class="widget-main padding-8">
					<div class="profile-feed">
						<?php 
							if( $companyFiles )
							{
								foreach( $companyFiles as $file )
								{
									if( isset($file->fileUpload) )
									{
										$date = new DateTime($file->date_created, new DateTimeZone('America/Chicago'));
										$date->setTimezone(new DateTimeZone('America/Denver'));	
										
										echo '<div class="profile-activity clearfix">';
											echo '<div>';
												echo CHtml::link($file->fileUpload->original_filename, array('/site/download', 'file'=>$file->fileUpload->original_filename,'CompanyFile[id]'=>$file->id));
												
												echo '<div class="time">';
													echo '<i class="ace-icon fa fa-clock-o bigger-110"></i> ';
														echo $date->format('m/d/Y g:i A'); 
												echo '</div>';
											echo '</div>';
										echo '</div>';
									}
								}
							}
							else
							{
								echo 'No files found.';
							}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>
	
	<?php if( $company->display_flyer_image == 1 ): ?>
	
	<div class="col-xs-12 col-sm-8 center">
		<div class="profile-picture">
			<!--<img style="width:100%" src="" alt="http://www.sawyoo.com/postpic/2010/09/free-business-flyer-templates_14189.jpg">-->	
			<?php 
				
				if($company->getFlyerImage())
				{
					echo CHtml::image($company->getFlyerImage(), '', array('style'=>'width:100%;'));
				}
				else
				{
					echo 'No image uploaded yet.'; 
				}
			?>
		</div>
	</div>
	
	<?php else: ?>
	
	<div class="col-xs-12 col-sm-8">
		<div class="profile-picture" style="padding:10px;">
			<?php echo $company->flyer_message; ?>
		</div>
	</div>
	
	<?php endif; ?>
</div>