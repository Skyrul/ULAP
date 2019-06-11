<style>
/* ==== Google font ==== */
@import url('https://fonts.googleapis.com/css?family=Lato:400,300,700,900');

body {
    background: #394864;
    font-family: 'Lato', sans-serif;
    font-weight: 300;
    font-size: 16px;
    color: #555;
    line-height: 1.6em;
    -webkit-font-smoothing: antialiased;
    -webkit-overflow-scrolling: touch;
}

h1, h2, h3, h4, h5, h6 {
    font-family: 'Lato', sans-serif;
    font-weight: 300;
    color: #444;
}

h1 {
	font-size: 40px;
}

h3 {
	font-weight: 400;
}

h4 {
	font-weight: 400;
	font-size: 20px;
}

p {
    margin-bottom: 20px;
    font-size: 16px;
}


a {
    color: #ACBAC1;
    word-wrap: break-word;
    -webkit-transition: color 0.1s ease-in, background 0.1s ease-in;
    -moz-transition: color 0.1s ease-in, background 0.1s ease-in;
    -ms-transition: color 0.1s ease-in, background 0.1s ease-in;
    -o-transition: color 0.1s ease-in, background 0.1s ease-in;
    transition: color 0.1s ease-in, background 0.1s ease-in;
}

a:hover,
a:focus {
    color: #4F92AF;
    text-decoration: none;
    outline: 0;
}

a:before,
a:after {
    -webkit-transition: color 0.1s ease-in, background 0.1s ease-in;
    -moz-transition: color 0.1s ease-in, background 0.1s ease-in;
    -ms-transition: color 0.1s ease-in, background 0.1s ease-in;
    -o-transition: color 0.1s ease-in, background 0.1s ease-in;
    transition: color 0.1s ease-in, background 0.1s ease-in;
}

.alignleft {
    text-align: left;
}
.alignright {
    text-align: right;
}

.aligncenter {
    text-align: center;
}

.btn {
  display: inline-block;
  padding: 10px 20px;
  margin-bottom: 0;
  font-size: 14px;
  font-weight: normal;
  line-height: 1.428571429;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
       -o-user-select: none;
          user-select: none;
  background-image: none;
  border: 1px solid transparent;
  border-radius: 0;
}

.btn-theme  {
  color: #fff;
  background-color: #4F92AF;
  border-color: #4F92AF;
}
.btn-theme:hover  {
  color: #fff;
  background-color: #444;
  border-color: #444;
}
form.signup input  {
	height: 42px;
	width: 200px;
	border-radius: 0;
	border: none;
}
form.signup button.btn {
	font-weight: 700;
}
form.signup input.form-control:focus {
	border-color: #fd680e;
}


/* wrapper */

#wrapper {
	text-align: center;
	padding: 50px 0;
	background: url(../img/main-bg.jpg) no-repeat center top;
	background-attachment: relative;
	background-position: center center;
	min-height: 650px;
	width: 100%;	
    -webkit-background-size: 100%;
    -moz-background-size: 100%;
    -o-background-size: 100%;
    background-size: 100%;

    -webkit-background-size: cover;
    -moz-background-size: cover;
    -o-background-size: cover;
    background-size: cover;
}



#wrapper h1 {
	margin-top: 60px;
	margin-bottom: 40px;
	color: #fff;
	font-size: 45px;
	font-weight: 900;
	letter-spacing: -1px;
}

h2.subtitle {
	color: #fff;
	font-size: 24px;
}

/* countdown */
#countdown {
	font-size: 48px;
	color: #fff;
	line-height: 1.1em;
	margin: 40px 0 60px;
}


/* footer */
p.copyright {
	margin-top: 50px;
	color: #fff;
	text-align: center;
}

