<?php /* <ul class="nav nav-tabs" role="tablist"> */ ?>
	<li class="active">
		<a href="<?php echo Yii::app()->createUrl('hostDial/leads/index',array('customer_id'=>$customer_id)); ?>">
			<?php echo "List"; ?>
		</a>
	</li>
	
	<li>
		<a href="#type" role="tab" data-toggle="tab">
			<?php echo "Type"; ?>
		</a>
	</li>
	
	<li>
		<a href="#month" role="tab" data-toggle="tab">
			<?php echo "Month"; ?>
		</a>
	</li>
	
	<li>
		<a href="#status" role="tab" data-toggle="tab">
			<?php echo "Status"; ?>
		</a>
	</li>
	
	<li>
		<a href="#lead" role="tab" data-toggle="tab">
			<?php echo "Lead"; ?>
		</a>
	</li>
	
	<li>
		<a href="#add-new" role="tab" data-toggle="tab">
			<?php echo "Add New"; ?>
		</a>
	</li>
<?php /*</ul>*/  ?>