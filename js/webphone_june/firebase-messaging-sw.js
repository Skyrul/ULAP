// In order to receive the Push Notifications, your app must define the Firebase messaging service worker in firebase-messaging-sw.js
// !!! IMPORTANT: This file must be located in the root directory of your web page where you are hosting the webphone (NOT in the root directory of the webphone). Example: https://www.domain.com/firebase-messaging-sw.js !!!
importScripts('https://www.gstatic.com/firebasejs/5.0.3/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/5.0.3/firebase-messaging.js');



// FCM Push Notifications config
var config = {
        apiKey: "AIzaSyCREU-8xSHkP093-OrE7dSouPYa5lwI380",
        authDomain: "voippush-da64b.firebaseapp.com",
        databaseURL: "https://voippush-da64b.firebaseio.com",
        projectId: "voippush-da64b",
        storageBucket: "voippush-da64b.appspot.com",
        messagingSenderId: "191412546148"
      };




firebase.initializeApp(config);

var messaging = firebase.messaging();
messaging.setBackgroundMessageHandler(function(payload)
{
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    // Customize notification here
    var notificationTitle = 'Webphone';
    var notificationBody = 'Incoming call';
    if (typeof (payload) && payload !== null)
    {
        if (typeof (payload.title) !== 'undefined' && payload.title !== null)
        {
            notificationTitle = payload.title;
        }
        if (typeof (payload.body) !== 'undefined' && payload.body !== null)
        {
            notificationBody = payload.body;
        }
    }
    var notificationOptions = {
        body: notificationBody,
        icon: '' // icon image path, ex: 'images/notification-icon.png'
    };

    return self.registration.showNotification(notificationTitle, notificationOptions);
});
