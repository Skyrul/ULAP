<!DOCTYPE html>

<!-- 
Click to call button implementation.
You can use it as-is on your website (after adjusting the parameters below) or modify after your needs.
For more customization details check out: js/click2call/click2call.js  and  css/click2call/click2call.css.
See the "Click to call" section in the documentation for a description.

You can also start click2call (or any other html files) from an url:
http://www.yourwebsite.com/webphonedir/click2call.html?wp_serveraddress=YOURSIPDOMAIN&wp_username=USERNAME&wp_password=PASSWORD&wp_md5=MD5AUTH&wp_callto=CALLEDNUMBER
-->

<html>
<head>
    <meta charset="utf-8" />
    <title>Click to call</title>
    
    <link rel="stylesheet" href="<?php echo Yii::app()->request->baseUrl; ?>/js/webphone/css/click2call/click2call.css" />
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/webphone/webphone_api.js?jscodeversion=133"></script>
    <script src="<?php echo Yii::app()->request->baseUrl; ?>/js/webphone/js/click2call/click2call.js?jscodeversion=133"></script>
    <script>
        /**Set Configuration parameters*/
        webphone_api.parameters['autostart'] = false;   // start the webphone only when button is clicked
        webphone_api.onLoaded(function ()
        {
            webphone_api.setparameter('serveraddress', '107.182.238.147'); // yoursipdomain.com your VoIP server IP address or domain name
            webphone_api.setparameter('username', '801');      // SIP account username
            webphone_api.setparameter('password', 'test1243');      // SIP account password (see the "Parameters encryption" in the documentation)
            webphone_api.setparameter('callto', '819093900003');        // destination number to call
            webphone_api.setparameter('autoaction', '0');    // 0=nothing (default), 1=call, 2=chat, 3=video call
        });
    </script>
</head>
<body style="margin: 0; font-family: Verdana ; font-size: 12px; color:#2e3d47; line-height: 155%;">
    <div style="font-family: Verdana ; font-size: 16px; color:#4eaaec; text-align:center">Click to call</div><br><br>

    <span id="demo_text">This is a click to call implementation of the webphone. You can easily modify this after your needs or create your custom click 2 call soltion. <br />See the "Click to call" section in the documentation about more details. <br /></span><br />	

    <div id="c2k_container_0" title="" style="text-align: center;">
    <!--rewrite the CALLTO and uncomment the following line to enable support for ancient browsers-->
    <!--<a href="tel://CALLTO" id="c2k_alternative_url">CALLTO</a>-->
    </div>
</body>
</html>
