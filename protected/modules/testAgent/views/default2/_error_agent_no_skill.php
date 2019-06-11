<div class="row">
	<div class="col-xs-12">
		<!-- PAGE CONTENT BEGINS -->

		<div class="error-container center">
			<div class="well">
				<h1 class="grey lighter smaller">
					<span class="blue bigger-125">
						<i class="ace-icon fa fa-ban"></i>
						<?php echo Yii::app()->user->name; ?>
					</span>
					has no assigned skill yet.
				</h1>

				<hr>
				
				<h3 class="lighter smaller">
					We looked everywhere but we couldn't find it!
					<i class="ace-icon fa fa-cog fa-spin bigger-125"></i>
				</h3>
	
				<div class="space-12"></div>

				<div class="center">
					
					
					<?php echo CHtml::link('<i class="ace-icon fa fa-refresh"></i>Refresh', array('/agent'), array('class'=>'btn btn-primary')); ?>
				</div>
			</div>
		</div>

		<!-- PAGE CONTENT ENDS -->
	</div><!-- /.col -->
</div>