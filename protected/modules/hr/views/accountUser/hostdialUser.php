<style>
	.org-tree ul {
		padding-top: 20px; position: relative;
		
		transition: all 0.5s;
		-webkit-transition: all 0.5s;
		-moz-transition: all 0.5s;
	}

	.org-tree li {
		float: left; text-align: center;
		list-style-type: none;
		position: relative;
		padding: 20px 5px 0 5px;
		
		transition: all 0.5s;
		-webkit-transition: all 0.5s;
		-moz-transition: all 0.5s;
	}

	/*We will use ::before and ::after to draw the connectors*/

	.org-tree li::before, .org-tree li::after{
		content: '';
		position: absolute; top: 0; right: 50%;
		border-top: 1px solid #ccc;
		width: 50%; height: 20px;
	}
	.org-tree li::after{
		right: auto; left: 50%;
		border-left: 1px solid #ccc;
	}

	/*We need to remove left-right connectors from elements without 
	any siblings*/
	.org-tree li:only-child::after, .org-tree li:only-child::before {
		display: none;
	}

	/*Remove space from the top of single children*/
	.org-tree li:only-child{ padding-top: 0;}

	/*Remove left connector from first child and 
	right connector from last child*/
	.org-tree li:first-child::before, .org-tree li:last-child::after{
		border: 0 none;
	}
	/*Adding back the vertical connector to the last nodes*/
	.org-tree li:last-child::before{
		border-right: 1px solid #ccc;
		border-radius: 0 5px 0 0;
		-webkit-border-radius: 0 5px 0 0;
		-moz-border-radius: 0 5px 0 0;
	}
	.org-tree li:first-child::after{
		border-radius: 5px 0 0 0;
		-webkit-border-radius: 5px 0 0 0;
		-moz-border-radius: 5px 0 0 0;
	}

	/*Time to add downward connectors from parents*/
	.org-tree ul ul::before{
		content: '';
		position: absolute; top: 0; left: 50%;
		border-left: 1px solid #ccc;
		width: 0; height: 20px;
	}

	.org-tree li a{
		border: 1px solid #ccc;
		padding: 5px 10px;
		text-decoration: none;
		color: #666;
		font-family: arial, verdana, tahoma;
		font-size: 11px;
		display: inline-block;
		width:120px;
		height:150px;
		
		border-radius: 5px;
		-webkit-border-radius: 5px;
		-moz-border-radius: 5px;
		
		transition: all 0.5s;
		-webkit-transition: all 0.5s;
		-moz-transition: all 0.5s;
	}
	
	.org-tree li a img { 
		margin:0 auto;
		width:80px;
		height:80px;
	}

	/*Time for some hover effects*/
	/*We will apply the hover effect the the lineage of the element also*/
	.org-tree li a:hover, .org-tree li a:hover+ul li a {
		background: #c8e4f8; color: #000; border: 1px solid #94a0b4;
	}
	/*Connector styles on hover*/
	.org-tree li a:hover+ul li::after, 
	.org-tree li a:hover+ul li::before, 
	.org-tree li a:hover+ul::before, 
	.org-tree li a:hover+ul ul::before{
		border-color:  #94a0b4;
	}
</style>

<script>
	$(document).ready( function(){
		
		$(".collapse-user-list").on("click", function(){
			
			var icon = $(this).find("i");
			
			if( icon.hasClass("fa-arrow-left") )
			{
				icon.removeClass("fa-arrow-left");
				icon.addClass("fa-arrow-right");
				
				$(".user-list-wrapper").hide();
				
				$(".org-chart-wrapper").removeClass("col-md-7");
				$(".org-chart-wrapper").addClass("col-md-12");
			}
			else
			{
				icon.removeClass("fa-arrow-right");
				icon.addClass("fa-arrow-left");
				
				$(".user-list-wrapper").fadeIn();
			
				$(".org-chart-wrapper").removeClass("col-md-12");
				$(".org-chart-wrapper").addClass("col-md-7");
			}
			
		});
		
		
		$(document).ready( function(){
			
			$(document).on("keyup", ".employee-search-input", function(e) {
		
				e.preventDefault();
				
				var search_query = $(".employee-search-input").val();
				var search_filter = $(".employee-search-filter").find(":radio:checked").val();
				
				$.fn.yiiListView.update("account-user-grid", { data: { search_query: search_query, search_filter:search_filter } });
			});
			
			$(document).on("click", ".employee-search-filter", function(e){
				
				e.preventDefault();
				
				var search_query = $(".employee-search-input").val();
				var search_filter = $(this).find(":radio").val();
	
				$.fn.yiiListView.update("account-user-grid", { data: { search_query: search_query, search_filter:search_filter } });
			});
			
		});
	});
