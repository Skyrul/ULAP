<?php 
	$controller = Yii::app()->controller->id;
	$action = Yii::app()->controller->action->id;
	$module = !empty(Yii::app()->controller->module->id) ? Yii::app()->controller->module->id : null;
	
?>

<div id="sidebar" class="sidebar h-sidebar navbar-collapse collapse">
	<script type="text/javascript">
		try{ace.settings.check('sidebar' , 'fixed')}catch(e){}
	</script>
	
	<?php /*
	<div class="sidebar-shortcuts" id="sidebar-shortcuts">
		<div class="sidebar-shortcuts-large" id="sidebar-shortcuts-large">
			<button class="btn btn-success">
				<i class="ace-icon fa fa-signal"></i>
			</button>

			<button class="btn btn-info">
				<i class="ace-icon fa fa-pencil"></i>
			</button>

			<!-- #section:basics/sidebar.layout.shortcuts -->
			<button class="btn btn-warning">
				<i class="ace-icon fa fa-users"></i>
			</button>

			<button class="btn btn-danger">
				<i class="ace-icon fa fa-cogs"></i>
			</button>

			<!-- /section:basics/sidebar.layout.shortcuts -->
		</div>

		<div class="sidebar-shortcuts-mini" id="sidebar-shortcuts-mini">
			<span class="btn btn-success"></span>

			<span class="btn btn-info"></span>

			<span class="btn btn-warning"></span>

			<span class="btn btn-danger"></span>
		</div>
	</div><!-- /.sidebar-shortcuts -->
	*/ ?>
	
	<?php /*
	<ul class="nav nav-list">
		
		<li class="<?php echo ($module == 'admin') ? 'active' : 'hover'; ?>">
			<a href="<?php echo Yii::app()->createUrl('admin'); ?>">
				<i class="menu-icon fa fa-building"></i>
				<span class="menu-text"> Structure</span>
			</a>

			<b class="arrow"></b>
		</li>

		<li class="<?php echo ($module == 'customer') ? 'active' : 'hover'; ?>">
			<a href="<?php echo Yii::app()->createUrl('customer'); ?>">
				<i class="menu-icon fa fa-user"></i>
				<span class="menu-text"> Customers</span>
			</a>

			<b class="arrow"></b>
		</li>
		
		<li class="<?php echo ($module == 'hr') ? 'active' : 'hover'; ?>">
			<a href="<?php echo Yii::app()->createUrl('hr'); ?>">
				<i class="menu-icon fa fa-group"></i>
				<span class="menu-text"> Employees </span>
			</a>

			<b class="arrow"></b>
		</li>
		
		<li class="<?php echo ($module == 'report') ? 'active' : 'hover'; ?>">
			<a href="<?php echo Yii::app()->createUrl('report'); ?>">
				<i class="menu-icon fa fa-file-text"></i>
				<span class="menu-text"> Reports </span>
			</a>

			<b class="arrow"></b>
		</li>	
		
		<li class="<?php echo ($module == 'billing') ? 'active' : 'hover'; ?>">
			<a href="<?php echo Yii::app()->createUrl('billing'); ?>">
				<i class="menu-icon fa fa-book"></i>
				<span class="menu-text"> Billing </span>
			</a>

			<b class="arrow"></b>
		</li>
		
		<!-- sample sub menu -->
		<?php /*
		<li class="hover <?php echo $controller == 'survey' ? 'active' : ''; ?>">
			<a href="<?php echo Yii::app()->createUrl('survey/index'); ?>">
				<i class="menu-icon fa fa-edit"></i>
				<span class="menu-text"> Survey </span>

				<b class="arrow fa fa-angle-down"></b>
			</a>

			<b class="arrow"></b>

			<ul class="submenu">
				<li class="hover">
					<a href="<?php echo Yii::app()->createUrl('survey/create'); ?>">
						<i class="menu-icon fa fa-caret-right"></i>
						New 
					</a>

					<b class="arrow"></b>
				</li>

				<li class="hover">
					<a href="<?php echo Yii::app()->createUrl('site/index'); ?>">
						<i class="menu-icon fa fa-caret-right"></i>
						Statistics / Reports
					</a>

					<b class="arrow"></b>
				</li>
			</ul>
		</li>
	
		
		
		
		<li class="hover">
			<a href="<?php echo Yii::app()->createUrl('site/index'); ?>">
				<i class="menu-icon fa fa-file-o"></i>
				<span class="menu-text"> Sign Up </span>
			</a>

			<b class="arrow"></b>
		</li>
		
		<li class="hover">
			<a href="<?php echo Yii::app()->createUrl('site/index'); ?>">
				<i class="menu-icon fa fa-bar-chart-o "></i>
				<span class="menu-text"> Reports </span>
			</a>

			<b class="arrow"></b>
		</li>
		
		<li class="hover">
			<a href="<?php echo Yii::app()->createUrl('site/index'); ?>">
				<i class="menu-icon fa fa-credit-card"></i>
				<span class="menu-text"> Billing </span>
			</a>

			<b class="arrow"></b>
		</li>
		
	</ul><!-- /.nav-list -->
	<?php */ ?>

	<!-- #section:basics/sidebar.layout.minimize -->
	<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
		<i class="ace-icon fa fa-angle-double-left" data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
	</div>

	<!-- /section:basics/sidebar.layout.minimize -->
	<script type="text/javascript">
		try{ace.settings.check('sidebar' , 'collapsed')}catch(e){}
	</script>
</div>