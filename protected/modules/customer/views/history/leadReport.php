<?php 
	$this->widget("application.components.CustomerSideMenu",array(
		'active'=> 'history',
		'customer' => $customer,
	));
?>

<div class="page-header">
	<h1 class="bigger"><?php echo $list->name; ?>: Lead Import - Review</h1>
</div>

<div class="row">
	<div class="col-sm-12">
				
		<div class="tabbable tabs-left">
			<ul id="myTab3" class="nav nav-tabs">
			
				<li id="" class="active">
					<a href="#badLead" data-toggle="tab">
						Bad Lead
					</a>
				</li>
				
				<li id="" class="">
					<a href="#duplicateLead" data-toggle="tab">
						Duplicate Lead
					</a>
				</li>
			</ul>

			<div class="tab-content office-tab-content">
			

				<div class="tab-pane fade in active" id="badLead">
					<?php 
						$this->renderPartial('_leadEntry',array(
							'list' => $list,
							'dataProvider' => $dataProviderBad,
						));
					?>
				</div>
				
				<div class="tab-pane fade in" id="duplicateLead">
					<?php 
						$this->renderPartial('_leadEntry',array(
							'list' => $list,
							'dataProvider' => $dataProviderDuplicate,
						));
					?>
				</div>

			</div>
		</div>
		
	</div>
</div>
