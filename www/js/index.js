var url = "http://beckmannjan.de/api/api.php?";
//var url = "api/api.php?";
var app = {
    // Application Constructor
    initialize: function () {
        this.bindEvents();
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function () {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    // deviceready Event Handler
    //
    // The scope of 'this' is the event. In order to call the 'receivedEvent'
    // function, we must explicitly call 'app.receivedEvent(...);'
    onDeviceReady: function () {
        app.receivedEvent('deviceready');
    },
    // Update DOM on a Received Event
    receivedEvent: function (id) {
        var parentElement = document.getElementById(id);
        var listeningElement = parentElement.querySelector('.listening');
        var receivedElement = parentElement.querySelector('.received');

        listeningElement.setAttribute('style', 'display:none;');
        receivedElement.setAttribute('style', 'display:block;');

        console.log('Received Event: ' + id);
    }
};

function reg() {
    $.ajax({
        url: url + "fn=newUser",
        type: "POST",
        data: { 'Vorname': $('#vorname').val(), 'Nachname': $('#nachname').val(), 'Adresse': $('#addresse').val(), 'Email': $('#email').val(), 'Password': $('#passwort').val() },
        success: function (msg) {
            console.log(msg);
            if (msg == "done") {
                window.location = "indexreg.html";
            }
            else if (msg == "Email") {
                $('#alert').html('<div class="alert alert-danger"><strong>Error!</strong> E-mail bereits vorhanden</div>');
            }
            else {
                $('#alert').html('<div class="alert alert-danger"><strong>Error!</strong> Etwas ist ungültig. Bitte überprüfen!</div>');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log("ERROR " + xhr.responseText + thrownError);
        }
    });
};

$("#login").click(function () {
    $.ajax({
        url: url + "fn=getUser",
        type: "POST",
        data: { 'Email': $('#email').val(), 'passwort': $('#passwort').val() },
        success: function (msg) {
            console.log(msg);
            if (msg == "login") {
                window.location = "veranstaltungen.html";
            }
            else {
                $('#alert').html('<div class="alert alert-danger"><strong>Error!</strong> Name oder Email falsch. Bitte überprüfen!</div>');
            }
        },
        error: function (xhr, ajaxOptions, thrownError) {
            console.log("ERROR" + xhr.responseText + thrownError);
        }
    });
});

$("#go").ready(function () {
    if ($(this)[0].title == "Jan's Planer") {
        $.ajax({
            url: url + "fn=logout"
        });
    }
    if ($(this)[0].title == "Jan's Planer Veranstaltungen") {
        $.ajax({
            url: url + "fn=getVeranstaltungen",
            type: "GET",
            success: function (msg) {
                if (msg == "no login") {
                    window.location = "index.html";
                }
                else {
                    $('#content').html(msg);

                    $(".info").click(function () {
                        var id = $(this).attr('id');
                        window.location = "info.html?iid=" + id;
                    });

                    $("#neu").click(function () {
                        window.location = "neu.html";
                    });
                }

            },
            beforeSend: function () {
                $('#content').html('<img style="text-align: center;" src="img/ajax-loader.gif" alt="lade" />');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
    }
});
$().ready(function () {
    if ($.urlParam('iid') != null && $(this)[0].title == "Jan's Planer Einladen") {
        $.ajax({
            url: url + "fn=getUsers",
            type: "POST",
            data: { 'vid': $.urlParam('iid') },
            success: function (msg) {
                console.log(msg);
                $('#content').html(msg);
                if (msg != "") {

                }
                if (msg == "no login") {
                    window.location = "index.html";
                }
            },
            beforeSend: function () {
                $('#content').html('<img style="text-align: center;" src="img/ajax-loader.gif" alt="lade" />');
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
        $("#einladen").click(function () {
            var useres = [];
            $('.usernames  label input').each(function () {
                var user
                if ($(this).prop('checked')) {
                    user = [this.value, true]
                    useres.push(user);
                }
                else {
                    user = [this.value, false]
                    useres.push(user);
                }
            });
            $.ajax({
                url: url + "fn=saveusers",
                type: "POST",
                data: { 'vid': $.urlParam('iid'), 'useres': useres },
                success: function (msg) {
                    console.log(msg);
                    $('#content').html(msg);
                    if (msg == "done") {
                        window.location = "info.html?iid=" + $.urlParam('iid');
                        loadinfo("Jan's Planer Info");
                    }
                    if (msg == "no login") {
                        window.location = "index.html";
                    }
                },
                beforeSend: function () {
                    $('#content').html('<img style="text-align: center;" src="img/ajax-loader.gif" alt="lade" />');
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log("ERROR" + xhr.responseText + thrownError);
                }
            });
        });
    }

    loadinfo($(this)[0].title);

    $("#neu").click(function () {
        $.ajax({
            url: url + "fn=createVer",
            type: "POST",
            data: { 'name': $('#name').val(), 'ort': $('#ort').val(), 'bild': $('#bild').val(), 'beschreibung': $('#beschreibung').val() },
            success: function (msg) {
                console.log(msg);
                if (msg != "null") {
                    window.location = "einladen.html?iid=" + msg;
                }
                else {
                    $('#alert').html('<div class="alert alert-danger"><strong>Error!</strong> Informationen fehlen. Bitte überprüfen!</div>');
                }
                if (msg == "no login") {
                    window.location = "index.html";
                }
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
    });
})

function loadinfo(titel) {
    if (titel == "Jan's Planer Info") {
        var iid = $.urlParam('iid');
        $.ajax({
            url: url + "fn=getVeranstaltungenInfos",
            type: "POST",
            data: { 'vid': iid },
            success: function (msg) {
                if (msg == "no login") {
                    window.location = "index.html";
                }
                $('#content').html(msg);

                var vid = $.urlParam('iid');
                $(".btn").click(function () {
                    var id = $(this).attr('id').split('|');
                    if (id[0] == 'Z') {
                        $.ajax({
                            url: url + "fn=zusagen",
                            type: "POST",
                            data: { 'vid': vid }
                        }).done(function (asd) {
                            console.log(asd);
                            loadinfo($(document)[0].title);
                        });
                    }
                    else if (id[0] == 'A') {
                        $.ajax({
                            url: url + "fn=absagen",
                            type: "POST",
                            data: { 'vid': vid }
                        }).done(function (asd) {
                            console.log(asd);
                            loadinfo($(document)[0].title);
                        });
                    }

                });


                $("button.btn").click(function () {
                    var el = "#" + $(this).attr('id') + "T";
                    if ($(el).attr('style') == 'display: none;') {
                        $(el).slideDown();
                    }
                    else {
                        $(el).slideUp();
                    }
                });
            },
            error: function (xhr, ajaxOptions, thrownError) {
                console.log("ERROR" + xhr.responseText + thrownError);
            }
        });
    }
}

$.urlParam = function (name) {
    var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
    if (results == null) {
        return null;
    }
    else {
        return results[1] || 0;
    }
}