<?php
/* @var $this SurveyController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Surveys',
);
?>

<div class="page-header">
	<h1>
		Survey
		
		<?php echo CHtml::link('<i class="fa fa-plus"></i> New', array('survey/create'), array('class'=>'btn btn-white btn-success btn-bold')); ?>
	</h1>
</div>

<div class="row">
	<div class="col-xs-12">
	
		<?php 
			$this->widget('zii.widgets.CListView', array(
				'dataProvider' => $dataProvider,
				'itemsTagName' => 'div',
				'itemView' => '_view',
				'viewData' => array(),
				'emptyText' => '<tr><td colspan="3">No entries found.</td></tr>',
				'template' => '<table class="table table-striped table-bordered table-hover table-condensed">{items}</table> <div class="hidden-table-info_info" class="dataTables_info">{summary}</div> <div id="hidden-table-info_paginate" class="dataTables_paginate paging_two_button pull-right">{pager}</div>',
				'pagerCssClass' => 'dataTables_paginate paging_bootstrap pagination',
				'pager' => array(
					'header' => false,
					// 'cssFile' => Yii::app()->request->baseUrl . '/assets/css/bootstrap.min.css',
					'prevPageLabel' => '< Previous',
					'firstPageLabel' => '<< First',
					'lastPageLabel' => 'Last >>',                  
					'nextPageLabel' => 'Next >',
				),
			)); 
		?>

	</div>
</div>


