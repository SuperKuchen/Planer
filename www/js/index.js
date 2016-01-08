//var url = "http://10.1.20.140/webapps/Jans%20Planer/www/api.php?";
var url = "api.php?";
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

$( "#login" ).click(function(){
    $.ajax({
        url:url+"fn=getUser",
        type:"POST",
        data:{'Email':$('#email').val(), 'Name':$('#name').val()},
        success:function(msg){
            console.log(msg);
            if(msg != "null"){
                window.location = "veranstaltungen.html";
            }
            else{
                $('#alert').html('<div class="alert alert-danger"><strong>Error!</strong> Name oder Email falsch. Bitte überprüfen!</div>');
            }
        },
        error:function(xhr, ajaxOptions, thrownError){
            console.log("ERROR" + xhr.responseText + thrownError);
        }
    });
});

$( "#go" ).ready(function(){
    if($(this)[0].title == "Jan's Planer Veranstaltungen")
    {
        $.ajax({
            url:url+"fn=getVeranstaltungen",
            type:"GET",
            success:function(msg){
                $('#content').html(msg);
                
                $( ".info" ).click(function(){
                    var id = $(this).attr('id');
                    window.location = "info.html?iid="+id;
                });
                
                $( "#neu" ).click(function(){
                    window.location = "neu.html";
                });
                
            },
            error:function(xhr, ajaxOptions, thrownError){
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
    }
});

$().ready(function(){
    loadinfo($(this)[0].title);
    
    $( "#neu" ).click(function(){
        $.ajax({
            url:url+"fn=createVer",
            type:"POST",
            data:{'name':$('#name').val(), 'ort':$('#ort').val(), 'bild':$('#bild').val(), 'beschreibung':$('#beschreibung').val()},
            success:function(msg){
                console.log(msg);
                if(msg != "null"){
                    window.location = "einladen.html?iid="+msg;
                }
                else{
                    $('#alert').html('<div class="alert alert-danger"><strong>Error!</strong> Informationen fehlen. Bitte überprüfen!</div>');
                }
            },
            error:function(xhr, ajaxOptions, thrownError){
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
    });
})

function loadinfo(titel)
{
    if(titel == "Jan's Planer Info"){
        var iid = $.urlParam('iid');
        $.ajax({
            url:url+"fn=getVeranstaltungenInfos",
            type:"POST",
            data:{'vid':iid},
            success:function(msg){
                $('#content').html(msg);
                
                var vid = $.urlParam('iid');
                $( ".btn" ).click(function(){
                    var id = $(this).attr('id').split('|');
                    if(id[0] == 'Z'){
                        $.ajax({
                            url:url+"fn=zusagen",
                            type:"POST",
                            data:{'vid':vid}
                        }).done(function(asd){
                                console.log(asd);
                                loadinfo($(document)[0].title);
                            });
                    }
                    else if(id[0] == 'A'){
                        $.ajax({
                            url:url+"fn=absagen",
                            type:"POST",
                            data:{'vid':vid}
                        }).done(function(asd){
                                console.log(asd);
                                loadinfo($(document)[0].title);
                            });
                    }

                });
                
                                
                $( "button.btn" ).click(function() {
                    var el = "#"+$(this).attr('id')+"T";
                    if($(el).attr('style') == 'display: none;'){                      
                        $(el).slideDown();
                    }
                    else{
                        $(el).slideUp();
                    }
                });
            },
            error:function(xhr, ajaxOptions, thrownError){
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
    }
}

$.urlParam = function(name){
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results==null){
       return null;
    }
    else{
       return results[1] || 0;
    }
}