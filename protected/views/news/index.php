<?php 

$cs = Yii::app()->clientScript;

$cs->registerCss(uniqid(), '

	.news-body-container{ height:500px; overflow:auto; }

');

$cs->registerScript(uniqid(), '

	$(document).ready( function(){
		
		$(document).on("click", ".next-news-btn, .previous-news-btn", function(){
			
			var this_button = $(this);
			
			var parent_container = $(".news-container");

			var current_offset_element = $(".current-news-offset");
			var current_offset_val = current_offset_element.val();
			
			var fetch_type;
			
			if( $(this).hasClass("next-news-btn") )
			{
				fetch_type = "next";
			}
			else
			{
				fetch_type = "previous";
			}
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/news/ajaxGetNews",
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1,
					"fetch_type": fetch_type,
					"current_offset": current_offset_val
				},
				beforeSend: function(){
					
					this_button.html("Loading...");
					
				},
				error: function(){ 
					
					if( fetch_type == "next" )
					{
						this_button.html("Next <i class=\"fa fa-arrow-right\"></i>");
					}
					else
					{
						this_button.html("Previous <i class=\"fa fa-arrow-left\"></i>");
					}
					
				},
				success: function(response){ 
				
					if(response.status == "success" )
					{
						parent_container.html(response.html);
					}
					else
					{
						alert(response.message);
					}
					
					if( fetch_type == "next" )
					{
						this_button.html("Next <i class=\"fa fa-arrow-right\"></i>");
					}
					else
					{
						this_button.html("Previous <i class=\"fa fa-arrow-left\"></i>");
					}
				}
				
			});
			
		});
		
		$(document).on("click", ".news-read-btn", function(){
			
			var this_button = $(this);
			var hide_button = this_button.next();
			
			var parent_container = this_button.parent().parent().parent().parent().parent();
			
			var current_news_id = $(this).prop("id");
			
			this_button.removeClass("news-read-btn");
			this_button.prop("disabled", true);
			this_button.html("Mark as Read <i class=\"fa fa-check\"></fa>");

			$.ajax({
				url: yii.urls.absoluteUrl + "/news/ajaxMarkAsRead",
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1,
					"current_news_id": current_news_id,
				},
				beforeSend: function(){ 
					this_button.html("Saving...");
				},
				error: function(){ },
				success: function(response){ 
				
					if( response.status == "success" )
					{
						this_button.removeClass("news-read-btn");
						this_button.removeClass("btn-success");
						this_button.addClass("btn-default");
						this_button.prop("disabled", true);
						this_button.html("Mark as Read <i class=\"fa fa-check\"></fa>");
						
						hide_button.addClass("news-hide-btn");
						hide_button.removeClass("btn-default");
						hide_button.addClass("btn-danger");
						hide_button.prop("disabled", false);
					}
					
				}
				
			});
			
		});
		
		$(document).on("click", ".news-hide-btn", function(){
			
			var this_button = $(this);
			
			var parent_container = this_button.parent().parent().parent().parent().parent();
			
			var current_news_id = $(this).prop("id");

			$.ajax({
				url: yii.urls.absoluteUrl + "/news/ajaxHide",
				type: "post",
				dataType: "json",
				data: { 
					"ajax": 1,
					"current_news_id": current_news_id,
				},
				beforeSend: function(){ 
					this_button.html("Saving...");
				},
				error: function(){ },
				success: function(response){ 
				
					if( response.status == "success" )
					{
						this_button.removeClass("news-hide-btn");
						this_button.removeClass("btn-danger");
						this_button.addClass("btn-default");
						this_button.prop("disabled", true);
						this_button.html("Hide <i class=\"fa fa-ban\"></fa>");
					}
					
				}
			});
			
		}); 
		
	});

', CClientScript::POS_END);

?> 

<div class="page-header">
	<h1>
		<i class="fa fa-newspaper-o"></i> News 
		
		<div class="pull-right">
			<?php echo CHtml::link('<span class="bigger-110"><i class="icon-on-right ace-icon fa fa-times red"></i> Close this page</span>', array('news/close'), array('class'=>'btn btn-md btn-primary btn-white btn-round close-btn')); ?>
		</div>
	</h1>
</div>

<div class="row">
	<div class="col-sm-12 news-container">
		<?php
			echo CHtml::hiddenField('news_offset', 0, array('class'=>'current-news-offset'));
			
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
								
								<?php 
									$existingHtmlMarkSettings = NewsAccountSettings::model()->find(array(
										'condition' => 'account_id = :account_id AND news_id = :news_id AND is_marked_read = 1',
										'params' => array(
											':account_id' => $authAccount->id,
											':news_id' => $htmlNewsPost['id'],
										),
									));
									
									$existingHtmlHideSettings = NewsAccountSettings::model()->find(array(
										'condition' => 'account_id = :account_id AND news_id = :news_id AND is_marked_hide = 1',
										'params' => array(
											':account_id' => $authAccount->id,
											':news_id' => $htmlNewsPost['id'],
										),
									));
								?>
								
								<div class="col-sm-6">
									<div class="pull-right" style="margin-top:10px;">
										
										<?php
											if( $existingHtmlMarkSettings )
											{
											?>
												<button id="<?php echo $htmlNewsPost['id']; ?>" class="btn btn-default btn-white btn-round btn-sm" disabled>Mark as Read <i class="fa fa-check"></i></button>
												
												<?php 
													if( $existingHtmlHideSettings )
													{
													?>
														<button id="<?php echo $htmlNewsPost['id']; ?>" class="btn btn-default btn-white btn-round btn-sm" disabled>Hide <i class="fa fa-ban"></i></button>
													<?php
													}
													else
													{
													?>
														<button id="<?php echo $htmlNewsPost['id']; ?>" class="btn btn-danger btn-white btn-round btn-sm news-hide-btn">Hide <i class="fa fa-ban"></i></button>
													<?php
													}
												?>
											<?php
											}
											else
											{
											?>
												<button id="<?php echo $htmlNewsPost['id']; ?>" class="btn btn-success btn-white btn-round btn-sm news-read-btn">Mark as Read <i class="fa fa-check"></i></button>
												<button id="<?php echo $htmlNewsPost['id']; ?>" class="btn btn-default btn-white btn-round btn-sm" disabled>Hide <i class="fa fa-ban"></i></button>
											<?php
											}
										?>
									</div>
								</div>
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
	</div>
</div>

<div class="space-12"></div>

<div class="row center">
	<div class="col-sm-12">
		<div class="col-sm-6 text-right">
			<button class="btn btn-primary btn-white btn-round btn-md previous-news-btn" style="width:100px;"><i class="fa fa-arrow-left"></i> Previous</button>			
		</div>
		<div class="col-sm-6 text-left">
			<button class="btn btn-primary btn-white btn-round btn-md next-news-btn" style="width:100px;">Next <i class="fa fa-arrow-right"></i></button>
		</div>
	</div>
</div>

<div class="space-12"></div>