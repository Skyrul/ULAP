<?php 
	$this->pageTitle = 'Engagex - Note View';
?>

<div class="">				
	<div style="clear:both;"></div>
</div>

<br />

<div class="tabbable tabs-left">
	<div class="tab-content">
		<div class="tab-pane in active">
			<div class="page-header">
				<h1>Note</h1>
			</div>

			<div class="row">
				<div class="col-sm-12">
					<?php

					if( $model )
					{
						echo $model->agent_note;
					}
					else
					{
						echo 'Note not found.';
					}

					?>
				</div>
			</div>
		</div>
	</div>
</div>