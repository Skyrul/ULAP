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