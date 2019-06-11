<?php 
	if( $leadHopperEntry->type == LeadHopper::TYPE_CONFIRMATION_CALL )
	{
		$pdfFile = $leadHopperEntry->confirmChildSkill->scriptFileupload->generated_filename;
	}
	elseif( $leadHopperEntry->type == LeadHopper::TYPE_RESCHEDULE )
	{
		$pdfFile = $leadHopperEntry->rescheduleChildSkill->scriptFileupload->generated_filename;
	}
	else
	{
		if( isset($leadHopperEntry->customer->company) && $leadHopperEntry->customer->company->customer_specific_skill_scripts == 1 )
		{
			$customerSkill = CustomerSkill::model()->find(array(
				'condition' => 'customer_id = :customer_id AND skill_id = :skill_id AND status=1',
				'params' => array(
					':customer_id' => $leadHopperEntry->customer_id,
					':skill_id' => $leadHopperEntry->skill_id,
				),
			));
			
			if( $customerSkill )
			{
				$pdfFile = $customerSkill->scriptFileupload->original_filename;
			}
			else
			{
				$pdfFile = $leadHopperEntry->skill->scriptFileupload->generated_filename;
			}
		}
		else
		{
			$pdfFile = $leadHopperEntry->skill->scriptFileupload->generated_filename;
		}
	}
?>

<div class="row">
	<div class="col-md-12">
		<embed id="scriptPdf" src="<?php echo Yii::app()->request->baseUrl.'/fileupload/'.$pdfFile; ?>" class="col-md-12" style="height:800px; border:3px solid #333;">
	</div>
</div>