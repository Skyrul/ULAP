<div class="tabbable tabs-left">
	<ul id="myTab" class="nav nav-tabs">
		<?php if( Yii::app()->user->account->checkPermission('employees_employees_tab','visible') ){ ?>
		
		<li class="<?php echo Yii::app()->getController()->getId() == 'accountUser' && Yii::app()->controller->action->id == 'index'  ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('accountUser/index'); ?>">
				Employees
			</a>
		</li>
		
		<?php } ?>
		
		<?php if( Yii::app()->user->account->checkPermission('employees_hostdial_users_tab','visible') ){ ?>
		
		<li class="<?php echo Yii::app()->controller->action->id == 'hostdialUser' ? 'active' : ''; ?>">
			<a href="<?php echo $this->createUrl('accountUser/hostdialUser'); ?>">
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
		<div class="row">
			<div class="col-md-12">
				<div class="row">
					<div class="col-sm-12">
						<div class="page-header">
							<h1>Permissions</h1>
						</div>
					</div>
				</div>
				
				<?php
					foreach(Yii::app()->user->getFlashes() as $key => $message) {
						echo '<div class="alert alert-' . $key . '"><button data-dismiss="alert" class="close" type="button"><i class="ace-icon fa fa-times"></i></button> ' . $message . "</div>\n";
					}
				?>

				<table class="table table-bordered table-striped table-hover table-condensed">
				
					<thead>
						<tr>
							<th>Security Group</th>
							<th class="center">Action</th>
						</tr>
					</thead>
					
					<?php foreach( Account::listAccountType() as $accountTypeId => $accountTypeName ): ?>
					
						<tr>
							<td><?php echo $accountTypeName; ?></td>
							<td class="center"><?php echo CHtml::link('<i class="fa fa-pencil"></i> Edit', array('update', 'id'=>$accountTypeId), array('class'=>'btn btn-minier btn-info')); ?></td>
						</tr>
					
					<?php endforeach; ?>
					
				</table>
			</div>
		</div>
	</div>
</div>
