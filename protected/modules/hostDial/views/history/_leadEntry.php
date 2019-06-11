<div class="row">
	<div class="col-xs-12">
		<div class="widget-box widget-color-blue2 ">
			<div class="widget-header widget-header-small">
				<h4 class="widget-title lighter">
					<?php //echo $list->name; ?> 
				</h4> 

				<!--
				<div class="widget-toolbar no-border">									
					<div id="nav-search" class="nav-search" style="position:inherit; margin-top:2px; right:0; ">
						<div class="form-search">
							<span class="input-icon">
								<input type="text" name="leadSearchQuery" autocomplete="off" id="lead-search-input" class="nav-search-input" placeholder="Search Leads..." style="width:200px;">
								<i class="ace-icon fa fa-search nav-search-icon"></i>
							</span>
						</div>
					</div>
				</div>
				-->
				<!--<div class="widget-toolbar no-border">
					<div class="btn-group btn-overlap btn-corner" data-toggle="buttons">
						<label class="btn btn-sm btn-white btn-info active">
							Current 
							<input type="radio" name="searchType" value="Current">											
						</label>

						<label class="btn btn-sm btn-white btn-info">
							All 
							<input type="radio" name="searchType" value="All">
						</label>
					</div>
				</div>-->
			</div>

			<div class="widget-body">
				<div class="widget-main no-padding clearfix">
					
					<form method="post" id="leadsManualEnter"></form>
					
					<?php 
					
						$leadManualEntry = '';
					
						if( $list->manually_enter == 1 )
						{
							$leadManualEntry = '
								<tr>
									
									
									<td class="center"><input type="text" name="Lead[office_phone_number]" class="col-sm-12 input-mask-phone manual-lead-input" form="leadsManualEnter"></td>
									
									<td class="center"><input type="text" name="Lead[mobile_phone_number]" class="col-sm-12 input-mask-phone manual-lead-input" form="leadsManualEnter"></td>
									
									<td class="center"><input type="text" name="Lead[home_phone_number]" class="col-sm-12 input-mask-phone manual-lead-input" form="leadsManualEnter"></td>
									
									<td class="center"><input type="text" name="Lead[first_name]" class="col-sm-12 manual-lead-input" form="leadsManualEnter"></td>
									
									<td class="center"><input type="text" name="Lead[last_name]" class="col-sm-12 manual-lead-input" form="leadsManualEnter"></td>
									
									<td class="center"></td>
									
									<td class="center"></td>
									
									<td class="center"><input type="text" name="Lead[number_of_dials]" class="col-sm-12  manual-lead-input" form="leadsManualEnter" value="0"></td>
									
									<td class="center">'.CHtml::dropDownList('Lead[status]', 1, Lead::statusOptions(), array('class'=>'col-sm-12 manual-lead-input', 'form'=>'leadsManualEnter')).'</td>
								</tr>
							';
						}
					
						$this->widget('zii.widgets.CListView', array(
							'id'=>'leadList',
							'dataProvider'=>$dataProvider,
							'itemView'=>'_lead_list',
							'summaryText' => '{start} - {end} of {count}',
							'emptyText' => '',
							'template'=>'
								<table id="leadsTbl" class="table table-bordered table-condensed table-hover">
									<thead>
										<th class="center">Office Number</th>
										<th class="center">Mobile Number</th>
										<th class="center">Phone Number</th>
										<th class="center">First Name</th>
										<th class="center">Last Name</th>
										<th class="center">List Name</th>
										<th class="center">Creation Date</th>
									</thead>
									'.$leadManualEntry.'
									{items}  
								</table> 
								<div class="col-sm-12"> 
									<div class="pager-container"> 
										<div class="col-sm-6">{summary}</div> 
										<div class="col-sm-6 text-right">{pager}</div>
									</div>
								</div>
							',
							'pagerCssClass' => 'pagination', 
							'pager' => array(
								'header' => '',
							),
						)); 
					?>
				</div>
			</div>
		</div>
	</div>
</div>