<?php
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;

	$cs->registerScriptFile($baseUrl . '/js/hr/trainingLibrary/index.js?time='.time());
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
				<button class="btn btn-success btn-xs add-category"><i class="fa fa-plus"></i> Add</button> Training Library Manager
			</h1>
		</div>

		<div class="row">
			<div class="col-sm-12 category-wrapper">
				<?php 
					if( $categories )
					{
						foreach( $categories as $category )
						{
							$categoryChecked = $category->status == 1 ? 'checked' : '';
							
							$categoryModels = TrainingLibraryFile::model()->findAll(array(
								'condition' => 'category_id = :category_id AND status != 3',
								'params' => array(
									':category_id' => $category->id,
								),
								'order' => 'sort_order ASC',
							));
							
							$categoryDataProvider = new CArrayDataProvider($categoryModels, array(
								'pagination' => array(
									'pageSize' => 50,
								),
							));
							
							$this->widget('zii.widgets.CListView', array(
								'id'=>'learningCenter'.str_replace(' ', '', ucfirst($category->name)).'List',
								'dataProvider' => $categoryDataProvider,
								'itemView'=>'_list',
								'template'=>'
									<table class="table table-hover table-striped table-condensed">
										<tr>
											<th width="50%">
												'.ucfirst($category->name).'
												<label>
													<small>
														<input type="checkbox" class="toggle-learning-center-category ace ace-switch ace-switch-1" category_id="'.$category->id.'" value="'.$category->status.'" '.$categoryChecked.'>
														<span class="lbl middle"></span>
													</small>
												</label>
											</th>
											<th>
												<button class="btn btn-success btn-xs add-file" category_id="'.$category->id.'"><i class="fa fa-plus"></i> Add</button>
												<button class="btn btn-warning btn-xs edit-category" id="'.$category->id.'"><i class="fa fa-pencil"></i> Edit</button>
												<button class="btn btn-danger btn-xs delete-category" category_id="'.$category->id.'"><i class="fa fa-remove"></i> Delete</button>
											</th>
										</tr>
										{items}
									</table>
								',
								'emptyText' => '<tr><td colspan="2">No files found.</td></tr>',
							)); 
						}
					}
					else
					{
						echo 'No categories found.';
					}
				?>
			</div>
		</div>
	</div>
</div>

<div class="space-12"></div>
<div class="space-12"></div>


