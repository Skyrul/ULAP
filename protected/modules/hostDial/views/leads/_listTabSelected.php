
<div class="hostDial-subheader">

	<div class="row">
	
		<div class="col-sm-9">
			<h3>List - <?php echo $listModel->name; ?></h3>
		</div>
		
		<div class="col-sm-3" align="right">
		
			<a href="javascript:void(0);" class="hostDial-list-action upload-script" id="<?php echo $listModel->id; ?>" title="Attach Script"><span style="font-size:20px;margin:0px 10px;"><i class="menu-icon fa fa fa-file-text"></i></span></a>
			
			<a href="javascript:void(0);" class="hostDial-list-action list-agent-assignment" id="<?php echo $listModel->id; ?>" title="Agent Assignment"><span style="font-size:20px;margin:0px 10px;"><i class="menu-icon fa fa-group"></i></span></a>
			
			<a href="javascript:void(0);" class="hostDial-list-action update-list-btn" id="<?php echo $listModel->id; ?>" title="List Settings"><span style="font-size:20px;margin:0px 10px;"><i class="menu-icon fa fa-gear"></i></span></a>
			
			
		</div>
		
	</div>
	
</div>

<br>

<?php 
								
$this->widget('zii.widgets.CListView', array(
	'id'=>'leadList',
	'dataProvider'=>$listDataProvider,
	'itemView'=>'_lead_list',
	'summaryText' => '{start} - {end} of {count}',
	'emptyText' => '',
	'template'=>'
		<table id="leadsTbl" class="table table-striped">
			<thead>
				<th class="center">Member Number</th>
				<th class="center">First Name</th>
				<th class="center">Last Name</th>
				<th class="center">Office Number</th>
				<th class="center">Mobile Number</th>
				<th class="center">Home Phone</th>
				<th class="center">Status</th>
				<th class="center">Actions</th>
			</thead>
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

<br style="clear:both;">
						
<?php /*
<div class="tabpanel-table">
	<table style="width:1020px;">
		<thead>
			<tr>
				<th>Member Number</th>
				<th>First Name</th>
				<th>Last Name</th>
				<th>Office Phone</th>
				<th>Mobile Phone</th>
				<th>Home Phone</th>
				<th>Status</th>
				
				<th>Actions</th>
			</tr>
		</thead>
		<?php 

		if(!empty($listLeadModel))
		{ 
			foreach($listLeadModel as $lead)
			{
				
				?>

					<tr>
						<td><?php echo $lead->_memberNumber; ?></td>
						<td><?php echo $lead->first_name; ?></td>
						<td><?php echo $lead->last_name; ?></td>
						<td><?php echo $lead->office_phone_number; ?></td>
						<td><?php echo $lead->mobile_phone_number; ?></td>
						<td><?php echo $lead->home_phone_number; ?></td>
						<td><?php echo $lead->getStatus(); ?></td>
						
						<td>
							<?php echo CHtml::link('Status',array('status','id'=>$lead->id),array('class'=>'btn btn-minier btn-info lead-status'));  ?> 
							<?php echo CHtml::link('Data Tab',array('dataTab','id'=>$lead->id),array('class'=>'btn btn-minier btn-info'));  ?> 
							<?php echo CHtml::link('View History',array('history','id'=>$lead->id),array('class'=>'btn btn-minier btn-info'));  ?> 
						</td>
							
					</tr>
				<?php 
			} 
		}
		else
		{
			?>
			<tr>
				<td colspan="9">No lead(s) found in the list.</td>
			</tr>
		<?php } ?>
	</table>

</div> */ ?>