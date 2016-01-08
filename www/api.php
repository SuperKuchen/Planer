<?php

session_start();

    if(isset($_GET['fn']))
    {
        switch($_GET["fn"])
        {
            case 'getUser':{
                session_destroy();
                session_start();
                if(isset($_POST['Name']) && isset($_POST['Email']))
                    getUser($_POST['Name'],$_POST['Email']);
                break;
            }
            case 'getVeranstaltungen':{
                if(isset($_SESSION['user']['id']))
                    getVeranstaltungen($_SESSION['user']['id']);
                break;
            }
            case 'getVeranstaltungenInfos':{
                if(isset($_POST['vid']) && isset($_SESSION['user']['id']))
                    getVeranstaltungenInfos($_POST['vid'],$_SESSION['user']['id']);
                break;
            }
            case 'zusagen':{
                if(isset($_POST['vid']) && isset($_SESSION['user']['id']))
                    zusagen($_POST['vid'],$_SESSION['user']['id']);
                break;
            }
            case 'absagen':{
                if(isset($_POST['vid']) && isset($_SESSION['user']['id']))
                    absagen($_POST['vid'],$_SESSION['user']['id']);
                break;
            }
        }
    }

    function connect()
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "planer";

        // Create connection
        $conn = new mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        return $conn;
    }
    
    function getUser($name, $email)
    {
        $conn = connect();
        $sql = "SELECT * FROM tbluser WHERE Name = '".$name."' AND Email = '".$email."';";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $user = array(
                "id" => $row["ID"],
                "Name" => $row["Name"],
                "Email" => $row["Email"]
            );
            $_SESSION['user'] = $user;
            echo "login";
        } else {
            echo "null";
        }
        $conn->close();
    }
    
    function getVeranstaltungen($uid){
        $conn = connect();
        $sql = "SELECT ID,'Name',Ort,Bild,Beschreibung FROM tblveranstaltungen JOIN tbluserveranstaltungen ON ID = fkveranstaltungenid WHERE fkuserid = ".$uid;
        $result = $conn->query($sql);
        $html = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $html.= '<div class="row">';
                            if($row["Bild"] != null){
                                $html.='<div class="col-md-12">
                                            <a class="info" id="'.$row["ID"].'">
                                                <img class="img-responsive" src="'.$row["Bild"].'" alt="">
                                            </a>
                                        </div>';
                            }
                            
                            $html.= '<div class="col-md-12">
                                            <h3>'.$row["Name"].'</h3>
                                            <h4>Wo: '.$row["Ort"].'</h4>
                                            <p>'.$row["Beschreibung"].'</p>
                                            <a class="btn btn-primary info" id="'.$row["ID"].'">Mehr Infos <span class="glyphicon glyphicon-chevron-right"></span></a>
                                        </div>
                                    </div>
                                    <hr>';
            }
        } else {
            echo '<div class="alert alert-info"><strong>Info!</strong> Du hast keine Veranstaltungen</div>';
        }
        $conn->close();
        
        echo $html;        
    }
    
    function getVeranstaltungenInfos($verid, $userid)
    {
        $conn = connect();
        $sql = "SELECT * FROM tblveranstaltungen WHERE ID = ". $verid;
        $result = $conn->query($sql);
        $html = "";
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $html.= '';
            if($row["Bild"] != null){
                $html.='<div class="col-md-7">
                                <img class="img-responsive" src="'.$row["Bild"].'" alt="">
                        </div>';
            }
            
            $html.= '<div class="col-md-12">
                            <h3>'.$row["Name"].'</h3>
                            <h4>Wo: '.$row["Ort"].'</h4>
                            <p>'.$row["Beschreibung"].'</p>
                        </div>
                    <hr>';
        } else {
            echo "0 results";
        }
        
        $html.= '<div class="col-md-12">
                <div class="btn-group btn-group" style="width:100%">
                    <a class="btn btn-primary" id="Z|'.$userid.'" style="width:50%">Zusagen</a>
                    <a class="btn btn-primary" id="A|'.$userid.'"style="width:50%">Absagen</a>
                </div></div> </br>';
        $html.= '<div class="col-md-12">
        <div class="btn-group-vertical" style="width:100%">
            <button type="button" class="btn btn-primary" id="Zugesagt">Bereits Zugesagt <span class="badge">'.zugesagt($verid).'</span></button>
            <div id="ZugesagtT" style="display: none;" class=" table-striped">'.zugesagtUser($verid).'</div>
            <button type="button" class="btn btn-primary" id="Abgesagt">Bereits Abgesagt <span class="badge">'.abgesagt($verid).'</span></button>
            <div id="AbgesagtT" style="display: none;" class=" table-striped">'.abgesagtUser($verid).'</div>            
            <button type="button" class="btn btn-primary" id="Eingeladen">Eingeladen <span class="badge">'.eingeladen($verid).'</span></button>
            <div id="EingeladenT" style="display: none;" class=" table-striped">'.eingeladenUser($verid).'</div>
        </div></div>';
        
        
        $conn->close();
        
        echo $html;          
    }
    
    function eingeladen($vid){
        $conn = connect();
        $sql = "SELECT COUNT(*) FROM `tbluserveranstaltungen` WHERE fkveranstaltungenid = ".$vid." AND zusage is NULL";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $count = $row['COUNT(*)'];
        $conn->close();
        return $count;   
    }
    
    function zugesagt($vid){
        $conn = connect();
        $sql = "SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage = 1";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $count = $row['COUNT(*)'];
        $conn->close();
        return $count;   
    }
    
    function abgesagt($vid){
        $conn = connect();
        $sql = "SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage = 0";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $count = $row['COUNT(*)'];
        $conn->close();
        return $count;   
    }

    function zusagen($vid,$uid){
        $conn = connect();
        $sql = "UPDATE tbluserveranstaltungen SET zusage = 1 WHERE fkveranstaltungenid = ".$vid." AND fkuserid = ". $uid;
        $conn->query($sql);
        $conn->close();
    }
    
    function absagen($vid,$uid){
        $conn = connect();
        $sql = "UPDATE tbluserveranstaltungen SET zusage = 0 WHERE fkveranstaltungenid = ".$vid." AND fkuserid = ". $uid;
        $conn->query($sql);
        $conn->close();
    }
    
    function eingeladenUser($vid){
        $conn = connect();
        $sql = "SELECT `Name` FROM tbluser JOIN tbluserveranstaltungen ON ID = fkuserid WHERE fkveranstaltungenid = ".$vid." AND zusage is NULL";
        $result = $conn->query($sql);
        $html = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $html.="<p class='unterbutton'>".$row["Name"]."</p>";
            }
        } else {
            $html = "<p class='unterbutton'>Keine</p>";
        }
        $conn->close();

        return $html;   
    }
    
    function zugesagtUser($vid){
        $conn = connect();
        $sql = "SELECT `Name` FROM tbluser JOIN tbluserveranstaltungen ON ID = fkuserid WHERE fkveranstaltungenid = ".$vid." AND zusage = 1";
        $result = $conn->query($sql);
        $html = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $html.="<p class='unterbutton'>".$row["Name"]."</p>";
            }
        } else {
            $html = "<p class='unterbutton'>Keine</p>";
        }
        $conn->close();
        return $html;   
    }
    
    function abgesagtUser($vid){
        $conn = connect();
        $sql = "SELECT `Name` FROM tbluser JOIN tbluserveranstaltungen ON ID = fkuserid WHERE fkveranstaltungenid = ".$vid." AND zusage = 0";
        $result = $conn->query($sql);
        $html = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $html.="<p class='unterbutton'>".$row["Name"]."</p>";
            }
        } else {
            $html = "<p class='unterbutton'>Keine</p>";
        }
        $conn->close();
        return $html;   
    }    
?>