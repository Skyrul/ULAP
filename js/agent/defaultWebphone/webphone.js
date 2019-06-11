$( function(){
	
	function Call(destnr)
	{
		if (typeof (destnr) === 'undefined' || destnr === null) { destnr = ''; }
		
		destnr = '81' + destnr;
		
		webphone_api.setparameter('destination', destnr);
		webphone_api.call(destnr);
	}
	
	function Hangup()
	{
		webphone_api.hangup();
	}
	
	var mutestate = true;
	function Mute()
	{
		if (mutestate === true)
		{
			webphone_api.mute(true, 0);
			mutestate = false;
			
			$('.mute-call-btn').html('UNMUTE');
		}
		else
		{
			webphone_api.mute(false, 0);
			mutestate = true;
			
			$('.mute-call-btn').html('MUTE');
		}
	}
	
	var holdstate = true;
	function Hold()
	{
		if (holdstate === true)
		{
			webphone_api.hold(true);
			holdstate = false;
			
			$('.hold-call-btn').html('UNHOLD');
		}
		else
		{
			webphone_api.hold(false);
			holdstate = true;
			
			$('.hold-call-btn').html('HOLD');
		}
		
	}
	
	function Transfer()
	{
		var destnum = prompt('Enter destination number', '');
		
		if (destnum !== null)
		{
			webphone_api.transfer(destnum);
		}
	}
	
	function Accept()
	{
		document.getElementById('icoming_call_layout').style.display = 'none';
		webphone_api.accept();
	}
	
	function Reject()
	{
		document.getElementById('icoming_call_layout').style.display = 'none';
		webphone_api.reject();
	}
	
	$(document).ready( function(){
		
		webphone_api.onLoaded(function ()
		{
			alert(serveraddress + ' | ' + username + ' | ' + password');
			
			webphone_api.setparameter('serveraddress', serveraddress); 
			webphone_api.setparameter('username', username); 
			webphone_api.setparameter('password', password); 
			
			$('#webphone-events').html('Initializing...');
			
			webphone_api.start();
		});
		
		webphone_api.onEvents(function (evt)
		{
			$('#webphone-events').html(evt);
		});

		webphone_api.onRegistered(function ()
		{
			// display supported callfunctions
			var funcl = webphone_api.getavailablecallfunc(); // possible values: conference,transfer,numpad,mute,hold,chat
			
			if (typeof (funcl) !== 'undefined' && funcl !== null && funcl.length > 0 && funcl.indexOf('ERROR') < 0)
			{
				var flist = funcl.split(',');
				for (var i = 0; i < flist.length; i++)
				{
					if (typeof (flist[i]) !== 'undefined' && flist[i] !== null && flist[i].length > 0)
					{
						document.getElementById('btn_' + flist[i]).style.display = 'block';
					}
				}
			}
		}); 

		//status: can have following values: callSetup, callRinging, callConnected, callDisconnected
		//direction: 1 (outgoing), 2 (incoming)
		//peername: is the other party username (or phone number or extension)
		//peerdisplayname: is the other party display name if any
		//line number
		webphone_api.onCallStateChange(function (event, direction, peername, peerdisplayname, line)
		{	
			$('#webphone-state').html(event);
			
			if( event === 'callSetup' )
			{
				$('.end-call-btn').removeClass('disabled');
				$('.conference-call-btn').removeClass('disabled');
				$('.transfer-call-btn').removeClass('disabled');
				$('.mute-call-btn').removeClass('disabled');
				$('.hold-call-btn').removeClass('disabled');
			}
			
			if( event === 'callConnected' )
			{
				
			}
			
			//detecting the end of a call, even if it wasn't successfull
			if( event === 'callDisconnected' )
			{
				$('.end-call-btn').addClass('disabled');
				$('.conference-call-btn').addClass('disabled');
				$('.transfer-call-btn').addClass('disabled');
				$('.mute-call-btn').addClass('disabled');
				$('.hold-call-btn').addClass('disabled');
			
				// reset to default state, after call ends
				mutestate = true;
				$('.mute-call-btn').html('MUTE');
				
				holdstate = true;
				$('.hold-call-btn').html('HOLD');
			}
		});
		
	});
	
});