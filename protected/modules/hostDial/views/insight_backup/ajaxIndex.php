<div class="page-header">
	<h1><?php echo $customer->getFullName(); ?></h1>
</div>

<div class="text-center">

	<div class="row">
		<div class="col-sm-12 infobox-container">
		
			<div class="col-sm-4">
				<div class="infobox infobox-blue" style="border:none;">
					<div class="infobox-icon">
						<i class="ace-icon fa fa-calendar"></i>
					</div>

					<div class="infobox-data">
						<span class="infobox-data-number"><?php echo $appointmentSetCount; ?></span>
						<div class="infobox-content">Appointments Set</div>
					</div>
				</div>
			</div>
			
			<div class="col-sm-4">
				<div class="infobox infobox-green" style="border:none;">
					<div class="infobox-icon">
						<i class="ace-icon fa fa-recycle"></i>
					</div>

					<div class="infobox-data">
						<span class="infobox-data-number">0</span>
						<div class="infobox-content">Recycle Names</div>
					</div>
				</div>
			</div>
			
			<div class="col-sm-4">
				<div class="infobox infobox-red" style="border:none;">
					<div class="infobox-icon">
						<i class="ace-icon fa fa-phone"></i>
					</div>

					<div class="infobox-data">
						<span class="infobox-data-number"><?php echo $remainingCallableCount; ?></span>
						<div class="infobox-content">Remaining Callable</div>
					</div>
				</div>
			</div>

		</div>
	</div>
	
</div>

<br />
<br />

<div class="page-header">
	<h1>Action Center 
		<span class="action-center-count">
			<?php 
				echo ( $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount ) > 0 ? '<span class="red">('.( $locationConflictDataProvider->totalItemCount + $scheduleConflictDataProvider->totalItemCount ).')</span>' : ''; 
			?>
		</span>
	</h1>
</div>

<div class="accordion-style1 panel-group" id="accordion">
	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a href="#collapseOne" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="ace-icon fa fa-angle-down bigger-110"></i>
					&nbsp;
					Schedule Conflict 
					<span class="schedule-conflict-count">
						<?php echo $scheduleConflictDataProvider->totalItemCount > 0 ? '<span class="red">('.$scheduleConflictDataProvider->totalItemCount.')</span>' : ''; ?>
					</span>
				</a>
			</h4>
		</div>

		<div id="collapseOne" class="panel-collapse collapse in">
			<div class="panel-body no-padding">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'scheduleConflictList',
						'dataProvider'=>$scheduleConflictDataProvider,
						'itemView'=>'_conflict_list',
						'template'=>'<ul class="item-list">{items}</ul>',
					)); 
				?>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">
			<h4 class="panel-title">
				<a href="#collapseTwo" data-parent="#accordion" data-toggle="collapse" class="accordion-toggle collapsed">
					<i data-icon-show="ace-icon fa fa-angle-right" data-icon-hide="ace-icon fa fa-angle-down" class="ace-icon fa fa-angle-right bigger-110"></i>
					&nbsp;
					Location Conflict 
					<span class="location-conflict-count">
						<?php echo $locationConflictDataProvider->totalItemCount > 0 ? '<span class="red">('.$locationConflictDataProvider->totalItemCount.')</span>' : ''; ?>
					</span>
				</a>
			</h4>
		</div>

		<div id="collapseTwo" class="panel-collapse collapse">
			<div class="panel-body no-padding">
				<?php 
					$this->widget('zii.widgets.CListView', array(
						'id'=>'locationConflictList',
						'dataProvider'=>$locationConflictDataProvider,
						'itemView'=>'_conflict_list',
						'template'=>'<ul class="item-list">{items}</ul>',
					)); 
				?>
			</div>
		</div>
	</div>

</div>