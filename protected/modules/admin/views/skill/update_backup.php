<?php 
	$cs = Yii::app()->clientScript;
	$baseUrl = Yii::app()->request->baseUrl;
?>

<?php $cs->registerScriptFile($baseUrl.'/js/select2.min.js'); ?>
<?php $cs->registerCssFile($baseUrl.'/css/select2.min.css'); ?>

<?php 
	$cs->registerScript(uniqid(), '
		
		$(document).on("click", ".btn-skill-history", function(){
			
			skill_id = $(this).attr("skill_id");
			
			$.ajax({
				url: yii.urls.absoluteUrl + "/admin/skill/history",
				type: "post",
				dataType: "json",
				data: { "ajax":1, "skill_id":skill_id },
				success: function(response) {

					if(response.html  != "" )
					{
						modal = response.html;
					}
										
					var modal = $(modal).appendTo("body");

					modal.modal("show").on("hidden.bs.modal", function(){
						modal.remove();
					});
				}
			});
			
		});
	
	', CClientScript::POS_END);
?>

<?php 
	$this->widget("application.components.AdminSideMenu",array(
		'active'=> Yii::app()->controller->id
	));
?>

<div class="page-header">
	<button class="btn btn-yellow btn-sm pull-right btn-skill-history" skill_id="<?php echo $model->id; ?>"><i class="fa fa-history"></i> History</button>
	<h1>Update <small>&raquo; <?php echo $model->skill_name; ?></small></h1>
</div>

<?php $this->renderPartial('_form', array(
	'model'=>$model,
	'skillAccountsArray'=>$skillAccountsArray,
	'selectedSkillCompany'=>$selectedSkillCompany,
	'selectedServiceTab'=>$selectedServiceTab,
)); ?>