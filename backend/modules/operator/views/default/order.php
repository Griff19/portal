<?php
/**
 * Окно дозвона и набора заказа
 */

use backend\models\Basket;
use yii\grid\GridView;
use yii\grid\Column;
use yii\helpers\Html;
use backend\modules\operator\assets\DefaultAsset;

/**
 * @var $customer   \backend\models\Customers
 * @var $goodData   \yii\data\ActiveDataProvider
 * @var $goodSearch \backend\models\GoodsSearch
 * @var $typicalOrderData \yii\data\ActiveDataProvider
 * @var $basketData \yii\data\ActiveDataProvider
 */

DefaultAsset::register($this);
$this->title = "Заявка";

?>

<!-- Javascript code -->
<script type="text/javascript">

    var oSipStack, oSipSessionRegister, oSipSessionCall, oSipSessionTransferCall;
    var videoRemote, videoLocal, audioRemote;
    var oNotifICall;
    var bDisableVideo = false;
    var viewVideoLocal, viewVideoRemote;
    var oConfigCall;
    var oReadyStateTimer;

    C = {divKeyPadWidth: 220};

    window.onload = function () {
        window.console && window.console.info && window.console.info("location=" + window.location);

        videoLocal = document.getElementById("video_local");
        videoRemote = document.getElementById("video_remote");
        audioRemote = document.getElementById("audio_remote");

        divCallCtrl.onmousemove = onDivCallCtrlMouseMove;

        // set debug level
        SIPml.setDebugLevel((window.localStorage && window.localStorage.getItem('org.doubango.expert.disable_debug') == "true") ? "error" : "info");

        //loadCredentials();
        loadCallOptions();

        // Initialize call button
        uiBtnCallSetText("Call");

        var getPVal = function (PName) {
            var query = window.location.search.substring(1);
            var vars = query.split('&');
            for (var i = 0; i < vars.length; i++) {
                var pair = vars[i].split('=');
                if (decodeURIComponent(pair[0]) === PName) {
                    return decodeURIComponent(pair[1]);
                }
            }
            return null;
        }

        var preInit = function () {
            // set default webrtc type (before initialization)
            var s_webrtc_type = getPVal("wt");
            var s_fps = getPVal("fps");
            var s_mvs = getPVal("mvs"); // maxVideoSize
            var s_mbwu = getPVal("mbwu"); // maxBandwidthUp (kbps)
            var s_mbwd = getPVal("mbwd"); // maxBandwidthUp (kbps)
            var s_za = getPVal("za"); // ZeroArtifacts
            var s_ndb = getPVal("ndb"); // NativeDebug

            if (s_webrtc_type) SIPml.setWebRtcType(s_webrtc_type);

            // initialize SIPML5
            SIPml.init(postInit);

            // set other options after initialization
            if (s_fps) SIPml.setFps(parseFloat(s_fps));
            if (s_mvs) SIPml.setMaxVideoSize(s_mvs);
            if (s_mbwu) SIPml.setMaxBandwidthUp(parseFloat(s_mbwu));
            if (s_mbwd) SIPml.setMaxBandwidthDown(parseFloat(s_mbwd));
            if (s_za) SIPml.setZeroArtifacts(s_za === "true");
            if (s_ndb == "true") SIPml.startNativeDebug();

        }

        oReadyStateTimer = setInterval(function () {
                if (document.readyState === "complete") {
                    clearInterval(oReadyStateTimer);
                    // initialize SIPML5
                    preInit();
                    sipRegister();
                }
            },
            500);
        $('#search').focus();
    };

    function postInit() {
        // check for WebRTC support
        if (!SIPml.isWebRtcSupported()) {
            // is it chrome?
            if (SIPml.getNavigatorFriendlyName() == 'chrome') {
                if (confirm("You're using an old Chrome version or WebRTC is not enabled.\nDo you want to see how to enable WebRTC?")) {
                    window.location = 'http://www.webrtc.org/running-the-demos';
                }
                else {
                    window.location = "index.html";
                }
                return;
            }
            else {
                if (confirm("webrtc-everywhere extension is not installed. Do you want to install it?\nIMPORTANT: You must restart your browser after the installation.")) {
                    window.location = 'https://github.com/sarandogou/webrtc-everywhere';
                }
                else {
                    // Must do nothing: give the user the chance to accept the extension
                    // window.location = "index.html";
                }
            }
        }

        // checks for WebSocket support
        if (!SIPml.isWebSocketSupported()) {
            if (confirm('Your browser don\'t support WebSockets.\nDo you want to download a WebSocket-capable browser?')) {
                window.location = 'https://www.google.com/intl/en/chrome/browser/';
            }
            else {
                window.location = "index.html";
            }
            return;
        }

        // FIXME: displays must be per session
        viewVideoLocal = videoLocal;
        viewVideoRemote = videoRemote;

        if (!SIPml.isWebRtcSupported()) {
            if (confirm('Your browser don\'t support WebRTC.\naudio/video calls will be disabled.\nDo you want to download a WebRTC-capable browser?')) {
                window.location = 'https://www.google.com/intl/en/chrome/browser/';
            }
        }

        btnRegister.disabled = false;
        document.body.style.cursor = 'default';
        oConfigCall = {
            audio_remote: audioRemote,
            video_local: viewVideoLocal,
            video_remote: viewVideoRemote,
            screencast_window_id: 0x00000000, // entire desktop
            bandwidth: {audio: undefined, video: undefined},
            video_size: {minWidth: undefined, minHeight: undefined, maxWidth: undefined, maxHeight: undefined},
            events_listener: {events: '*', listener: onSipEventSession},
            sip_caps: [
                {name: '+g.oma.sip-im'},
                {name: 'language', value: '\"en,fr\"'}
            ]
        };
    }

    function loadCallOptions() {
        if (window.localStorage) {
            var s_value;
            if ((s_value = window.localStorage.getItem('org.doubango.call.phone_number'))) txtPhoneNumber.value = s_value;
            bDisableVideo = (window.localStorage.getItem('org.doubango.expert.disable_video') == "true");
            txtCallStatus.innerHTML = '<i>Video ' + (bDisableVideo ? 'disabled' : 'enabled') + '</i>';
        }
    }

    function saveCallOptions() {
        if (window.localStorage) {
            window.localStorage.setItem('org.doubango.call.phone_number', txtPhoneNumber.value);
            window.localStorage.setItem('org.doubango.expert.disable_video', bDisableVideo ? "true" : "false");
        }
    }

    // sends SIP REGISTER request to login
    function sipRegister() {
        // catch exception for IE (DOM not ready)
        try {
            btnRegister.disabled = true;

            // enable notifications if not already done
            if (window.webkitNotifications && window.webkitNotifications.checkPermission() != 0) {
                window.webkitNotifications.requestPermission();
            }

            // update debug level to be sure new values will be used if the user haven't updated the page
            SIPml.setDebugLevel((window.localStorage && window.localStorage.getItem('org.doubango.expert.disable_debug') == "true") ? "error" : "info");
            // create SIP stack
            oSipStack = new SIPml.Stack({
                    realm: '192.168.0.18',
                    impi: '699',
                    impu: 'sip:699@192.168.0.18',
                    password: '699',
                    display_name: '699',
                    websocket_proxy_url: 'ws://192.168.0.18:8088/ws',
                    outbound_proxy_url: null,
                    ice_servers: null,
                    enable_rtcweb_breaker: false,
                    events_listener: {events: '*', listener: onSipEventStack},
                    enable_early_ims: true, // Must be true unless you're using a real IMS network
                    enable_media_stream_cache: true,
                    bandwidth: null, // could be redefined a session-level
                    video_size: null, // could be redefined a session-level
                    sip_headers: [
                        {name: 'User-Agent', value: 'IM-client/OMA1.0 sipML5-v1.2016.03.04'},
                        {name: 'Organization', value: 'AltBurenka'}
                    ]
                }
            );
            if (oSipStack.start() != 0) {
                txtRegStatus.innerHTML = '<b>Failed to start the SIP stack</b>';
            }
            else return;
        }
        catch (e) {
            txtRegStatus.innerHTML = "<b>2:" + e + "</b>";
        }
        btnRegister.disabled = false;
    }

    // sends SIP REGISTER (expires=0) to logout
    function sipUnRegister() {
        if (oSipStack) {
            oSipStack.stop(); // shutdown all sessions
        }
    }

    // makes a call (SIP INVITE)
    function sipCall(s_type) {
        if (oSipStack && !oSipSessionCall && !tsk_string_is_null_or_empty(txtPhoneNumber.value)) {
            if (s_type == 'call-screenshare') {
                alert('Screen sharing not supported. Are you using chrome 26+?');
                return;
            }
            btnCall.disabled = true;
            btnHangUp.disabled = false;

            if (window.localStorage) {
                oConfigCall.bandwidth = tsk_string_to_object(window.localStorage.getItem('org.doubango.expert.bandwidth')); // already defined at stack-level but redifined to use latest values
                oConfigCall.video_size = tsk_string_to_object(window.localStorage.getItem('org.doubango.expert.video_size')); // already defined at stack-level but redifined to use latest values
            }

            // create call session
            oSipSessionCall = oSipStack.newSession(s_type, oConfigCall);
            // make call
            if (oSipSessionCall.call(txtPhoneNumber.value) != 0) {
                oSipSessionCall = null;
                txtCallStatus.value = 'Failed to make call';
                btnCall.disabled = false;
                btnHangUp.disabled = true;
                return;
            }
            saveCallOptions();
        }
        else if (oSipSessionCall) {
            txtCallStatus.innerHTML = '<i>Connecting...</i>';
            oSipSessionCall.accept(oConfigCall);
        }
    }

    // holds or resumes the call
    function sipToggleHoldResume() {
        if (oSipSessionCall) {
            var i_ret;
            btnHoldResume.disabled = true;
            txtCallStatus.innerHTML = oSipSessionCall.bHeld ? '<i>Resuming the call...</i>' : '<i>Holding the call...</i>';
            i_ret = oSipSessionCall.bHeld ? oSipSessionCall.resume() : oSipSessionCall.hold();
            if (i_ret != 0) {
                txtCallStatus.innerHTML = '<i>Hold / Resume failed</i>';
                btnHoldResume.disabled = false;
                return;
            }
        }
    }

    // Mute or Unmute the call
    function sipToggleMute() {
        if (oSipSessionCall) {
            var i_ret;
            var bMute = !oSipSessionCall.bMute;
            txtCallStatus.innerHTML = bMute ? '<i>Mute the call...</i>' : '<i>Unmute the call...</i>';
            i_ret = oSipSessionCall.mute('audio'/*could be 'video'*/, bMute);
            if (i_ret != 0) {
                txtCallStatus.innerHTML = '<i>Mute / Unmute failed</i>';
                return;
            }
            oSipSessionCall.bMute = bMute;
            btnMute.value = bMute ? "Unmute" : "Mute";
        }
    }

    // terminates the call (SIP BYE or CANCEL)
    function sipHangUp() {
        if (oSipSessionCall) {
            txtCallStatus.innerHTML = '<i>Terminating the call...</i>';
            oSipSessionCall.hangup({events_listener: {events: '*', listener: onSipEventSession}});
        }
    }

    function sipSendDTMF(c) {
        if (oSipSessionCall && c) {
            if (oSipSessionCall.dtmf(c) == 0) {
                try {
                    dtmfTone.play();
                } catch (e) {
                }
            }
        }
    }

    function startRingTone() {
        try {
            ringtone.play();
        }
        catch (e) {
        }
    }

    function stopRingTone() {
        try {
            ringtone.pause();
        }
        catch (e) {
        }
    }

    function startRingbackTone() {
        try {
            ringbacktone.play();
        }
        catch (e) {
        }
    }

    function stopRingbackTone() {
        try {
            ringbacktone.pause();
        }
        catch (e) {
        }
    }

    function openKeyPad() {
        divKeyPad.style.visibility = 'visible';
        divKeyPad.style.left = ((document.body.clientWidth - C.divKeyPadWidth) >> 1) + 'px';
        divKeyPad.style.top = '70px';
        divGlassPanel.style.visibility = 'visible';
    }

    function closeKeyPad() {
        divKeyPad.style.left = '0px';
        divKeyPad.style.top = '0px';
        divKeyPad.style.visibility = 'hidden';
        divGlassPanel.style.visibility = 'hidden';
    }

    function showNotifICall(s_number) {
        // permission already asked when we registered
        if (window.webkitNotifications && window.webkitNotifications.checkPermission() == 0) {
            if (oNotifICall) {
                oNotifICall.cancel();
            }
            oNotifICall = window.webkitNotifications.createNotification('images/sipml-34x39.png', 'Incaming call', 'Incoming call from ' + s_number);
            oNotifICall.onclose = function () {
                oNotifICall = null;
            };
            oNotifICall.show();
        }
    }

    function onDivCallCtrlMouseMove(evt) {
        try { // IE: DOM not ready
            if (tsk_utils_have_stream()) {
                btnCall.disabled = (!tsk_utils_have_stream() || !oSipSessionRegister || !oSipSessionRegister.is_connected());
                document.getElementById("divCallCtrl").onmousemove = null; // unsubscribe
            }
        }
        catch (e) {
        }
    }

    function uiOnConnectionEvent(b_connected, b_connecting) { // should be enum: connecting, connected, terminating, terminated
        btnRegister.disabled = b_connected || b_connecting;
        btnUnRegister.disabled = !b_connected && !b_connecting;
        btnCall.disabled = !(b_connected && tsk_utils_have_webrtc() && tsk_utils_have_stream());
        btnHangUp.disabled = !oSipSessionCall;
    }

    function uiBtnCallSetText(s_text) {
        switch (s_text) {
            case "Call": {

                btnCall.value = btnCall.innerHTML = 'Call';
                btnCall.setAttribute("class", "btn btn-primary");
                btnCall.onclick = function () {
                    sipCall('call-audio');
                };

                break;
            }
            default: {
                btnCall.value = btnCall.innerHTML = s_text;
                btnCall.setAttribute("class", "btn btn-primary");
                btnCall.onclick = function () {
                    sipCall(bDisableVideo ? 'call-audio' : 'call-audiovideo');
                };

                break;
            }
        }
    }

    function uiCallTerminated(s_description) {
        uiBtnCallSetText("Call");
        btnHangUp.value = 'HangUp';
        btnHoldResume.value = 'hold';
        btnMute.value = "Mute";
        btnCall.disabled = false;
        btnHangUp.disabled = true;
        if (window.btnBFCP) window.btnBFCP.disabled = true;

        oSipSessionCall = null;

        stopRingbackTone();
        stopRingTone();

        txtCallStatus.innerHTML = "<i>" + s_description + "</i>";
        divCallOptions.style.opacity = 0;

        if (oNotifICall) {
            oNotifICall.cancel();
            oNotifICall = null;
        }

        setTimeout(function () {
            if (!oSipSessionCall) txtCallStatus.innerHTML = '';
        }, 2500);
    }

    // Callback function for SIP Stacks
    function onSipEventStack(e /*SIPml.Stack.Event*/) {
        tsk_utils_log_info('==stack event = ' + e.type);
        switch (e.type) {
            case 'started': {
                // catch exception for IE (DOM not ready)
                try {
                    // LogIn (REGISTER) as soon as the stack finish starting
                    oSipSessionRegister = this.newSession('register', {
                        expires: 200,
                        events_listener: {events: '*', listener: onSipEventSession},
                        sip_caps: [
                            {name: '+g.oma.sip-im', value: null},
                            //{ name: '+sip.ice' }, // rfc5768: FIXME doesn't work with Polycom TelePresence
                            {name: '+audio', value: null},
                            {name: 'language', value: '\"en,fr\"'}
                        ]
                    });
                    oSipSessionRegister.register();
                }
                catch (e) {
                    txtRegStatus.value = txtRegStatus.innerHTML = "<b>1:" + e + "</b>";
                    btnRegister.disabled = false;
                }
                break;
            }
            case 'stopping':
            case 'stopped':
            case 'failed_to_start':
            case 'failed_to_stop': {
                var bFailure = (e.type == 'failed_to_start') || (e.type == 'failed_to_stop');
                oSipStack = null;
                oSipSessionRegister = null;
                oSipSessionCall = null;

                uiOnConnectionEvent(false, false);

                stopRingbackTone();
                stopRingTone();

                divCallOptions.style.opacity = 0;

                txtCallStatus.innerHTML = '';
                txtRegStatus.innerHTML = bFailure ? "<i>Disconnected: <b>" + e.description + "</b></i>" : "<i>Disconnected</i>";
                break;
            }

            case 'i_new_call': {
                if (oSipSessionCall) {
                    // do not accept the incoming call if we're already 'in call'
                    e.newSession.hangup(); // comment this line for multi-line support
                }
                else {
                    oSipSessionCall = e.newSession;
                    // start listening for events
                    oSipSessionCall.setConfiguration(oConfigCall);

                    uiBtnCallSetText('Answer');
                    btnHangUp.value = 'Reject';
                    btnCall.disabled = false;
                    btnHangUp.disabled = false;

                    startRingTone();

                    var sRemoteNumber = (oSipSessionCall.getRemoteFriendlyName() || 'unknown');
                    txtCallStatus.innerHTML = "<i>Incoming call from [<b>" + sRemoteNumber + "</b>]</i>";
                    showNotifICall(sRemoteNumber);
                }
                break;
            }

            case 'm_permission_requested': {
                divGlassPanel.style.visibility = 'visible';
                break;
            }
            case 'm_permission_accepted':
            case 'm_permission_refused': {
                divGlassPanel.style.visibility = 'hidden';
                if (e.type == 'm_permission_refused') {
                    uiCallTerminated('Media stream permission denied');
                }
                break;
            }

            case 'starting':
            default:
                break;
        }
    };

    function addBasketOne(id) {
        $.post('/basket/addone?id=' + id + '&mod=1', function(res) {
            var res = JSON.parse(res);
            $('#basket > tbody').html(res[0]);
            $('#summ').html(res[1]);
        });
    };

    function delBasketOne(id) {
        $.post('/basket/deleteone?id=' + id + '&mod=1', function(res) {
            var res = JSON.parse(res);
            $('#basket > tbody').html(res[0]);
            $('#summ').html(res[1]);
        });
    }

    // Callback function for SIP sessions (INVITE, REGISTER, MESSAGE...)
    function onSipEventSession(e /* SIPml.Session.Event */) {
        tsk_utils_log_info('==session event = ' + e.type);

        switch (e.type) {
            case 'connecting':
            case 'connected': {
                var bConnected = (e.type == 'connected');
                if (e.session == oSipSessionRegister) {
                    uiOnConnectionEvent(bConnected, !bConnected);
                    txtRegStatus.innerHTML = "<i>" + e.description + "</i>";
                }
                else if (e.session == oSipSessionCall) {
                    btnHangUp.value = 'HangUp';
                    btnCall.disabled = true;
                    btnHangUp.disabled = false;

                    if (window.btnBFCP) window.btnBFCP.disabled = false;

                    if (bConnected) {
                        stopRingbackTone();
                        stopRingTone();

                        if (oNotifICall) {
                            oNotifICall.cancel();
                            oNotifICall = null;
                        }
                    }

                    txtCallStatus.innerHTML = "<i>" + e.description + "</i>";
                    divCallOptions.style.opacity = bConnected ? 1 : 0;

                }
                break;
            } // 'connecting' | 'connected'
            case 'terminating':
            case 'terminated': {
                if (e.session == oSipSessionRegister) {
                    uiOnConnectionEvent(false, false);

                    oSipSessionCall = null;
                    oSipSessionRegister = null;

                    txtRegStatus.innerHTML = "<i>" + e.description + "</i>";
                }
                else if (e.session == oSipSessionCall) {
                    uiCallTerminated(e.description);
                }
                break;
            } // 'terminating' | 'terminated'

            case 'm_stream_audio_local_added':
            case 'm_stream_audio_local_removed':
            case 'm_stream_audio_remote_added':
            case 'm_stream_audio_remote_removed': {
                break;
            }

            case 'i_ect_new_call': {
                oSipSessionTransferCall = e.session;
                break;
            }

            case 'i_ao_request': {
                if (e.session == oSipSessionCall) {
                    var iSipResponseCode = e.getSipResponseCode();
                    if (iSipResponseCode == 180 || iSipResponseCode == 183) {
                        startRingbackTone();
                        txtCallStatus.innerHTML = '<i>Remote ringing...</i>';
                    }
                }
                break;
            }

            case 'm_early_media': {
                if (e.session == oSipSessionCall) {
                    stopRingbackTone();
                    stopRingTone();
                    txtCallStatus.innerHTML = '<i>Early media started</i>';
                }
                break;
            }

            case 'm_local_hold_ok': {
                if (e.session == oSipSessionCall) {
                    if (oSipSessionCall.bTransfering) {
                        oSipSessionCall.bTransfering = false;
                        // this.AVSession.TransferCall(this.transferUri);
                    }
                    btnHoldResume.value = 'Resume';
                    btnHoldResume.disabled = false;
                    txtCallStatus.innerHTML = '<i>Call placed on hold</i>';
                    oSipSessionCall.bHeld = true;
                }
                break;
            }
            case 'm_local_hold_nok': {
                if (e.session == oSipSessionCall) {
                    oSipSessionCall.bTransfering = false;
                    btnHoldResume.value = 'Hold';
                    btnHoldResume.disabled = false;
                    txtCallStatus.innerHTML = '<i>Failed to place remote party on hold</i>';
                }
                break;
            }
            case 'm_local_resume_ok': {
                if (e.session == oSipSessionCall) {
                    oSipSessionCall.bTransfering = false;
                    btnHoldResume.value = 'Hold';
                    btnHoldResume.disabled = false;
                    txtCallStatus.innerHTML = '<i>Call taken off hold</i>';
                    oSipSessionCall.bHeld = false;
                }
                break;
            }
            case 'm_local_resume_nok': {
                if (e.session == oSipSessionCall) {
                    oSipSessionCall.bTransfering = false;
                    btnHoldResume.disabled = false;
                    txtCallStatus.innerHTML = '<i>Failed to unhold call</i>';
                }
                break;
            }
            case 'm_remote_hold': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = '<i>Placed on hold by remote party</i>';
                }
                break;
            }
            case 'm_remote_resume': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = '<i>Taken off hold by remote party</i>';
                }
                break;
            }
            case 'm_bfcp_info': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = 'BFCP Info: <i>' + e.description + '</i>';
                }
                break;
            }
            /*
            case 'o_ect_trying': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = '<i>Call transfer in progress...</i>';
                }
                break;
            }
            case 'o_ect_accepted': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = '<i>Call transfer accepted</i>';
                }
                break;
            }
            case 'o_ect_completed':
            case 'i_ect_completed': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = '<i>Call transfer completed</i>';

                    if (oSipSessionTransferCall) {
                        oSipSessionCall = oSipSessionTransferCall;
                    }
                    oSipSessionTransferCall = null;
                }
                break;
            }
            case 'o_ect_failed':
            case 'i_ect_failed': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = '<i>Call transfer failed</i>';

                }
                break;
            }
            case 'o_ect_notify':
            case 'i_ect_notify': {
                if (e.session == oSipSessionCall) {
                    txtCallStatus.innerHTML = "<i>Call Transfer: <b>" + e.getSipResponseCode() + " " + e.description + "</b></i>";
                    if (e.getSipResponseCode() >= 300) {
                        if (oSipSessionCall.bHeld) {
                            oSipSessionCall.resume();
                        }

                    }
                }
                break;
            }
            case 'i_ect_requested': {
                if (e.session == oSipSessionCall) {
                    var s_message = "Do you accept call transfer to [" + e.getTransferDestinationFriendlyName() + "]?";//FIXME
                    if (confirm(s_message)) {
                        txtCallStatus.innerHTML = "<i>Call transfer in progress...</i>";
                        oSipSessionCall.acceptTransfer();
                        break;
                    }
                    oSipSessionCall.rejectTransfer();
                }
                break;
            }*/
        }
    }
