<?php Yii::app()->clientScript->registerScriptFile(Yii::app()->request->baseUrl.'/js/setup_admin_tiers.js',CClientScript::POS_END); ?>
<?php Yii::app()->clientScript->registerCssFile(Yii::app()->request->baseUrl.'/css/tier.css'); ?>

<?php 
	Yii::app()->clientScript->registerScript('did-action-buttons','
		didAjaxSending = false;
		
		$(".btn-add-did").on("click",function(){
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/company/ajaxAddDid/",
				type: "GET",	
				data: { 
					"id" : "'.$model->id.'"			
				},
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					header = "Add DID";
					$("#myModalMd #myModalLabel").html(header);
					$("#myModalMd .modal-body").html(r);
					$("#myModalMd").modal();
					
				},
			});
		});
		
		
		//edit tier
		$("body").on("click", ".btn-edit-did", function(e){
			var thisUrl = $(this).prop("href");
			e.preventDefault();
			
			if(!didAjaxSending)
			{
				didAjaxSending = true;
				
				$.ajax({
					url: thisUrl,
					type: "post",
					beforeSend: function(){
					},
					complete: function(){
					},
					error: function(){
					},
					success: function(r){
						header = "Edit DID";
						$("#myModalMd #myModalLabel").html(header);
						$("#myModalMd .modal-body").html(r);
						$("#myModalMd").modal();
						
						didAjaxSending = false;
					},
				});
			}
		});
		
		$("body").on("click", ".btn-remove-did", function(e){
			e.preventDefault();
			if(confirm("Remove selected DID?"))
			{
				var thisUrl = $(this).prop("href");
				e.preventDefault();
				
				if(!didAjaxSending)
				{
					didAjaxSending = true;
					
					$.ajax({
						url: thisUrl,
						type: "post",
						beforeSend: function(){
						},
						complete: function(){
						},
						error: function(){
						},
						success: function(r){
							didList();
							didAjaxSending = false;
						},
					});
				}
			}
		});
		
		function didList()
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/company/didList/id/'.$model->id.'",
				type: "post",
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					$("#did-container").html(r);
				},
			});
				
			
		}
	',CClientScript::POS_END); 
?>

<?php 
	Yii::app()->clientScript->registerScript('subsidy-action-buttons','
		subsidyAjaxSending = false;
		
		$(".btn-add-subsidy").on("click",function(){
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/companySubsidy/ajaxAddSubsidy",
				type: "GET",	
				data: { 
					"company_id" : "'.$model->id.'"			
				},
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					header = "Add Subsidy";
					$("#myModalMd #myModalLabel").html(header);
					$("#myModalMd .modal-body").html(r);
					$("#myModalMd").modal();
					
				},
			});
		});
		
		$("body").on("click", ".btn-edit-subsidy", function(e){
			var thisUrl = $(this).prop("href");
			e.preventDefault();
			
			if(!subsidyAjaxSending)
			{
				subsidyAjaxSending = true;
				
				$.ajax({
					url: thisUrl,
					type: "post",
					beforeSend: function(){
					},
					complete: function(){
					},
					error: function(){
					},
					success: function(r){
						header = "Edit Subsidy";
						$("#myModalMd #myModalLabel").html(header);
						$("#myModalMd .modal-body").html(r);
						$("#myModalMd").modal();
						
						subsidyAjaxSending = false;
					},
				});
			}
		});
		
		$("body").on("click", ".btn-remove-subsidy", function(e){
			e.preventDefault();
			if(confirm("Remove selected Subsidy?"))
			{
				var thisUrl = $(this).prop("href");
				e.preventDefault();
				
				if(!subsidyAjaxSending)
				{
					subsidyAjaxSending = true;
					
					$.ajax({
						url: thisUrl,
						type: "post",
						beforeSend: function(){
						},
						complete: function(){
						},
						error: function(){
						},
						success: function(r){
							subsidyList();
							subsidyAjaxSending = false;
						},
					});
				}
			}
		});
		
		function subsidyList()
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/companySubsidy/subsidyList/company_id/'.$model->id.'",
				type: "post",
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					$("#subsidy-container").html(r);
				},
			});
				
			
		}
	',CClientScript::POS_END); 
