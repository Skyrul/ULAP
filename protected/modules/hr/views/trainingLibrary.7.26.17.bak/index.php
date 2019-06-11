<?php

$baseUrl = Yii::app()->request->baseUrl;
$cs = Yii::app()->clientScript;

$cs->registerScriptFile($baseUrl . '/js/hr/trainingLibrary/index.js');
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

		<div class="page-header">
			<h1>
				Training Library
			</h1>
		</div>

		<div class="row">
			<div class="col-sm-12">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'trainingLibraryVideoList',
						'dataProvider' => $videosDataProvider,
						'itemView'=>'_list',
						'template'=>'
							<table class="table table-hover table-striped table-condensed">
								<tr>
									<th width="50%">VIDEO</th>
									<th><button class="btn btn-success btn-xs add-file" type="1">Add</button></th>
								</tr>
								{items}
							</table>
						',
						'emptyText' => '<tr><td colspan="2">No video found.</td></tr>',
					)); 
				?>
			</div>
		</div>

		<div class="space-12"></div>
		<div class="space-12"></div>

		<div class="row">
			<div class="col-sm-12">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'trainingLibraryAudioList',
						'dataProvider' => $audiosDataProvider,
						'itemView'=>'_list',
						'template'=>'
							<table class="table table-hover table-striped table-condensed">
								<tr>
									<th width="50%">AUDIO</th>
									<th><button class="btn btn-success btn-xs add-file" type="2">Add</button></th>
								</tr>
								{items}
							</table>
						',
						'emptyText' => '<tr><td colspan="2">No audio found.</td></tr>',
					)); 
				?>
			</div>
		</div>

		<div class="space-12"></div>
		<div class="space-12"></div>

		<div class="row">
			<div class="col-sm-12">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'trainingLibraryDocumentList',
						'dataProvider' => $documentsDataProvider,
						'itemView'=>'_list',
						'template'=>'
							<table class="table table-hover table-striped table-condensed">
								<tr>
									<th width="50%">DOCUMENTS</th>
									<th><button class="btn btn-success btn-xs add-file" type="3">Add</button></th>
								</tr>
								{items}
							</table>
						',
						'emptyText' => '<tr><td colspan="2">No document found.</td></tr>',
					)); 
				?>
			</div>
		</div>
		
		<div class="space-12"></div>
		<div class="space-12"></div>

		<div class="row">
			<div class="col-sm-12">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'trainingLibraryLinkList',
						'dataProvider' => $linksDataProvider,
						'itemView'=>'_list',
						'template'=>'
							<table class="table table-hover table-striped table-condensed">
								<tr> 
									<th width="50%">LINKS</th>
									<th><button class="btn btn-success btn-xs add-link" type="4">Add</button></th>
								</tr>
								{items}
							</table>
						',
						'emptyText' => '<tr><td colspan="2">No document found.</td></tr>',
					)); 
				?>
			</div>
		</div>
	</div>
</div>
