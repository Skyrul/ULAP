<div class="modal fade">
	<div class="modal-dialog" style="width:750px;">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
				<h4 class="modal-title blue">
					<i class="fa fa-search"></i> 
					Lead Search
				</h4>
			</div>
			
			<div class="modal-body">
				<div class="row">
					<?php echo CHtml::beginForm(CHtml::normalizeUrl(array('leadSearch')), 'get', array('id'=>'lead-filter-form')); ?>
					
						<div class="col-sm-12">
							<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
								<span class="input-icon">
									<input type="text" autocomplete="off" class="nav-search-input lead-search-input" placeholder="Search Leads..." style="width:300px;">
									<i class="ace-icon fa fa-search nav-search-icon"></i>
								</span>
							</div>
						</div>
						
					<?php echo CHtml::endForm(); ?>
				</div>
				
				<div class="space-6"></div>
				
				<table id="leadSearchTable" class="table table-hover table-striped table-bordered table-condensed">
					<thead>
						<th>Name</th>
						<th>Home</th>
						<th>Mobile</th>
						<th>Office</th>
						<th>Customer Name</th>
						<th>Company</th>
						<th>Skill</th>
						<th width="5%">Options</th>
					</thead>

					<tbody>
						<?php 
							if( $models )
							{
								foreach( $models as $model )
								{
									$skillsTxt = '';
									
									$customerSkills = CustomerSkill::model()->findAll(array(
										'condition' => 'customer_id = :customer_id AND status=1',
										'params' => array(
											':customer_id' => $model->list->customer_id,
										),
									));
									
									if( $customerSkills )
									{
										foreach( $customerSkills as $customerSkill )
										{
											$skillsTxt .= $customerSkill->skill->skill_name.', ';
										}
									}
									
									$skillsTxt = rtrim($skillsTxt, ', ');
									
								?>
									<tr>
										<td><?php echo $model->first_name.' '.$model->last_name; ?></td>
										
										<td><?php echo !empty($model->home_phone_number) ? "(".substr($model->home_phone_number, 0, 3).") ".substr($model->home_phone_number, 3, 3)."-".substr($model->home_phone_number,6) : ''; ?></td>
										
										<td><?php echo !empty($model->mobile_phone_number) ? "(".substr($model->mobile_phone_number, 0, 3).") ".substr($model->mobile_phone_number, 3, 3)."-".substr($model->mobile_phone_number,6) : ''; ?></td>
										
										<td><?php echo !empty($model->office_phone_number) ? "(".substr($model->office_phone_number, 0, 3).") ".substr($model->office_phone_number, 3, 3)."-".substr($model->office_phone_number,6) : ''; ?></td>
										
										<td><?php echo $model->list->customer->firstname.' '.$model->list->customer->lastname; ?></td>
										
										<td><?php echo $model->list->customer->company->company_name; ?></td>
										
										<td><?php echo $skillsTxt; ?></td>
										
										<td class="center">
											<?php echo CHtml::link('Load <i class="fa fa-arrow-right"></i>', 'javascript:void(0);', array('id'=>$model->id, 'class'=>'btn btn-info btn-minier load-lead-to-hopper')); ?>
										</td>	
									</tr>
								<?php
								}
							}
							else
							{
							?>
								<tr><td colspan="2">No models found.</td></tr>
							<?php
							}
						?>
					</tbody>

				</table>
			</div>
			
			<div class="modal-footer hide"></div>
		</div>
	</div>
</div>