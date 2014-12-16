/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */
var app = {
    // Application Constructor
    initialize: function() {
        this.bindEvents();
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicitly call 'app.receivedEvent(...);'
    onDeviceReady: function() {
        app.receivedEvent('deviceready');
    },
    // Update DOM on a Received Event
    receivedEvent: function(id) {
        var parentElement = document.getElementById(id);
        var listeningElement = parentElement.querySelector('.listening');
        var receivedElement = parentElement.querySelector('.received');

        listeningElement.setAttribute('style', 'display:none;');
        receivedElement.setAttribute('style', 'display:block;');

        console.log('[DEBUG]Received Event: ' + id);
	
		if (id == 'deviceready')
		{
			//httpRequest('POST');
			window.location = 'index.html';
			console.log('[DEBUG] index.html loaded');
		}
    }
};

var xmlHttp = null;
var xmlHttpRespose;
var serverAddress = '10.0.0.197';

function httpRequest(requestType)
{   
	// function parameter default value
	requestType = requestType || 'GET';
    
	xmlHttp = new XMLHttpRequest();
	xmlHttp.open(requestType, 'http://' + serverAddress + '/karybu/index.php?mid=get_started&act=procMobile_applicationAdminGenerateStaticPage', true);
	xmlHttp.onreadystatechange = handleReadyStateChange;
	
	xmlHttp.addEventListener('progress', updateProgress, false);
	xmlHttp.addEventListener('load', transferComplete, false);
	xmlHttp.addEventListener('error', transferFailed, false);
	xmlHttp.addEventListener('abort', transferCanceled, false);
    
	xmlHttp.send(null);
    return xmlHttp.responseText;
}

function handleReadyStateChange()
{
	console.log('[DEBUG]handleReadyStateChange');
	if (xmlHttp.readyState == 4)
	{
        if (xmlHttp.status == 200)
        {
			xmlHttpRespose = xmlHttp.responseText;
			xmlHttpRespose.replace('localhost', serverAddress);
			document.body.innerHTML = xmlHttpRespose;
		}
    }
}

// progress on transfers from the server to the client (downloads)
function updateProgress(oEvent) {
   console.log('[DEBUG]updateProgress');
  if (oEvent.lengthComputable) {
    var percentComplete = oEvent.loaded / oEvent.total;
    // ...
  } else {
    // Unable to compute progress information since the total size is unknown
  }
}

function transferComplete(evt)
{
  console.log('[DEBUG]The transfer is complete.');
}

function transferFailed(evt)
{
  console.log('[DEBUG]An error occurred while transferring the file.');
}

function transferCanceled(evt)
{
  console.log('[DEBUG]The transfer has been canceled by the user.');
}

function onClickRequest()
{
	httpRequest('POST');
}