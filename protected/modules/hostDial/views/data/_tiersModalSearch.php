<ul id="tier-tree-<?php echo $company->id; ?>" class="tree tree-selectable" role="tree">
	<?php 
		if($company->parentTiers())
		{
			foreach($company->parentTiers() as $tier)
			{
				$this->renderPartial('/tier/_treeBranch',array(
					'tier' => $tier
				));
			}
		}
		else
		{
			echo 'No tiers found.';
		}
	?>
</ul>