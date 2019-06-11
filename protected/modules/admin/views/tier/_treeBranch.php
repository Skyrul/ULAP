<li id="<?php echo $tier->id; ?>" class="tree-branch" role="treeitem" aria-expanded="false">
	<div class="tree-branch-header">
		
		<span class="tree-branch-name">
			<i class="icon-folder ace-icon tree-plus"></i>
			
			<span class="tree-label">
			<?php echo $tier->tier_name; ?>  
			</span>
		</span>
		
		<?php echo CHtml::link('Add','javascript:void(0);',array(
			'id' => 'parentTier-'.$tier->tier_name,
			'class' => 'btn btn-minier add-child-tier',
			'tier_ParentTier_Id' => $tier->id,
			'tier_ParentSubTier_Id' => $tier->id,
			'tier_Company_Id' => $tier->company_id,
			'tier_Level' => $tier->tier_level,
			'tier_Name' => $tier->tier_name,
		)); ?>
		
		<?php echo CHtml::link('Edit','javascript:void(0);',array(
			'id' => $tier->id,
			'class' => 'btn btn-minier edit-tier',
			'tier_Company_Id' => $tier->company_id,
			'tier_Name' => $tier->tier_name,
		)); ?>
		
	</div>
	
	<ul class="tree-branch-children"></ul>
	
	<div class="tree-loader" style="display: none;">
		<div class="tree-loading"><i class="icon-refresh icon-spin blue"></i></div>
	</div>
</li>