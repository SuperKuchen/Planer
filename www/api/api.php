<?php 

include_once('mail.php');
session_start();

if (isset($_GET['fn'])) {
    switch ($_GET["fn"]) {
        case 'getUser':
            {
                session_destroy();
                session_start();
                if (isset($_POST['passwort']) && isset($_POST['Email'])) {
                    $pass = md5($_POST['passwort']);
                    getUser($_POST['Email'], $pass);
                }
                exit;
                break;
            }
        case 'getVeranstaltungen':
            {
                if (isset($_SESSION['user']['id'])) getVeranstaltungen($_SESSION['user']['id']);
                exit;
                break;
            }
        case 'getVeranstaltungenInfos':
            {
                if (isset($_POST['vid']) && isset($_SESSION['user']['id'])) getVeranstaltungenInfos($_POST['vid'], $_SESSION['user']['id']);
                exit;
                break;
            }
        case 'zusagen':
            {
                if (isset($_POST['vid']) && isset($_SESSION['user']['id'])) zusagen($_POST['vid'], $_SESSION['user']['id']);
                exit;
                break;
            }
        case 'absagen':
            {
                if (isset($_POST['vid']) && isset($_SESSION['user']['id'])) absagen($_POST['vid'], $_SESSION['user']['id']);
                exit;
                break;
            }
        case 'createVer':
            {
                if (isset($_POST['name']) && $_SESSION['user']['id'] == 1) {
                    createVer($_POST['name'], $_POST['ort'], $_POST['bild'], $_POST['beschreibung']);
                }
                exit;
                break;
            }
        case 'getUsers':
            {
                if (isset($_POST['vid'])) {
                    getUsers($_POST['vid']);
                }
                exit;
                break;
            }
        case 'saveusers':
            {
                if (isset($_POST['vid']) && isset($_POST['useres'])) {
                    saveusers($_POST['vid'], $_POST['useres']);
                }
                exit;
                break;
            }
        case 'newUser':
            {
                if (isset($_POST['Vorname']) && isset($_POST['Nachname']) && isset($_POST['Email']) && isset($_POST['Password'])) {
                    if (isset($_POST['Adresse'])) {
                        $adresse = $_POST['Adresse'];
                    } else {
                        $adresse = null;
                    }
                    if ($_POST['Vorname'] != "" && $_POST['Nachname'] != "" && $_POST['Email'] != "" && $_POST['Password'] != "") {
                        $pass = md5($_POST['Password']);
                        newusers($_POST['Vorname'], $_POST['Nachname'], $adresse, $_POST['Email'], $pass);
                    }
                }
                exit;
                break;
            }
        case 'logout':
            {
                session_destroy();
                exit;
                break;
            }
    }
    if (!isset($_SESSION['user']['id'])) {
        echo "no login";
    }
}

function connect() {
    $servername = "212.144.99.249:3306";
    $username = "qrrgp_planer";
    $password = "Izw05%d2";
    $dbname = "planer";

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_errno) {
        echo "Failed to connect to MySQL: (".$conn->connect_errno.") ".$conn->connect_error;
    }
    return $conn;
}

