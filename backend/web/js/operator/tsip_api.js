/*
* Copyright (C) 2012-2016 Doubango Telecom <http://www.doubango.org>
* License: BSD
* This file is part of Open Source sipML5 solution <http://www.sipml5.org>
*/
function tsip_api_add_js_scripts(s_elt) {
    var tag_hdr = document.getElementsByTagName(s_elt)[0];
    for (var i = 1; i < arguments.length; ++i) {
        var tag_script = document.createElement('script');
        tag_script.setAttribute('type', 'text/javascript');
        tag_script.setAttribute('src', "/js/operator/" + arguments[i] + "?svn=251");
        tag_hdr.appendChild(tag_script);
    }
};

// add tinySAK API
tsip_api_add_js_scripts('head', 'src/tinySAK/src/tsk_api.js');

// add tinyMEDIA API
tsip_api_add_js_scripts('head', 'src/tinyMEDIA/src/tmedia_api.js');

// add tinySDP API
tsip_api_add_js_scripts('head', 'src/tinySDP/src/tsdp_api.js');

// add tinySIP API
tsip_api_add_js_scripts('head',
'src/tinySIP/src/tsip_action.js',
'src/tinySIP/src/tsip_event.js',
'src/tinySIP/src/tsip_message.js',
'src/tinySIP/src/tsip_session.js',
'src/tinySIP/src/tsip_stack.js',
'src/tinySIP/src/tsip_timers.js',
'src/tinySIP/src/tsip_uri.js'
);

tsip_api_add_js_scripts('head',
'src/tinySIP/src/authentication/tsip_auth.js',
'src/tinySIP/src/authentication/tsip_challenge.js'
);

tsip_api_add_js_scripts('head', 
'src/tinySIP/src/dialogs/tsip_dialog.js',
'src/tinySIP/src/dialogs/tsip_dialog_layer.js'
);

tsip_api_add_js_scripts('head',
'src/tinySIP/src/headers/tsip_header.js'
);

tsip_api_add_js_scripts('head',
'src/tinySIP/src/parsers/tsip_parser_header.js'
);

tsip_api_add_js_scripts('head',
'src/tinySIP/src/transactions/tsip_transac.js'
);

tsip_api_add_js_scripts('head',
'src/tinySIP/src/transports/tsip_transport.js',
'src/tinySIP/src/transports/tsip_transport_layer.js'
);