?>


<?php 
	Yii::app()->clientScript->registerScript('tier-subsidy-action-buttons','
		tierSubsidyAjaxSending = false;
		
		$("body").on("click", ".btn-add-tier-subsidy",function(){
			var tierId = $(this).data("tier-id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/companyTierSubsidy/ajaxAddSubsidy",
				type: "GET",	
				data: { 
					"company_id" : "'.$model->id.'",	
					"tier_id" : tierId,	
				},
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					header = "Add Tier Subsidy";
					$("#myModalMd #myModalLabel").html(header);
					$("#myModalMd .modal-body").html(r);
					$("#myModalMd").modal();
					
				},
			});
		});
		
		$("body").on("click", ".btn-edit-tier-subsidy", function(e){
			var thisUrl = $(this).prop("href");
			e.preventDefault();
			
			if(!tierSubsidyAjaxSending)
			{
				tierSubsidyAjaxSending = true;
				
				$.ajax({
					url: thisUrl,
					type: "post",
					beforeSend: function(){
					},
					complete: function(){
					},
					error: function(){
					},
					success: function(r){
						header = "Edit Tier Subsidy";
						$("#myModalMd #myModalLabel").html(header);
						$("#myModalMd .modal-body").html(r);
						
						$("#myModalMd").modal();
						
						tierSubsidyAjaxSending = false;
					},
				});
			}
		});
		
		$("body").on("click", ".btn-remove-tier-subsidy", function(e){
			e.preventDefault();
			
			if(confirm("Remove selected Tier Subsidy?"))
			{
				var thisUrl = $(this).prop("href");
				e.preventDefault();
				
				if(!tierSubsidyAjaxSending)
				{
					tierSubsidyAjaxSending = true;
					
					$.ajax({
						url: thisUrl,
						type: "post",
						beforeSend: function(){
						},
						complete: function(){
						},
						error: function(){
						},
						success: function(r){
							tierSubsidyList();
							tierSubsidyAjaxSending = false;
						},
					});
				}
			}
		});
		
		function tierSubsidyList()
		{
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/companyTierSubsidy/subsidyList",
				type: "GET",
				data: { 
					"company_id" : "'.$model->id.'"			
				},
				beforeSend: function(){
				},
				complete: function(){
				},
				error: function(){
				},
				success: function(r){
					$("#tier-subsidy-container").html(r);
				},
			});
		}
		
	',CClientScript::POS_END); 
?>

