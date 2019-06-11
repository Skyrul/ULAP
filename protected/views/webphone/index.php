<html>
<head>
    <meta charset="utf-8" />
    <title>Webphone Basic Example</title>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/webphone/webphone_api.js?jscodeversion=133"></script>
    <style>input { display: inline-block; width: 13em; }</style>
</head>
<body style="margin: 0; font-family: Tahoma, Arial ; font-size: 14px; color:#2e3d47;">
    <div style="font-family: Verdana; font-size: 16px; color:#4eaaec; text-align:center; margin-top:10px">Basic example</div><br><br>
    <div><span style="font-family: Tahoma, Arial ; font-size: 14px; color:#2e3d47;">This is the simplest example to demonstrate the webphone usage without any error handling or state management.<br />	
	Don't use this in production as it is not a complete implementation.<br />	
        See the source of "basic_example.html" from the demo <a href="https://www.mizu-voip.com/Portals/0/Files/webphone.zip" target="_blank">webphone package</a> about how it is done.</span><br />		
    </div><br /><br />
    <div style="width: 100%; text-align: center;">
        <div style="display: inline-block; text-align: left">
            <input type="text" placeholder="VoIP Server address" id="serveraddress" autocapitalize="off"><br />
            <input type="text" placeholder="Username" id="username" autocapitalize="off"><br />
            <input type="text" placeholder="Password" id="password" autocapitalize="off"><br />
            <button onclick="Start();">Start</button><br /><br />
            <input type="text" placeholder="Destination number" id="destnumber" autocapitalize="off"><br />
            <button id="btn_call" onclick="Call();">Call</button>
            <button id="btn_hangup" onclick="Hangup();">Hangup</button><br /><br />
            
            <div id="icoming_call_layout">
                ----------------------<br />
                <button id="btn_accept" onclick="Accept();">Accept</button>
                <button id="btn_reject" onclick="Reject();">Reject</button><br />
                ----------------------<br /><br />
            </div>
            <iframe allow="microphone; camera" style="display:none" height="0" width="0" id="loader"></iframe>
            <div id="events" style="width: 13em;"></div>
        </div>
        <div id="video_container" style="display: none;"></div>
    <script>
        var serveraddress_input = document.getElementById('serveraddress');
        var username_input = document.getElementById('username');
        var password_input = document.getElementById('password');
        var destination_input = document.getElementById('destnumber');
        
        document.getElementById('btn_hangup').disabled = true;
        document.getElementById('icoming_call_layout').style.display = 'none';

        // Wait until the webphone is loaded, before calling any API functions
        // if automatic start is required, then webphone_api.start() should be called "onLoaded" event like this:
        //      webphone_api.onLoaded(function ()
        //      {
        //          webphone_api.start();
        //      });
        webphone_api.parameters['autostart'] = false;

        webphone_api.onLoaded(function ()
        {
            var serveraddress = webphone_api.getparameter('serveraddress');
            if (serveraddress.length < 1) { serveraddress = webphone_api.getparameter('serveraddress_user'); } // only for demo
            var username = webphone_api.getparameter('username');
            var password = webphone_api.getparameter('password');
            var destination = webphone_api.getparameter('destination');

            if (serveraddress.length > 0) { serveraddress_input.value = serveraddress; }
            if (username.length > 0) { username_input.value = username; }
            if (password.length > 0) { password_input.value = password; }
            if (destination.length > 0) { destination_input.value = destination; }
        });

        function Start()
        {
            var serveraddress = serveraddress_input.value;
            var username = username_input.value;
            var password = password_input.value;
            var destination = destination_input.value;
            
            if (typeof (serveraddress) === 'undefined' || serveraddress === null || serveraddress.length < 1) { alert('Set a valid serveaddress.'); serveraddress_input.focus(); return; }
            if (typeof (username) === 'undefined' || username === null || username.length < 1) { alert('Set a valid username.'); username_input.focus(); return; }
            if (typeof (password) === 'undefined' || password === null || password.length < 1) { alert('Set a valid password.'); password_input.focus(); return; }
            
            if (typeof (serveraddress) !== 'undefined' && serveraddress !== null && serveraddress.length > 0)
            {
                webphone_api.setparameter('serveraddress', serveraddress);
            }
            webphone_api.setparameter('username', username);
            webphone_api.setparameter('password', password);
            webphone_api.setparameter('destination', destination);
            
            document.getElementById('events').innerHTML = 'EVENT, Initializing...';
            
            webphone_api.start();
        }
        
        function Call()
        {
            var destnr = document.getElementById('destnumber').value;
            document.getElementById('btn_hangup').disabled = false;
            if (typeof (destnr) === 'undefined' || destnr === null) { destnr = ''; }
            
            webphone_api.setparameter('destination', destnr);
            webphone_api.call(destnr);
        }
        
        function Hangup()
        {
            webphone_api.hangup();
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
        
        webphone_api.onEvents(function (evt)
        {
            if (evt) { evt = evt.toString(); if (evt.indexOf('[') > 0) { evt = evt.substring(0, evt.indexOf('[')); } }

            document.getElementById('events').innerHTML = evt;

            
            // always display the last relevant status
            evtReceivedTick = GetTickCount();
            if (evt.indexOf('STATUS') >= 0) { lastStatus = evt; }
            if (eventTimer === null)
            {
                // We receive EVENT and STATUS type messages here. We display EVENT messages only for a few seconds and after which we put back the last STATUS message (so always the relevant status is displayed)
                eventTimer = setInterval(function ()
                {
                    if (GetTickCount() - evtReceivedTick > 3000 && lastStatus.length > 0)
                    {
                        evtReceivedTick = GetTickCount();
                        if (lastStatus.indexOf('Registered') > 0) { lastStatus = lastStatus.substring(0, lastStatus.indexOf('Registered') + 10); }
                        document.getElementById('events').innerHTML = lastStatus;
                    }
                }, 300);
            }
            
            //ProcessEvents(evt);
        });
        var eventTimer = null;
        var lastStatus = '';
        var evtReceivedTick = 0;
        
        
        
        webphone_api.onCallStateChange(function (event, direction, peername, peerdisplayname, line)
        {
            if (event === 'callSetup')
            {
                document.getElementById('btn_hangup').disabled = false;

                if (direction == 1)
                {
                    // means it's outgoing call
                }
                else if (direction == 2)
                {
                    // means it's icoming call
                    
                    document.getElementById('icoming_call_layout').style.display = 'block';
                }
            }
            
            //detecting the end of a call, even if it wasn't successfull
            if (event === 'callDisconnected')
            {
                document.getElementById('btn_hangup').disabled = true;
                document.getElementById('icoming_call_layout').style.display = 'none';
            }
        });
        function GetTickCount() // returns the current time in milliseconds
        {
            var currDate = new Date();
            return currDate.getTime();
        }
        /*
        function ProcessEvents(evt)
        {
            var evtarray = evt.split(',');

            // detecting incoming and outgoing calls
            if (evtarray[0] === 'STATUS' && evtarray[2] === 'Ringing')
            {
                document.getElementById('btn_hangup').disabled = false;

                if (evtarray[5] === '1')
                {
                    // means it's outgoing call
                }
                else if (evtarray[5] === '2')
                {
                    // means it's icoming call
                    
                    document.getElementById('icoming_call_layout').style.display = 'block';
                }
            }
            
            //detecting the end of a call, even if it wasn't successfull
            if (evtarray[0] === 'STATUS' && (evtarray[2] === 'Finished' || evtarray[2] === 'Call Finished'))
            {
                document.getElementById('btn_hangup').disabled = true;
                document.getElementById('icoming_call_layout').style.display = 'none';
            }
        }*/
    </script>
</body>
</html>