function getUser($email, $password) {
    $conn = connect();

    if (!($stmt = $conn->prepare("SELECT * FROM tbluser WHERE Email = '".$email."' AND Password = '".$password."' AND Active = 1"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    $out_id = NULL;
    $out_name = NULL;
    $out_email = NULL;
    $out_active = NULL;
    $out_key = NULL;
    if (!$stmt->bind_result($out_id, $out_vorname, $out_name, $out_adresse, $out_email, $out_passwort,$out_active,$out_key)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }

    while ($stmt->fetch()) {
        $user = array("id" => $out_id, "Vorname" => $out_vorname, "Name" => $out_name, "Adresse" => $out_adresse, "Email" => $out_email, "password" => $out_passwort);
        $_SESSION['user'] = $user;
        echo "login";
    }
    if (!isset($user)) {
        echo "null";
    }
    $stmt->close();
}

function newusers($vorname, $nachname, $adresse, $email, $password) {
    $conn = connect();

    if (!($stmt = $conn->prepare("SELECT Email FROM tbluser WHERE Email = '".$email."'"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    $out_email = NULL;
    if (!$stmt->bind_result($out_id)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }

    while ($stmt->fetch()) {
        echo "Email";
        exit;
    }

    $key = rand();

    sendMail($email, $key);

    if (!($stmt = $conn->prepare("INSERT INTO `tbluser` VALUES (?,?,?,?,?,?,?,?)"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    
    $active = 0;

    $stmt->bind_param('isssssii', $id, $vorname, $nachname, $adresse, $email, $password, $active, $key);
    $id = null;

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    echo "done";

}

function saveusers($verid, $useres) {
    $conn = connect();
    if (!($stmt = $conn->prepare("INSERT IGNORE INTO `tbluserveranstaltungen`(`fkuserid`, `fkveranstaltungenid`, `zusage`) VALUES (?,?,?)"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    foreach($useres as $key => $value) {
        if ($value[1] == 'true') {
            $stmt->bind_param('iii', $fkuserid, $fkveranstaltungenid, $zusaget);
            $fkuserid = $value[0];
            $fkveranstaltungenid = $verid;
            $zusage = 0;

            if (!$stmt->execute()) {
                echo "Execute failed: (".$conn->errno.") ".$conn->error;
            }
        }
    }

    if (!($stmt = $conn->prepare("DELETE FROM `tbluserveranstaltungen` WHERE `fkuserid` = ? AND `fkveranstaltungenid` = ?"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    foreach($useres as $key => $value) {
        if ($value[1] == 'false') {
            $stmt->bind_param('ii', $value[0], $verid);

            if (!$stmt->execute()) {
                echo "Execute failed: (".$conn->errno.") ".$conn->error;
            }
        }
    }

    echo "done";

}

function getUsers($verid) {
    $conn = connect();

    $html = '<div class="col-md-12">';

    if (!($stmt = $conn->prepare("SELECT `ID`, `Vorname`, `Name` FROM `tbluser` WHERE `ID` NOT IN (SELECT `fkuserid` FROM `tbluserveranstaltungen` WHERE `fkveranstaltungenid` = $verid);"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    $out_id = NULL;
    $out_name = NULL;
    $out_vorname = NULL;
    if (!$stmt->bind_result($out_id, $out_vorname, $out_name)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }

    while ($stmt->fetch()) {
        $html .= '<div class="checkbox usernames">
                     <label><input type="checkbox" value="'.$out_id.'">'.$out_vorname." ".$out_name.'</label>
                 </div>';
    }

    if (!($stmt = $conn->prepare("SELECT `ID`, `Vorname`, `Name` FROM `tbluser` WHERE `ID`  IN (SELECT `fkuserid` FROM `tbluserveranstaltungen` WHERE `fkveranstaltungenid` = $verid);"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    $out_id = NULL;
    $out_name = NULL;
    $out_vorname = NULL;
    if (!$stmt->bind_result($out_id, $out_vorname, $out_name)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }

    while ($stmt->fetch()) {
        $html .= '<div class="checkbox usernames">
                     <label><input type="checkbox" value="'.$out_id.'" checked>'.$out_vorname." ".$out_name.'</label>
                 </div>';
    }

    $stmt->close();

    echo '</div>'.$html;
}

function createVer($uename, $ueort, $uebild, $uebeschreibung) {
    if ($uename != '' && $ueort != '' && $uebeschreibung != '') {
        $conn = connect();
        if (!($stmt = $conn->prepare("INSERT INTO `tblveranstaltungen`(`ID`, `Name`, `Ort`, `Bild`, `Beschreibung`) VALUES (?,?,?,?,?)"))) {
            echo "Prepare failed: (".$conn->errno.") ".$conn->error;
        }
        $stmt->bind_param('issss', $id, $name, $ort, $bild, $beschreibung);
        $id = NULL;
        $name = $uename;
        $ort = $ueort;
        $bild = $uebild;
        $beschreibung = $uebeschreibung;
        if (!$stmt->execute()) {
            echo "Execute failed: (".$conn->errno.") ".$conn->error;
        }
        echo $lastid = $stmt->insert_id;

        if (!($stmt = $conn->prepare("INSERT INTO `tbluserveranstaltungen`(`fkuserid`, `fkveranstaltungenid`, `zusage`) VALUES (?,?,?)"))) {
            echo "Prepare failed: (".$conn->errno.") ".$conn->error;
        }
        $stmt->bind_param('iii', $fkuserid, $fkveranstaltungenid, $zusage);
        $fkuserid = 1;
        $fkveranstaltungenid = $lastid;
        $zusage = 1;
        if (!$stmt->execute()) {
            echo "Execute failed: (".$conn->errno.") ".$conn->error;
        }

        $stmt->close();
    } else {
        echo 'null';
    }
}

function getVeranstaltungen($uid) {
    if ($uid == 1) {
        $html = '<div class="row"><div class="col-md-12"><button style="width:100%" type="button" class="btn btn-primary" id="neu">Neu</button></div></div><hr>';
    } else {
        $html = '';
    }

    $conn = connect();
    if (!($stmt = $conn->prepare("SELECT ID,`Name`,Ort,Bild,Beschreibung FROM tblveranstaltungen JOIN tbluserveranstaltungen ON ID = fkveranstaltungenid WHERE fkuserid = ".$uid." ORDER BY ID DESC"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    $out_id = NULL;
    $out_name = NULL;
    $out_ort = NULL;
    $out_bild = NULL;
    $out_beschreibung = NULL;
    if (!$stmt->bind_result($out_id, $out_name, $out_ort, $out_bild, $out_beschreibung)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }

    while ($stmt->fetch()) {
        $html .= '<div class="row">';
        if ($out_bild != null) {
            $html .= '<div class="col-md-12">
                            <a class="info" id="'.$out_id.'">
                                <img class="img-responsive" src="'.$out_bild.'" alt="">
                            </a>
                        </div>';
        }

        $html .= '<div class="col-md-12">
                            <h3>'.$out_name.'</h3>
                            <h4>Wo: '.$out_ort.'</h4>
                            <p>'.$out_beschreibung.'</p>
                            <a class="btn btn-primary info" id="'.$out_id.'">Mehr Infos <span class="glyphicon glyphicon-chevron-right"></span></a>
                        </div>
                    </div>
                    <hr>';
    }
    if ($html == '' || $html == '<div class="row"><div class="col-md-12"><button style="width:100%" type="button" class="btn btn-primary" id="neu">Neu</button></div></div><hr>') {
        echo '<div class="alert alert-info"><strong>Info!</strong> Du hast keine Veranstaltungen</div>';
    }

    echo $html;
    $stmt->close();
}

function getVeranstaltungenInfos($verid, $userid) {
    $conn = connect();

    if (!($stmt = $conn->prepare("SELECT * FROM tblveranstaltungen WHERE ID = ".$verid))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }

    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }

    $out_id = NULL;
    $out_name = NULL;
    $out_ort = NULL;
    $out_bild = NULL;
    $out_beschreibung = NULL;
    if (!$stmt->bind_result($out_id, $out_name, $out_ort, $out_bild, $out_beschreibung)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }

    while ($stmt->fetch()) {
        $html = '';
        if ($out_bild != null) {
            $html .= '<div class="col-md-12">
                                <img class="img-responsive" src="'.$out_bild.'" alt="">
                        </div>';
        }

        $html .= '<div class="col-md-12">
                            <h3>'.$out_name.'</h3>
                            <h4>Wo: '.$out_ort.'</h4>
                            <p>'.$out_beschreibung.'</p>
                        </div>
                    <hr>';
        $html .= '<div class="col-md-12">
                    <div class="btn-group btn-group" style="width:100%">
                        <a class="btn btn-primary" id="Z|'.$userid.'" style="width:50%">Zusagen</a>
                        <a class="btn btn-primary" id="A|'.$userid.'"style="width:50%">Absagen</a>
                    </div></div> </br>';
        $html .= '<div class="col-md-12">
            <div class="btn-group-vertical" style="width:100%">
                <button type="button" class="btn btn-primary" id="Zugesagt">Bereits Zugesagt <span class="badge">'.zugesagt($verid).'</span></button>
                <div id="ZugesagtT" style="display: none;" class=" table-striped">'.zugesagtUser($verid).'</div>
                <button type="button" class="btn btn-primary" id="Abgesagt">Bereits Abgesagt <span class="badge">'.abgesagt($verid).'</span></button>
                <div id="AbgesagtT" style="display: none;" class=" table-striped">'.abgesagtUser($verid).'</div>            
                <button type="button" class="btn btn-primary" id="Eingeladen">Eingeladen <span class="badge">'.eingeladen($verid).'</span></button>
                <div id="EingeladenT" style="display: none;" class=" table-striped">'.eingeladenUser($verid).'</div>
            </div>';
        if($userid == 1){
            $html .= '<form action="einladen.html" method="get" >
                        <input type="hidden" name="iid" value='.$verid.'>
                        <button type="submit" class="btn btn-primary" style="width:100%">Eingeladen/Ausladen</button>
                    </form>
                </div>';
        }
    }
    if (!isset($html)) {
        echo "0 results";
    }

    echo $html;
    $stmt->close();
}

function eingeladen($vid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("SELECT COUNT(*) FROM `tbluserveranstaltungen` WHERE fkveranstaltungenid = ".$vid." AND zusage is NULL"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $out_COUNT = NULL;
    if (!$stmt->bind_result($out_COUNT)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }
    while ($stmt->fetch()) {
        return $out_COUNT;
    }
    $stmt->close();
}

function zugesagt($vid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage = 1"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $out_COUNT = NULL;
    if (!$stmt->bind_result($out_COUNT)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }
    while ($stmt->fetch()) {
        return $out_COUNT;
    }
    $stmt->close();
}

function abgesagt($vid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage = 0"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $out_COUNT = NULL;
    if (!$stmt->bind_result($out_COUNT)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }
    while ($stmt->fetch()) {
        return $out_COUNT;
    }
    $stmt->close();
}

function zusagen($vid, $uid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("UPDATE tbluserveranstaltungen SET zusage = ? WHERE fkveranstaltungenid = ".$vid." AND fkuserid = ".$uid))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    $stmt->bind_param('i', $ja);
    $ja = 1;
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $stmt->close();
}

function absagen($vid, $uid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("UPDATE tbluserveranstaltungen SET zusage = ? WHERE fkveranstaltungenid = ".$vid." AND fkuserid = ".$uid))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    $stmt->bind_param('i', $nein);
    $nein = 0;
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $stmt->close();
}

function eingeladenUser($vid) {
    $conn = connect();

    if (!($stmt = $conn->prepare("SELECT `Vorname`, `Name` FROM tbluser JOIN tbluserveranstaltungen ON ID = fkuserid WHERE fkveranstaltungenid = ".$vid." AND zusage is NULL"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $out_name = NULL;
    if (!$stmt->bind_result($out_vorname, $out_name)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }
    $html = "";
    while ($stmt->fetch()) {
        $html .= "<p class='unterbutton'>".$out_vorname." ".$out_name."</p>";
    }
    if ($html == "") {
        $html = "<p class='unterbutton'>Keine</p>";
    }

    $stmt->close();
    return $html;
}

function zugesagtUser($vid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("SELECT `Vorname`, `Name` FROM tbluser JOIN tbluserveranstaltungen ON ID = fkuserid WHERE fkveranstaltungenid = ".$vid." AND zusage = 1"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $out_name = NULL;
    if (!$stmt->bind_result($out_vorname, $out_name)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }
    $html = "";
    while ($stmt->fetch()) {
        $html .= "<p class='unterbutton'>".$out_vorname." ".$out_name."</p>";
    }
    if ($html == "") {
        $html = "<p class='unterbutton'>Keine</p>";
    }
    $stmt->close();
    return $html;
}

function abgesagtUser($vid) {
    $conn = connect();
    if (!($stmt = $conn->prepare("SELECT `Vorname`, `Name` FROM tbluser JOIN tbluserveranstaltungen ON ID = fkuserid WHERE fkveranstaltungenid = ".$vid." AND zusage = 0"))) {
        echo "Prepare failed: (".$conn->errno.") ".$conn->error;
    }
    if (!$stmt->execute()) {
        echo "Execute failed: (".$conn->errno.") ".$conn->error;
    }
    $out_name = NULL;
    if (!$stmt->bind_result($out_vorname, $out_name)) {
        echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
    }
    $html = "";
    while ($stmt->fetch()) {
        $html .= "<p class='unterbutton'>".$out_vorname." ".$out_name."</p>";
    }
    if ($html == "") {
        $html = "<p class='unterbutton'>Keine</p>";
    }
    $stmt->close();
    return $html;
}
?>