</script>
<div class="row">
    <div class="col-md-8">
        <h4>Типичная заявка:</h4>
        <?= GridView::widget([
            'tableOptions' => ['id' => 'typicalOrder', 'class' => 'table table-bordered table-nopadding'],
            'dataProvider' => $typicalOrderData,
            'layout'=>"{items}",
            'columns' => [
                'good_id',
                'good.good_name',
                'count',
                ['class' => Column::className(),
                    'header' => 'Повторить',
                    'content' => function($model){
                        return Html::a('<span class="glyphicon glyphicon-ok"></span>', '#', [
                                'class' => 'addBasket',
                                'data-customer' => $model->customer_id,
                                'data-good' => $model->good_hash,
                                'data-count' => $model->count
                            ]);
                    },
                    'contentOptions' => ['style' => 'text-align: center']
                ]
            ]
        ]); ?>

        <h4>Набираемый заказ:</h4>
	    <?= GridView::widget([
            'tableOptions' => ['id' => 'basket', 'class' => 'table table-bordered table-nopadding'],
		    'layout' => '{items}',
		    'dataProvider' => $basketData,
            'columns' => [
                'id',
                'user_id',
                'goods.good_name',
                ['attribute' => 'count',
                    'value' => function($model) {
	                    return $model->count
	                    . Html::a('+', 'javascript:void(0);', ['class' => 'btn btn-xs btn-primary', 'onclick' => "addBasketOne($model->id)",
                                             'style' => 'float:right;'])
                        . Html::a('-', 'javascript:void(0);', ['class' => 'btn btn-xs btn-primary', 'onclick' => "delBasketOne($model->id)",
                                             'style' => 'float:right; padding: 1px 7px 1px 7px;']);
	                },
                    'format' => 'raw'
                ],
                ['attribute' => 'summ',
                    'value' => function($model){return $model->summ / 100;}
                ]
            ],
	    ])?>
        <p style="float:right">
            <b>Сумма:<span id="summ"><?= Basket::getTotals('summ', $customer->customer_id)?></span>р.</b>
            <?= Html::a('Принять', ['/orders/create', 'amount' => 1], ['class' => 'btn btn-success']);?>
        </p>
    </div>

    <div class="col-md-4">
        <div id="divCallCtrl" class="span3 well" style='display:table-cell; vertical-align:middle'>
            <label style="width: 100%;" align="center" id="txtRegStatus">
            </label>
            <label style="width: 100%;" align="center" id="txtCallStatus">
            </label>
            <h4><?= $customer->customer_name ?></h4>
            <p>Ответственный: <b><?= $customer->directResponsible ?></b></p>
            <br/>

            <input type="text" style="width: 100%; height:100%;" id="txtPhoneNumber" value=""
                   placeholder="Enter phone number to call"/>

            <div style="text-align: center">
                <input type="button" class="btn btn-success" id="btnRegister" value="LogIn" disabled
                       onclick='sipRegister();'/>
                <input type="button" class="btn btn-primary" id="btnCall" value="Call" disabled
                       onclick='sipCall("call-audio");'/>
                <input type="button" class="btn btn-primary" id="btnHangUp" value="HangUp" disabled
                       onclick='sipHangUp();' />
                <input type="button" class="btn btn-danger" id="btnUnRegister" value="LogOut" disabled
                       onclick='sipUnRegister();'/>
            </div>

            <div id='divCallOptions' class='call-options' style="opacity: 1; margin-top: 0px; text-align: center;">
                <input type="button" class="btn btn-default" style="" id="btnMute" value="Mute"
                       onclick='sipToggleMute();'/> &nbsp;
                <input type="button" class="btn btn-default" style="" id="btnHoldResume" value="Hold"
                       onclick='sipToggleHoldResume();'/> &nbsp;
                <!--<input type="button" class="btn btn-default" style="" id="btnKeyPad" value="KeyPad" onclick='openKeyPad();'/>-->
                <input type="button" class="btn btn-default" style="" id="btnKeyPad" value="KeyPad"
                       data-toggle="collapse" data-target="#divKeyPad"/>
            </div>

            <!-- KeyPad Div -->
            <!--<div id='divKeyPad' class='span2 well div-keypad' style="width:100%; height:240px; visibility:hidden">-->
            <div id='divKeyPad' class='collapse' style="width:100%; height:240px; text-align: center">
                <br/>
                <input type="button" style="width: 60px" class="btn btn-default" value="1" onclick="sipSendDTMF('1');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="2" onclick="sipSendDTMF('2');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="3" onclick="sipSendDTMF('3');"/>
                <br/>
                <input type="button" style="width: 60px" class="btn btn-default" value="4" onclick="sipSendDTMF('4');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="5" onclick="sipSendDTMF('5');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="6" onclick="sipSendDTMF('6');"/>
                <br/>
                <input type="button" style="width: 60px" class="btn btn-default" value="7" onclick="sipSendDTMF('7');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="8" onclick="sipSendDTMF('8');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="9" onclick="sipSendDTMF('9');"/>
                <br/>
                <input type="button" style="width: 60px" class="btn btn-default" value="*" onclick="sipSendDTMF('*');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="0" onclick="sipSendDTMF('0');"/>
                <input type="button" style="width: 60px" class="btn btn-default" value="#" onclick="sipSendDTMF('#');"/>
                <br/>
                <!--<td colspan=3><input type="button" style="width: 100%" class="btn btn-medium btn-danger" value="close" onclick="closeKeyPad();"/></td>-->
                <input type="button" style="width: 187px" class="btn btn-medium btn-danger" value="close"
                       data-toggle="collapse" data-target="#divKeyPad"/>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <h4>Номенклаура:</h4>
		<?= GridView::widget([
			'tableOptions' => ['id' => 'goods', 'class' => 'table table-bordered table-nopadding'],
			'dataProvider' => $goodData,
			'filterModel'  => $goodSearch,
			'layout' => '{items}',
			'columns'      => [
                ['attribute'          => 'good_name',
				 'value'              => function ($model) {
					 return Html::a($model->good_name, '#');
				 },
				 'format'             => 'raw',
				 'filterInputOptions' => ['id' => 'search', 'class' => 'form-control', 'autocomplete' => 'off']
				],
				'good_description',
                ['attribute' => 'good_price',
                    'value' => function($model){return $model->good_price / 100;}
                ],
				['class'   => Column::className(),
				 'header'  => 'Количество',
				 'content' => function ($model) {
					 return Html::input('number', 'count' . $model->good_id, 0, [
						 'class' => 'form-control count input-sm', 'id' => $model->good_id
					 ]);
				 },
				 'options' => ['style' => 'width:10px']
				],
			]]);
		?>
    </div>

