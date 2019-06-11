function restore(){$("#record, #live").removeClass("disabled");$(".one").addClass("disabled");Fr.voice.stop();}
$(document).ready(function(){
  $(document).on("click", "#record:not(.disabled)", function(){
    elem = $(this);
	
    Fr.voice.record($("#live").is(":checked"), function(){
      elem.addClass("hidden");
      $("#download").addClass("btn-danger").html("Stop Recording").removeClass("hidden");
	  
      $("#live").addClass("disabled");
      $(".one").removeClass("disabled");
    });
	
	
  });
  
  $(document).on("click", "#stop:not(.disabled)", function(){
    restore();
  });
  
  $(document).on("click", "#play:not(.disabled)", function(){
    Fr.voice.export(function(url){
      $("#audio").attr("src", url);
      $("#audio")[0].play();
    }, "URL");
	
    restore();
  });
  
  $(document).on("click", "#download:not(.disabled)", function(){
	  
	Fr.voice.export(function(base64){
		
		$.ajax({
			url : yii.urls.absoluteUrl + "/customer/data/voiceRecord",
			type: 'POST',
			data: {"audio" : base64, "id" : event_id},
			success: function(result) {
				$("#record").removeClass("hidden").html("Recorded").removeClass("btn-success").removeClass("btn-danger").addClass("button");
				$("#download").addClass("hidden");
				
				$("#voice-container").html("<audio controls src='"+result+"'></audio>");
			}
		});
	}, "base64");
  
	// Fr.voice.export(function(blob){
		// var data = new FormData();
		// data.append('file', blob);
	  
		// $.ajax({
			// url : yii.urls.absoluteUrl + "/customer/data/voiceRecord",
			// type: 'POST',
			// data: data,
			// contentType: false,
			// processData: false,
			// success: function(data) {
		  /*Sent to Server*/
			// }
		// });
	// }, "blob");
	
    // Fr.voice.export(function(url){
      // $("<a href='"+url+"' download='"+customer_name+".wav'></a>")[0].click();
    // }, "URL");
	
	// $("#record").removeClass("hidden").html("Record");
	$("#download").removeClass("btn-danger").addClass("btn-warning").html("Processing...");
    restore();
  });
  
  $(document).on("click", "#base64:not(.disabled)", function(){
    Fr.voice.export(function(url){
      console.log("Here is the base64 URL : " + url);
      alert("Check the web console for the URL");
      
      $("<a href='"+ url +"' target='_blank'></a>")[0].click();
    }, "base64");
    restore();
  });
  
  $(document).on("click", "#mp3:not(.disabled)", function(){
    alert("The conversion to MP3 will take some time (even 10 minutes), so please wait....");
    Fr.voice.export(function(url){
      console.log("Here is the MP3 URL : " + url);
      alert("Check the web console for the URL");
      
      $("<a href='"+ url +"' target='_blank'></a>")[0].click();
    }, "mp3");
    restore();
  });
});