<?php 
Yii::app()->clientScript->registerScript(uniqid().'company-subsidy-level','

	var ctr = 0;
	
	$("body").on("click", ".btn-add-subsidy-level", function(){
		
		var containerObj = $(this).parent().parent().find(".subsidyLevel-container");
		$.ajax({
			url: "'.Yii::app()->createUrl('/admin/companySubsidy/addNewSubsidyLevel').'",
			method: "GET",
			data: {				  
			  "ctr" : ctr,					  
			}
		}).success(function(response) {
			containerObj.append(response);
			
			ctr++;
		});
			
	});
	
	$("body").on("click",".btn-remove-subsidy-level",function(){
		var containerObj = $(this).parent().parent().parent().parent().remove();
	});
',CClientScript::POS_END);

?>

<?php 
Yii::app()->clientScript->registerScript(uniqid().'company-tier-subsidy-level','

	var ctr = 0;
	
	$("body").on("click", ".btn-add-tier-subsidy-level", function(){
		
		var containerObj = $(this).parent().parent().find(".tierSubsidyLevel-container");
		$.ajax({
			url: "'.Yii::app()->createUrl('/admin/companyTierSubsidy/addNewSubsidyLevel').'",
			method: "GET",
			data: {				  
			  "ctr" : ctr,					  
			}
		}).success(function(response) {
			containerObj.append(response);
			
			ctr++;
		});
			
	});
	
	$("body").on("click",".btn-remove-tier-subsidy-level",function(){
		var containerObj = $(this).parent().parent().parent().parent().remove();
	});
',CClientScript::POS_END);

?>

<?php
	Yii::app()->clientScript->registerScript('tababbleScript','
	
		$("#myTab3 a").click(function (e) {
			
			if(!($(this).parent().hasClass("link")))
			{
				e.preventDefault()
				$(this).tab("show");
			}
		});
	', CClientScript::POS_END);
 ?>

<?php 
	Yii::app()->clientScript->registerScript('company-subsidy-form-populate-contract-dropdown','
		$("body").on("change", "#CompanySubsidy_skill_id", function(){
			
			thisVal = $(this).val();
			
			$.ajax({
				url: "'.Yii::app()->createUrl('/customer/customerSkill/getContractByCompanyAndSkill').'",
				method: "GET",
				dataType: "json",
				data: {
				  "company_id" : "'.$model->id.'",					  
				  "skill_id" : thisVal,					  
				}
			}).success(function(response) {
				
				
				var options = $("#CompanySubsidy_contract_id");
				options.empty();
				options.append($("<option />").val("").text("-Select Contract-"));
				$.each(response, function() {
					options.append($("<option />").val(this.id).text(this.contract_name));
				});
			});
		});
	',CClientScript::POS_END);
 ?>
 
 
 <?php 
	Yii::app()->clientScript->registerScript('company-tier-subsidy-form-populate-contract-dropdown','
		$("body").on("change", "#TierSubsidy_skill_id", function(){
			
			thisVal = $(this).val();
			
			$.ajax({
				url: "'.Yii::app()->createUrl('/customer/customerSkill/getContractByCompanyAndSkill').'",
				method: "GET",
				dataType: "json",
				data: {
				  "company_id" : "'.$model->id.'",					  
				  "skill_id" : thisVal,					  
				}
			}).success(function(response) {
				
				
				var options = $("#TierSubsidy_contract_id");
				options.empty();
				options.append($("<option />").val("").text("-Select Contract-"));
				$.each(response, function() {
					options.append($("<option />").val(this.id).text(this.contract_name));
				});
			});
		});
	',CClientScript::POS_END);
 ?>
 
<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>


<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModalMd" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
  <div class="modal-dialog modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<div class="page-header">
	<h1><?php echo $model->company_name; ?></h1>
</div>

<div class="tabbable tabs-left">
	<ul id="myTab3" class="nav nav-tabs">
		
		<li data-toggle="tab" class="active">		
			<a href="#company-setup">Setup</a>
		</li>
		
		<li data-toggle="tab">		
			<a href="#company-tiers">Tiers</a>
		</li>
		
		<!--<li data-toggle="tab">		
			<a href="#company-did">DID's</a>
		</li>-->
		<li data-toggle="tab">		
			<a href="#company-list">Subsidy List</a>
		</li>
		<li data-toggle="tab">		
			<a href="#portal-access">Portal Access</a>
		</li>
		<li data-toggle="tab">		
			<a href="#company-file">Company Files</a>
		</li>
		<li data-toggle="tab">		
			<a href="#customer-id-file">Customer ID Files</a>
		</li>
		<li data-toggle="tab">		
			<a href="#file-history">File History</a>
		</li>
		
		<li data-toggle="tab">		
			<a href="#learning-center">Resource Center Manager</a>
		</li>
		
		<li data-toggle="tab">		
			<a href="#permission">Customer Portal Permissions</a>
		</li>
	</ul>


	<div class="tab-content">		
		<div role="tabpanel" class="tab-pane active" id="company-setup">
			<?php $this->renderPartial('_form', array('model'=>$model)); ?>
		</div>

		
		<div role="tabpanel" class="tab-pane" id="company-tiers">
			<div class="page-header">
				<h1>Company Tiers</h1>
			</div>
					
					<?php echo CHtml::link('Add Tier','javascript:void(0);',array(
						'class' => 'btn btn-minier add-new-tier',
						'tier_ParentTier_Id' => null,
						'tier_ParentSubTier_Id' => null,
						'tier_Company_Id' => $model->id,
						'tier_Level' => 1,
						'tier_Name' => null,
					)); ?>				
					<ul id="tier-tree-<?php echo $model->id; ?>" class="tree tree-selectable" role="tree">
						<?php 
							if($model->parentTiers())
							{
								foreach($model->parentTiers() as $tier)
								{
									$this->renderPartial('/tier/_treeBranch',array(
										'tier' => $tier
									));
								}
							}
							else
							{
								echo 'No tiers found.';
							}
						?>
					</ul>
		</div>
		
		<?php /*<div role="tabpanel" class="tab-pane" id="company-did">
			<div class="row">
				<div class="col-md-12">
					<div class="page-header">
						<h1>DID's <?php echo CHtml::button('Add DID',array('class'=>'btn btn-sm btn-primary btn-add-did')); ?></h1>
					</div>
					
					<div id="did-container">
						<?php $this->forward('/admin/company/didList/id/'.$model->id,false); ?>
					</div>
				</div>
			</div>
		</div>*/ ?>
		
		<div role="tabpanel" class="tab-pane" id="company-list">
			<div class="row">
				<div class="col-md-12">
					<div class="page-header">
						<h1>Subsidy List <?php echo CHtml::button('Add Subsidy',array('class'=>'btn btn-sm btn-primary btn-add-subsidy')); ?></h1>
					</div>
					
					<div id="subsidy-container">
						<?php $this->forward('/admin/companySubsidy/subsidyList/company_id/'.$model->id,false); ?>
					</div>
				</div>
			</div>
		</div>
	
		<div role="tabpanel" class="tab-pane" id="portal-access">
			<div class="page-header">
				<h1>Portal Access</h1>
				
				<div class="row">
					<div class="col-md-6">
					<?php $this->renderPartial('_portalAccess', array('model'=>$model)); ?>
					</div>
				</div>
			
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane" id="company-file">
			<div class="row">
				<div class="col-md-12">
					<div id="company-file-container">
						<?php $this->forward('/admin/companyFile/index/company_id/'.$model->id.'/forward/1',false); ?>
					</div>
				</div>
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane" id="customer-id-file">
			<div class="row">
				<div class="col-md-12">
					<div id="customer-id-file-container">
						<?php $this->forward('/admin/customerFile/index/company_id/'.$model->id.'/forward/1',false); ?>
					</div>
				</div>
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane" id="file-history">
			<div class="row">
				<div class="col-md-12">
					<div id="file-history-container">
						<?php $this->forward('/admin/history/index/company_id/'.$model->id.'/forward/1',false); ?>
					</div>
				</div>
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane" id="learning-center">
			<div class="row">
				<div class="col-md-12">
					<div id="file-learning-center-container">
						<?php 
							if( isset($_GET['addFile']) )
							{
								$this->forward('/admin/learningCenter/create/company_id/'.$model->id.'/forward/1',false); 
							}
							else
							{
								$this->forward('/admin/learningCenter/index/company_id/'.$model->id.'/forward/1',false); 
							}
						?>
					</div>
				</div>
			</div>
		</div>
		
		<div role="tabpanel" class="tab-pane" id="permission">
			<div class="row">
				<div class="col-md-12">
					<div id="file-history-container">
						<?php $this->forward('/admin/permission/index/company_id/'.$model->id.'/forward/1',false); ?>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>