.page-content{ background:#394864 !important; }	
</style>

<script>

	$(function() {
    
		$(document).ready( function(){ 
	
			var hours = minutes = seconds = milliseconds = 0;
			var prev_hours = prev_minutes = prev_seconds = prev_milliseconds = undefined;
			var timeUpdate;
			
			// Start/Pause/Resume button onClick
			$("#start_pause_resume").button().click(function(){
				// Start button
				if($(this).text() == "Start"){  // check button label
					$(this).html("<span class='ui-button-text'>Pause</span>");
					updateTime(0,0,0,0);
				}
				// Pause button
				else if($(this).text() == "Pause"){
					clearInterval(timeUpdate);
					$(this).html("<span class='ui-button-text'>Resume</span>");
				}
				// Resume button		
				else if($(this).text() == "Resume"){
					prev_hours = parseInt($("#hours").html());
					prev_minutes = parseInt($("#minutes").html());
					prev_seconds = parseInt($("#seconds").html());
					prev_milliseconds = parseInt($("#milliseconds").html());
					
					updateTime(prev_hours, prev_minutes, prev_seconds, prev_milliseconds);
					
					$(this).html("<span class='ui-button-text'>Pause</span>");
				}
			});
			
			// Reset button onClick
			$("#reset").button().click(function(){
				if(timeUpdate) clearInterval(timeUpdate);
				setStopwatch(0,0,0,0);
				$("#start_pause_resume").html("<span class='ui-button-text'>Start</span>");      
			});
			
			// Update time in stopwatch periodically - every 25ms
			function updateTime(prev_hours, prev_minutes, prev_seconds, prev_milliseconds){
				var startTime = new Date();    // fetch current time
				// var startTime = new Date('<?php echo date("D M d Y H:i:s O", strtotime($currentLoginState->start_time)); ?>');    // fetch current time
				
				timeUpdate = setInterval(function () {
					var timeElapsed = new Date().getTime() - startTime.getTime();    // calculate the time elapsed in milliseconds
					
					// calculate hours                
					hours = parseInt(timeElapsed / 1000 / 60 / 60) + prev_hours;
					
					// calculate minutes
					minutes = parseInt(timeElapsed / 1000 / 60) + prev_minutes;
					if (minutes > 60) minutes %= 60;
					
					// calculate seconds
					seconds = parseInt(timeElapsed / 1000) + prev_seconds;  
					if (seconds > 60) seconds %= 60;
					
					// calculate milliseconds 
					milliseconds = timeElapsed + prev_milliseconds;
					if (milliseconds > 1000) milliseconds %= 1000;
					
					// set the stopwatch
					setStopwatch(hours, minutes, seconds, milliseconds);
					
				}, 25); // update time in stopwatch after every 25ms
				
			}
			
			// Set the time in stopwatch
			function setStopwatch(hours, minutes, seconds, milliseconds){
				
				$("#hours").html(prependZero(hours, 2));
				$("#minutes").html(prependZero(minutes, 2));
				$("#seconds").html(prependZero(seconds, 2));
				$("#milliseconds").html(prependZero(milliseconds, 3));
			}
			
			// Prepend zeros to the digits in stopwatch
			function prependZero(time, length) {
				time = new String(time);    // stringify time
				return new Array(Math.max(length - time.length + 1, 0)).join("0") + time;
			}
			
			
			var ajaxReloginSending = false
			
			$(document).on("click", ".timer-login-submit-btn", function(){

				var this_button = $(this);
				var password = $(".timer-login-password-txt").val();
				
				if( !ajaxReloginSending )
				{					
					ajaxReloginSending = true;
				
					$.ajax({
						
						url: yii.urls.absoluteUrl + "/agent/idle/ajaxRelogin",
						type: "post",
						dataType: "json",
						data:{ "ajax":1, "password":password },
						complete: function(){ ajaxReloginSending = false; },
						beforeSend: function(){ 
							this_button.text("Logging in, please wait...");
							this_button.removeClass("btn-info");
							this_button.addClass("btn-grey");
						},
						success: function(response){
							
							if( response.status == 'success' )
							{
								if( response.is_host_dialer == 1 )
								{
									$(location).attr("href", yii.urls.absoluteUrl + "/agent/webphone");
								}
								else
								{
									$(location).attr("href", yii.urls.absoluteUrl + "/agent");
								}
							}
							else
							{
								if( response.message != "" )
								{
									alert(response.message);
									this_button.text("Login");
									this_button.addClass("btn-info");
									this_button.removeClass("btn-grey");
								}
							}
						},
					});
				}
				
			});

			$(document).on("keypress", ".timer-login-password-txt", function (e) {
				
				var key = e.which;
				
				if(key == 13)  // the enter key code
				{
					$(".timer-login-submit-btn").click();
					return false;  
				}
			});   
			
			
			$("#start_pause_resume").click();
		
		});
	});

</script>

<div id="wrapper" style="background:#394864">
        <div class="container">
			<div class="row">
				<div class="col-sm-12 col-md-12 col-lg-12">
					<h1>
						<?php 
							if( isset($authAccount->accountUser) )
							{
								echo $authAccount->accountUser->getFullName(); 
							}
							elseif( isset($authAccount->customerOfficeStaff) )
							{
								echo $authAccount->customerOfficeStaff->staff_name; 
							}
							else
							{
								echo $authAccount->username; 
							}
						?>
					</h1>
					
					<h1><?php echo $currentLoginState->getType(); ?></h1>
					
					<h2 class="subtitle">
						<?php 
							if( $currentLoginState->getType() == 'Lunch' )
							{
								echo 'You are logged out for time keeping purposes during this state.<br>';
							}
						?>
						Enter your passsword to go back in the dialer page.
					</h2>
					
					<div id="countdown">
						<span id="hours">00</span> :
						<span id="minutes">00</span> :
						<span id="seconds">00</span>
						<span id="milliseconds" class="hide">000</span>
					</div>
					
					<div id="controls" class="hide">
						<button id="start_pause_resume">Start</button>
						<button id="reset">Reset</button>
					</div>

					<form class="form-inline signup" role="form">
						<div class="form-group">
							<input type="password" class="form-control timer-login-password-txt" placeholder="Enter Password">
						</div>
						<button type="button" class="btn btn-info timer-login-submit-btn">Login</button>
					</form>		
					
				</div>
				
			</div>		
		</div>
	</div>


