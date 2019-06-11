<?php Yii::app()->clientScript->registerScript('contractLoginScript','
	$("#contract-login").on("click",function(){
		verificationProcess();
	});
	
	
	$("#contractPassword").on("keypress",function(e){
		if (e.keyCode == 13) {
			verificationProcess();
            return false; // prevent the button click from happening
        }
	});
	
	function verificationProcess()
	{
		contractFieldVal = $("#contractPassword").val();
		
		$.ajax({
			url: "'.Yii::app()->createUrl('/enrollment/verify').'",
			method: "GET",
			dataType: "json",
			data: {			  
			  "customerId" : contractFieldVal									  
			},
			beforeSend: function() {
				//$("#agent-id-search-container").html("Finding customer ID, please wait...");
			},
		}).success(function(response) {
			
			if(response.status == true)
			{
				location.href = yii.urls.baseUrl+"/index.php/enrollment/contract/customerId/"+response.customerData.custom_customer_id;
			}
			else if(response.status == "registered")
			{
				alert(response.errorMessage);
			}
			else
			{
				alert("Password incorrect!");
			}
		});
	}
	
',CClientScript::POS_END); ?>

<div class="">
	<div class="">
		
		<div class="login-container" id="login-container">
			<div class="page-header center">
			</div>
			
			<div class="position-relative">
				<div class="login-layout">
					<div class="login-box visible widget-box no-border" id="contract-login-box">
						<div class="widget-body">
							<div class="widget-main">
								<div class="row">
									<div class="col-md-12">
									
										<h4 class="header blue lighter bigger">
											Please Enter Agent ID
										</h4>
						
										<label class="block clearfix">
											<span class="block input-icon input-icon-right">
												<?php //echo $form->labelEx($model,'username'); ?>
												<?php echo CHtml::textField('contractPassword','', array('class'=>'form-control', 'placeholder'=>'Enter Agent ID')); ?>
												<i class="ace-icon fa fa-lock"></i>
											</span>
										</label>
										
										<div class="space"></div>

										<div class="clearfix">
											<button id="contract-login" class="width-35 pull-right btn btn-sm btn-primary">
												<i class="ace-icon fa fa-key"></i>
												<span class="bigger-110">Login</span>
											</button>
										</div>
									</div>
								</div>
							</div>
							
							<div class="toolbar clearfix">
							<br/>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>