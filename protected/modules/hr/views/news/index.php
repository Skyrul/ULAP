<?php 

$cs = Yii::app()->clientScript;

$cs->registerScript(uniqid(), '

	$(document).ready( function(){
		
		$(document).on("keyup", ".news-order-txt", function(){
			
			var id = $(this).prop("id");
			var order = $(this).val();
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/hr/news/ajaxSaveOrder",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "id":id, "order":order },
				success: function(r){ console.log(r) }
			});
			
		});
		
	});

', CClientScript::POS_END);

?>

<div class="tabbable tabs-left">
	<ul id="myTab" class="nav nav-tabs">
		<li class="<?php echo Yii::app()->getController()->getId() == 'accountUser' ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('/hr'); ?>">
				Employees
			</a>
		</li>
		
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
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="col-sm-12">
						<div class="page-header">
							<h1>
								News
								<?php echo CHtml::link('<i class="fa fa-plus"></i> Add News',array('create'),array('class'=>'btn btn-sm btn-primary')); ?>
							</h1>
						</div>
					</div>
				</div>
				
				<?php
					foreach(Yii::app()->user->getFlashes() as $key => $message) {
						echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
					}
				?>

				<?php $this->widget('zii.widgets.CListView', array(
					'id'=>'news-list',
					// 'dataProvider'=>$model->search(),
					'dataProvider'=>$dataProvider,
					'itemView'=>'_list',
					'template'=>'
						<table class="table table-bordered table-striped table-hover table-condensed">
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