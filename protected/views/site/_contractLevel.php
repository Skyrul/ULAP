<?php if( $contract->fulfillment_type == Contract::TYPE_FULFILLMENT_GOAL_VOLUME ): ?>

	<div class="subsidy-containers" id="goal-volume-container">
		<?php 
			if(!empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME])){
				foreach($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_GOAL_VOLUME] as $key => $subsidyLevel){
					$this->renderPartial('_goalVolume',array(
						'subsidyLevel' => $subsidyLevel,
						'selectedCustomerEnrollment' => $model,
					));
				}
			} 
		?>
	</div>

<?php else: ?>

	<div class="subsidy-containers" id="lead-volume-container">
		<?php 
			if(!empty($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME])){
				foreach($contract->subsidyLevelArray[Contract::TYPE_FULFILLMENT_LEAD_VOLUME] as $key => $subsidyLevel){
					$this->renderPartial('_leadVolume',array(
						'subsidyLevel' => $subsidyLevel,
						'selectedCustomerEnrollment' => $model,
					));
				}
			} 
		?>
	</div>

<?php endif; ?>