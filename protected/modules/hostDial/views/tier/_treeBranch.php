<li id="<?php echo $tier->id; ?>" class="tree-branch" role="treeitem" aria-expanded="false">
	<div class="tree-branch-header">
		
		<span class="tree-branch-name">
			<i class="icon-folder ace-icon tree-plus"></i>
			
			<span class="tree-label">
			<?php echo $tier->tier_name; ?>  
			</span>
		</span>
		
		<?php echo CHtml::link('Select','javascript:void(0);',array(
			'id' => $tier->id,
			'class' => 'btn btn-minier select-tier',
			'tier_Company_Id' => $tier->company_id,
			'tier_Name' => $tier->tier_name,
		)); ?>
		
	</div>
	
	<ul class="tree-branch-children"></ul>
	
	<div class="tree-loader" style="display: none;">
		<div class="tree-loading"><i class="icon-refresh icon-spin blue"></i></div>
	</div>
</li>