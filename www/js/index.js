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

        console.log('Received Event: ' + id);
    }
};

$( "#id01" ).click(function(){
    $('#id02').html("Warten");
    $.ajax({
        //url:"http://10.1.20.140/webapps/Jans%20Planer/www/api.php?fn=getUser",
        url:"api.php?fn=getUser",
        type:"GET",
        success:function(msg){
            $('#id02').html(msg[0]['id']);
        },
        error:function(xhr, ajaxOptions, thrownError){
            $('#id02').html("ERROR" + xhr.responseText + thrownError);
        },
        dataType:"json"
    });
});

$( "body" ).ready(function(){
    $.ajax({
        //url:"http://10.1.20.140/webapps/Jans%20Planer/www/api.php?fn=getVeranstaltungen",
        url:"api.php?fn=getVeranstaltungen",
        type:"GET",
        success:function(msg){
            $('#content').html(msg);
            
            $( ".info" ).click(function(){
                $("body").load("info.html");
                var id = $(this).attr('id');
                $.ajax({
                    //url:"http://10.1.20.140/webapps/Jans%20Planer/www/api.php?fn=getVeranstaltungenInfos",
                    url:"api.php?fn=getVeranstaltungenInfos",
                    type:"POST",
                    data:{'vid':id, 'uid':1},
                    success:function(msg){
                        $('#content').html(msg);
                        
                        
                    },
                    error:function(xhr, ajaxOptions, thrownError){
                        console.log("ERROR" + xhr.responseText + thrownError);
                    }
                });
            });
            
        },
        error:function(xhr, ajaxOptions, thrownError){
            console.log("ERROR" + xhr.responseText + thrownError);
        }
    });
});