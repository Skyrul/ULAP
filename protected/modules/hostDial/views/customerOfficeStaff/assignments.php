<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	 
	$cs = Yii::app()->clientScript;
	
	$cs->registerCssFile('//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css'); 
	
	$cs->registerCss(uniqid(), '
		.ui-sortable {
			border: 1px solid #eee;
			width: 100%;
			min-height: 40px;
			list-style-type: none;
			margin: 0;
			padding: 5px 0 0 0;
			margin-right: 10px;
		}

		.ui-sortable li {
			margin: 0 5px 5px 5px;
			padding: 5px;
			font-size: 1.2em;
			width: 95%;
		}
	');
	
	$cs->registerScriptFile( $baseUrl.'/js/jquery.bootstrap-duallistbox.min.js' ); 

	$cs->registerScript(uniqid(),'
		
		$( "#sortableLanguagesAvailable, #sortableLanguagesAssigned" ).sortable({
		  connectWith: ".languageSortable",
		  receive: function(event, ui) {
			   
			var container_id = $(this).attr("id");
			var item_id = ui.item.attr("data-id");
			
			var ajax_url;
			var type;
			
			if(container_id == "sortableLanguagesAvailable")
			{
				ajax_url = yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/updateAccountLanguage";
				type = "remove";
			}
			
			if(container_id == "sortableLanguagesAssigned")
			{
				ajax_url = yii.urls.absoluteUrl + "/hostDial/customerOfficeStaff/updateAccountLanguage";
				type = "add";
			}
			
			$.ajax({
				url: ajax_url,
				type: "post",	
				data: { 
					"ajax": 1, 
					"account_id": account_id, 
					"item_id": item_id,
					"type": type, 
				},
				success: function(response){ },
			});
		}
		  
		}).disableSelection();
		
	',CClientScript::POS_END);
?>

<?php 
	if(!empty($model->customer) && !$model->customer->isNewRecord)
	{
		
		$this->widget("application.components.HostDialSideMenu",array(
			'active'=> Yii::app()->controller->id,
			'customer' => $model->customer,
		));

	}
?>

<div class="page-header">
	<h1 class="bigger">Assignments</h1>
</div>

<div class="tabbable">

	<ul class="nav nav-tabs">
	
		<li class="">
			<a href="<?php echo $this->createUrl('update', array('id'=>$model->id)); ?>">
				Profile
			</a>
		</li>
		
		<li class="">
			<a href="<?php echo $this->createUrl('timeKeeping', array('id'=>$model->id)); ?>">
				Time Keeping
			</a>
		</li>
		
		<li class="active">
			<a href="<?php echo $this->createUrl('assignments', array('id'=>$model->id)); ?>">
				Assignments
			</a>
		</li>
		
		<li class="">
			<a href="<?php echo $this->createUrl('performance', array('id'=>$model->id)); ?>">
				Performance
			</a>
		</li>
		
	</ul>
	
	<div class="tab-content" style="overflow:hidden;">
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="col-sm-12">
				<div class="col-sm-3">
					
					<?php if( Yii::app()->user->account->checkPermission('employees_assigments_languages','visible') && Yii::app()->user->account->checkPermission('employees_assigments_languages','only_for_direct_reports', $account->id) ){ ?>
					
					<div class="row">
						<div class="col-sm-5">
							<h2 class="lighter blue">Languages</h2>
						</div>
					</div>
					
					<div class="row">
						<div class="widget-box">
							<div class="widget-body">
								<div class="widget-main"> 
								
									<div class="row">
										<div class="col-sm-6" style="min-height:200px; border-right:1px solid #e3e3e3;">
											<div class="text-center">
												<label>Available</label>
											</div>

											<ul id="sortableLanguagesAvailable" class="languageSortable">
												<?php 
													$availableLanguages = AccountLanguageAssigned::items();
													
													if( $accountLanguages )
													{
														foreach( $accountLanguages as $accountLanguage )
														{
															unset( $availableLanguages[$accountLanguage->language_id] );
														}
													}
		
													foreach( $availableLanguages as $availableLanguageId => $availableLanguageLabel )
													{
														echo '<li class="ui-state-default" data-id="'.$availableLanguageId.'" >'.$availableLanguageLabel.'</li>';
													}
												?>
											</ul>
										</div>
										
										<div class="col-sm-6">
											<div class="text-center">
												<label>Assigned</label>
											</div>
											
											<ul id="sortableLanguagesAssigned" class="languageSortable">
												<?php 
													if( $accountLanguages )
													{
														foreach( $accountLanguages as $accountLanguage )
														{
															echo '<li class="ui-state-default" data-id="'.$accountLanguage->language_id.'" >'.AccountLanguageAssigned::items($accountLanguage->language_id).'</li>';
														}
													}
												?>											
											</ul>
										</div>
									</div>
		
								
								</div>
							</div>
						</div>
					</div>
					<?php } ?>
				</div>

			</div>
				
		</div>

	</div>
</div>