<li class="item-red clearfix" style="padding:9px;">
	<label class="inline">
		<span class="lbl"> 
			<b>Lead: <?php echo isset($data->lead) ? $data->lead->getFullName() : ''; ?></b> - 
			<?php 
				if( !empty($data->details) ) 
				{
					echo $data->details;
				}
				else
				{
					echo $data->agent_notes; 
				}
			?>
		</span>
	</label>

	<div class="pull-right action-buttons">
		<button type="button" id="<?php echo $data->id; ?>" current_date="<?php echo $data->start_date; ?>" class="btn btn-info btn-xs action-form-btn">
			Action <i class="fa fa-arrow-right"></i>
		</button>
	</div>
</li>