</script>

<div class="tabbable tabs-left">
	<ul id="myTab" class="nav nav-tabs">
		<?php if( Yii::app()->user->account->checkPermission('employees_employees_tab','visible') ){ ?>
		
		<li class="<?php echo Yii::app()->getController()->getId() == 'accountUser' && Yii::app()->controller->action->id == 'index'  ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('index'); ?>">
				Employees
			</a>
		</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_hostdial_users_tab','visible') ){ ?>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'hostdialUser' ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('hostdialUser'); ?>">
				Hostdial Users
			</a>
		</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_permissions_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'permission' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/permission'); ?>">
					Permissions
				</a>
			</li>
		<?php } ?>

		<?php if( Yii::app()->user->account->checkPermission('employees_teams_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'team' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/team'); ?>">
					Teams
				</a>
			</li>
			
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_news_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'news' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/news'); ?>">
					News
				</a>
			</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('training_library_main_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'trainingLibrary' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/trainingLibrary'); ?>">
					Training Library
				</a>
			</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_texting_main_tab','visible') ){ ?>
		
			<li class="<?php echo Yii::app()->getController()->getId() == 'texting' ? 'active' : ''; ?>">
				<a href="<?php echo $this->createUrl('/hr/texting'); ?>">
					Texting
				</a>
			</li>
		
		<?php } ?>
	</ul>
	<div class="tab-content">
		
		<?php
			foreach(Yii::app()->user->getFlashes() as $key => $message) {
				echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
			}
		?>
		
		<div class="row">
			<div class="col-md-5 user-list-wrapper">
				<div class="row">
					<div class="col-sm-12">
						<div class="page-header">
							<h1>
								Users
								<?php 
									if( Yii::app()->user->account->checkPermission('employees_add_users_button','visible') )
									{
										echo CHtml::link('<i class="fa fa-plus"></i> Add Users',array('create'),array('class'=>'btn btn-sm btn-primary')); 
									}
								?>
							</h1>
						</div>
					</div>
				</div>
				
				<div class="row">
					<form id="customerSearchForm">
						<div class="col-md-8">
							<div class="btn-group btn-corner" data-toggle="buttons">
								
								<label class="btn btn-white btn-sm btn-primary employee-search-filter active">
									<input type="radio" value="showAll" checked>
									Show All
								</label>
								
								<label class="btn btn-white btn-sm btn-primary employee-search-filter">
									<input type="radio" value="hideInactive">
									Hide Inactives
								</label>				
							</div>
						</div>
						<div class="col-md-4 text-right">
							<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
								<span class="input-icon">
									<input type="text" autocomplete="off" class="nav-search-input employee-search-input" placeholder="Search Host Dial Users..." style="width:200px;">
									<i class="ace-icon fa fa-search nav-search-icon"></i>
								</span>
							</div>
						</div>
					</form>
				</div>

				<div class="space-6"></div>

				<?php $this->widget('zii.widgets.CListView', array(
					'id'=>'account-user-grid',
					// 'dataProvider'=>$model->search(),
					'dataProvider'=>$dataProvider,
					'itemView'=>'_hostDialUserListPartial',
					'template'=>'
						<table class="table table-striped table-hover">
							<tr>
								<th>First Name</th>
								<th>Last Name</th>
								<th>Job Title</th>
								<th>Action</th>
							</tr>
							{items}
						</table><br>
						<div class="text-center">{pager}</div>
							
					',
					'pagerCssClass'=>'pagination',
				)); ?>
			</div>
		</div>
	</div>
</div>
