<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'lead',
		'customer' => $customer_id ? Customer::model()->findByPk($customer_id) : null,
	));
?>

<div class="page-header">
	<h1>
		<?php echo $survey->survey_name; ?>
		<small><?php echo $lead->getFullName(); ?></small>
	</h1>
</div>

<div class="form">
<?php
	foreach($formOrder as $survey)
	{
		$answer = $survey['answer'];
		if(is_array($survey['answer']))
			$answer = implode(', ',$survey['answer']);
		
		echo '<div class="form-group row" style="border-top:1px solid #ddd">';
			echo '<div class="col-sm-12">';
				echo '<label>';
					echo $survey['question'];
				echo '</label>';
				
				if(isset($survey['answer_other']))
					echo $answer.': '.$survey['answer_other'];
				else
					echo $answer;
					
			echo '</div>';
		echo '</div>';
		

		if(isset($survey['child']))
		{
			foreach($survey['child'] as $child)
			{
				
				$answer = $child['answer'];
				if(is_array($child['answer']))
					$answer = implode(', ',$child['answer']);
		
				echo '<div class="form-group row" style="background-color:#428bca;">';
					echo '<div class="col-sm-12">';
						echo '<label>';
						echo $child['question'];
						echo '</label>';
						
						if(isset($child['answer_other']))
							echo $answer.': '.$child['answer_other'];
						else
							echo $answer;
						
					echo '</div>';
				echo '</div>';
			}
		}
			
			
	}
	
	
	
?>
</div>