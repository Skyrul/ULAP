<?php
	$baseUrl = Yii::app()->request->baseUrl;
	$cs = Yii::app()->clientScript;

	$cs->registerScriptFile($baseUrl . '/js/admin/learningCenter/index.js?t='.time());
	
	$cs->registerScript(uniqid(), '
		
		$(document).on("keyup", ".learning-center-label-input", function(){
			
			value = $(this).val();
			company_id = $(this).attr("company_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/learningCenter/updateLearningCenterLabel",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "value": value, "company_id":company_id },
				success: function(response) {

				}
			});
			
		});
	
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.CompanySideMenu",array(
		'active'=> 'learningCenter',
		'company' => $company,
	));
?>

<div class="page-header">
	<h1>
		<button class="btn btn-success btn-xs add-category" company_id="<?php echo $company->id; ?>"><i class="fa fa-plus"></i> Add</button> 
		<input type="text" class="learning-center-label-input" company_id="<?php echo $company->id; ?>" value="<?php echo $company->learning_center_label; ?>">
		<label>
			<small>
				<input type="checkbox" class="toggle-learning-center-tab ace ace-switch ace-switch-1" company_id="<?php echo $company->id; ?>" value="<?php echo $company->display_learning_center_tab; ?>" <?php echo $company->display_learning_center_tab ? 'checked':''; ?>>
				<span class="lbl middle"></span>
			</small>
		</label>
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
					
					$categoryModels = CompanyLearningCenterFile::model()->findAll(array(
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
												<input type="checkbox" class="toggle-learning-center-category ace ace-switch ace-switch-1" company_id="'.$company->id.'" category_id="'.$category->id.'" value="'.$category->status.'" '.$categoryChecked.'>
												<span class="lbl middle"></span>
											</small>
										</label>
									</th>
									<th>
										<button class="btn btn-success btn-xs add-file" company_id="'.$company->id.'" category_id="'.$category->id.'"><i class="fa fa-plus"></i> Add</button>
										<button class="btn btn-warning btn-xs edit-category" id="'.$category->id.'"><i class="fa fa-pencil"></i> Edit</button>
										<button class="btn btn-danger btn-xs delete-category" company_id="'.$company->id.'" category_id="'.$category->id.'"><i class="fa fa-remove"></i> Delete</button>
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

<div class="space-12"></div>
<div class="space-12"></div>


