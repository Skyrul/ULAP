<?php 
	echo CHtml::hiddenField('news_offset', $offset, array('class'=>'current-news-offset'));
	
	if($htmlNewsPosts)
	{
		$ctr = 1;
		
		foreach( $htmlNewsPosts as $htmlNewsPost )
		{
			$style = '';
			
			if( $ctr == 1)
			{
				$style = 'border-right: 3px solid #e3e3e3;';
			}
			
		?>
			<div class="col-sm-6" style="<?php echo $style; ?>">
					
				<h1 id="<?php echo $htmlNewsPost['id']; ?>" class="html-news-<?php echo $ctr; ?>-title"><?php echo $htmlNewsPost['title']; ?></h1>
				
				<div class="space-12"></div>
				
				<div class="news-body-container">
					<?php 
						if( Yii::app()->getBaseUrl(true) == 'https://portal.engagexapp.com' )
						{
							echo str_replace('/ulap', '', $htmlNewsPost['body']); 
							
						}
						elseif( Yii::app()->getBaseUrl(true) == 'http://system.engagexapp.com/ulap' )
						{
							echo str_replace('/fileupload/', Yii::app()->getBaseUrl(true).'/fileupload/', $htmlNewsPost['body']); 
						}
						else
						{
							echo $htmlNewsPost['body']; 
						}
					?>
				</div>
				
				<div class="space-12"></div>
				
				<div class="well well-sm">
					<div class="row">
						<div class="col-sm-6">
							<img class="pull-left" style="border: 2px solid #c9d6e5; border-radius: 100%; box-shadow: none;margin-left: 0; margin-right: 10px; max-width: 40px;" src="<?php echo Account::model()->findByPk($htmlNewsPost['account_id'])->accountUser->getImage(); ?>">
							<div>
								<span class="blue"> <?php echo Account::model()->findByPk($htmlNewsPost['account_id'])->accountUser->getFullName(); ?> </span>
								<br />
								<i class="ace-icon fa fa-clock-o bigger-110"></i> 
								<?php 
									$date = new DateTime($htmlNewsPost['date_created'], new DateTimeZone('America/Chicago'));

									$date->setTimezone(new DateTimeZone('America/Denver'));

									echo $date->format('m/d/Y g:i A');
								?>
							</div>
						</div>

						
						<div class="col-sm-6"></div>
					</div>
				</div>
				
			</div>
		<?php
		$ctr++;
		}
	}
	else
	{
	?>
	
		<div class="col-sm-12">

			<div class="well well-sm">No records found.</div>
		</div>
	
	<?php
	}
?>