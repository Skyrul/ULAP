<div class="row">	
	<div class="col-xs-4">
		<div class="widget-box widget-color-blue2 light-border">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">List Details - <?php echo $model->name; ?></h4>

				<div class="widget-toolbar">
					<a data-action="collapse" href="#">
						<i class="ace-icon fa fa-chevron-up"></i>
					</a>
				</div>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					
					<?php $form=$this->beginWidget('CActiveForm', array(
						'enableAjaxValidation'=>false,
						'htmlOptions' => array(
							'class' => 'form-horizontal',
						),
					)); ?>
		
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> Status </div>

							<div class="profile-info-value">
								<?php echo $form->dropDownList($model, 'status', $model::getStatusOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> # of Leads </div>

							<div class="profile-info-value">
								<span><?php echo $model->leadCount; ?></span>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Ordering Type </div>

							<div class="profile-info-value">
								<?php echo $form->dropDownList($model, 'lead_ordering', $model::getOrderingOptions(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Skill Assignment </div>

							<div class="profile-info-value">
								<select style="width:auto;">
									<option>- Select -</option>
								</select>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Calendar Assignment </div>

							<div class="profile-info-value">
								<?php echo $form->dropDownList($model, 'calendar_id', Calendar::items(), array('class'=>'form-control', 'prompt'=>'- Select -', 'style'=>'width:auto;')); ?>
							</div>
						</div>
						
						<div class="profile-info-row">
							<div class="profile-info-name"> Creation Date </div>

							<div class="profile-info-value">
								<span>
									<?php 
										if(!$model->isNewRecord)
										{
											echo date('m/d/Y', strtotime($model->date_created)); 
										}
									?>
								</span>
							</div>
						</div>
					</div>
					
					<?php $this->endWidget(); ?>
					
				</div>
			</div>
		</div>
	</div>

	<div class="col-xs-3 col-xs-offset-1">
		<div class="widget-box widget-color-blue2 light-border">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">List Performance - <?php echo $model->name; ?></h4>

				<div class="widget-toolbar">
					<a data-action="collapse" href="#">
						<i class="ace-icon fa fa-chevron-up"></i>
					</a>
				</div>
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding">
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> # of callable </div>

							<div class="profile-info-value">
								<span>0</span>
							</div>
						</div>
					</div>
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> # of appointments </div>

							<div class="profile-info-value">
								<span>0</span>
							</div>
						</div>
					</div>
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> # of wrong numbers </div>

							<div class="profile-info-value">
								<span>0</span>
							</div>
						</div>
					</div>
					
					<div class="profile-user-info profile-user-info-striped">
						<div class="profile-info-row">
							<div class="profile-info-name"> # of completed leads </div>

							<div class="profile-info-value">
								<span>0</span>
							</div>
						</div>
					</div>
					
				</div>
			</div>
		</div>
	</div>
	
</div>

<div class="hr hr-18 hr-double dotted"></div>

<div class="row">
	<div class="col-xs-12">
		<div class="widget-box widget-color-blue2 ">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter"><?php echo $model->name; ?></h4>

				<div class="widget-toolbar no-border"></div>
			</div>

			<div class="widget-body">
				<div class="widget-main padding-6 no-padding-left no-padding-right clearfix">
					<div class="col-xs-12">
						<table class="table table-striped table-bordered">
							<thead>
								<th>#</th>
								<th style="width:150px;"></th>
								<th>Home Phone</th>
								<th>Mobile Phone</th>
								<th>Office Phone</th>
								<th>First name</th>
								<th>Last Name</th>
								<th>Partner's First Name</th>
								<th>Partner's Last Name</th>
								<th>Email</th>
								<th>Custom Date</th>
								<th>Creation Date</th>
								<th># of Dials</th>
								<th>Status</th>
							</thead>
							
							<tbody>
								<tr>
									<td></td>
									
									<td class="text-center">
										<a class="btn btn-xs btn-white btn-success">
											Submit
										</a>
									</td>

									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									<td><input type="text" style="width:74px;"></td>
									
									<td><input type="text" style="width:74px;"></td>
									
									<td></td>
									
									<td>0</td>
									
									<td>
										<select>
											<option>Active</option>
											<option>Inactive</option>
											<option>Complete</option>
										</select>
									</td>
								</tr>

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>