</div>
<object id="fakePluginInstance" classid="clsid:69E4A9D1-824C-40DA-9680-C7424A27B6A0"
        style="visibility:hidden;"></object>
<!-- Glass Panel -->
<div id='divGlassPanel' class='glass-panel' style='visibility:visible'></div>

<?php
/** Скрипт для интерактивной работы страницы */
$script = <<<JS
    var idx;
    var len;
    var customer = "$customer->customer_id";
    // Останавливаем "всплытие" события 'change' чтобы предотвратить стандартную обработку этого события
    // и не допустить обновления страницы при изменении поля
    $('#search').on('change', function(e) {
        e.stopPropagation();  
    });
    // Ожидаем ввод в строку фильтра и отправляем текущее состояние строки на сервер для поиска данных
    // Перед выводом очищаем таблицу и блок пагинации, получаем строку html и вставляем в тело таблицы как есть
    $('#search').on('input', function () {
        $('ul.pagination').text('');
        //$('#goods > tbody').text('');
        $.post('/goods/search-good?text='+ $(this).val() +"&tp=$customer->typeprices_id", function(res) {
            $('#goods > tbody').html(res);
        });
        idx = -1;
    });
    // Для перехода к строкам таблицы ждем когда в поле ввода нажмут клавишу "вниз"
    // После этого фокусируемся на первой строке таблицы        
    $('#search').on('keydown', function(e) {
        if (e.keyCode === 40) {
            idx = 0; 
            $('.chain').eq(idx).focus();
            $('#goods > tbody > tr').eq(idx).addClass('success').siblings().removeClass('success');
            
         }
    });
    // Для перехода по строкам таблицы вверх и вниз, ловим нажатие клавиш "вверх" и "вниз" в теле таблицы
    // соответствующим образом изменяем индекс текущего элемента +1 или -1
    $('#goods > tbody').on('keydown', function(e) {
        if (e.target.nodeName !== "INPUT"){ 
            len = $('#goods > tbody > tr').length - 1;
            if (e.keyCode === 40) {
                if (idx < len) idx += 1; 
                else idx = len;
            } else if (e.keyCode === 38) {
                if (idx > 0) idx -= 1;
                else idx = 0;
            } 
            
            $('.chain').eq(idx).focus();
            $('#goods > tbody > tr').eq(idx).addClass('success').siblings().removeClass('success');
            
        }
    });
    // Для возобновления поиска просто начинаем вводить новую строку, произойдет переход в поле ввода
    // для установки количества заказываемого товара набираем число, произойтет заполнение поля количества
    // при нажатии клавиши "enter" происходит переход к следующей строке
    $('#goods > tbody').on('keypress', function(e) {
        if (e.keyCode === 13) {
            var c = $('#'+e.target.id).val();
            $.post('/basket/insert?customer_id='+ customer +
                '&good_id='+ e.target.id + 
                '&count=' + c, function(res) {
                    var res = JSON.parse(res);
                    $('#basket > tbody').html(res[0]);
                    $('#summ').html(res[1]);
            });
            idx += 1; 
            $('.chain').eq(idx).focus();
            $('#goods > tbody > tr').eq(idx).addClass('success').siblings().removeClass('success');
            
            return true
        }        
        
        if (e.which != 0 && e.charCode != 0) { // все кроме IE
            if (!e.which < 32) // спец. символ
                var c = String.fromCharCode(e.which); // остальные
        }
        if (isNaN(parseInt(c))) {
            $('#search').focus();
            //$('#search').val(c);
        } else {
            if (e.target.nodeName !== "INPUT")
                $('.count').eq(idx).select();              
        }        
    }); 
    // Добавляем данные в заказ из таблицы "Типичная заявка"
    $('.addBasket').on('click', function(){
        $.post('/basket/insert?customer_id='+ $(this).data('customer') +
            '&good_hash='+ $(this).data('good') + 
            '&count=' + $(this).data('count'), function(res) {
                var res = JSON.parse(res);
                $('#basket > tbody').html(res[0]);
                $('#summ').html(res[1]);
        });
    });    
JS;

$this->registerJs($script);
?>

<audio id="audio_remote" autoplay="autoplay"></audio>
<audio id="ringtone" loop src="/js/operator/sounds/ringtone.wav"></audio>
<audio id="ringbacktone" loop src="/js/operator/sounds/ringbacktone.wav"></audio>
<audio id="dtmfTone" src="/js/operator/sounds/dtmf.wav"></audio>
