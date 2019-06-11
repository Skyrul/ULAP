$( function(){
	
	$(document).ready( function(){ 
		
		$(window).load(function()
		{
			$("#customerPopupModal").modal("show");
			
			$("#customerPopupModal .popupDelayCtr").text(delayTime);
				
			setInterval(function(){
				delayTime--;

				if( delayTime >= 0 )
				{
					$(".popupDelayCtr").text(delayTime);
				}
				
			},1000);
			
			setTimeout(function(){
				
				var button = $("#customerPopupModal").find(".modal-footer :button");
			
				button.removeClass("btn-default");
				button.addClass("btn-info");
				button.prop("disabled", false);
				button.text("Close");
				
				$(".dial-phonenumber-btn").removeClass("popup-delay-disabled");
				
			}, delayTime * 1000);
		});
	});
	
});