<div class="row">
	<div class="col-md-offset-10 col-md-2">
		<?php echo  CHtml::link('<i class="fa fa-file-excel-o"></i> Export',array('queueViewer/export','skill_id'=>$skill_id),array('class'=>'btn btn-yellow btn-sm')); ?>
	</div>
</div>
	
<div class="row">
	<div class="col-sm-2">
		<?php echo CHtml::dropDownList('is_header', $is_header, array("On" => "On", "Off" => "Off"), array('empty'=>'-', 'class'=>'is-table-header')); ?>
	</div>
	
	<div class="col-sm-4">
		<label>Filter By Skill:</label>
		<?php echo CHtml::dropDownList('skill_id', $skill_id, $queueSkillList, array('empty'=>'-Select Skill-', 'class'=>'filter-by-skill')); ?>
	</div>
	
	<div class="col-sm-4">
		<label>Filter By Campaign:</label>
		<?php echo CHtml::dropDownList('campaign_id', $campaign_id, $queueCampaignList, array('empty'=>'-Select Campaign-', 'class'=>'filter-by-campaign')); ?>
	</div>
	
	<?php if( in_array(Yii::app()->user->account->id, array(1,2)) ): ?>
	
		<div class="col-sm-2 text-right">
			
			<button class="btn btn-sm btn-yellow force-queue-link">
				<i class="fa fa-ambulance"></i> Force Queue
			</button>
			
		</div>
	
	<?php endif; ?>
</div>