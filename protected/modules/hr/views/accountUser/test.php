<?php 
	$baseUrl = Yii::app()->request->baseUrl;
	
	$cs = Yii::app()->clientScript;
	$cs->registerCssFile($baseUrl.'/template_assets/css/jquery-ui.css');
?>
<?php Yii::app()->clientScript->registerScript('addJs','
	$(".add-pto-form-btn").on("click",function(){
					$.ajax({
						url: yii.urls.absoluteUrl + "/hr/accountUser/AjaxAddPtoForm",
						// url: yii.urls.absoluteUrl + "/admin/companySubsidy/AjaxAddSubsidy",
						type: "GET",	
						data: { 
							"id" : "1"			
						},
						beforeSend: function(){
						},
						complete: function(){
						},
						error: function(){
						},
						success: function(r){
						
							header = "Add Time-Off Request";
							$("#myModalMd #myModalLabel").html(header);
							$("#myModalMd .modal-body").html(r);
							$("#myModalMd").modal();
							
						},
					});
				});
				
				
',CClientScript::POS_END); ?>

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

<button type="button" class="btn btn-xs btn-primary add-pto-form-btn"><i class="fa fa-plus"></i> Add</button>