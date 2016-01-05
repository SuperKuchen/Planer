<?php

    if(isset($_GET['fn']))
    {
        switch($_GET["fn"])
        {
            case 'getUser':{
                getUser();
                break;
            }
            case 'getVeranstaltungen':{
                getVeranstaltungen();
                break;
            }
            case 'getVeranstaltungenInfos':{
                if(isset($_POST['vid']) && isset($_POST['uid']))
                    getVeranstaltungenInfos($_POST['vid'],$_POST['uid']);
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
    
    function getUser()
    {
        $conn = connect();
        $sql = "SELECT * FROM tbluser";
        $result = $conn->query($sql);
        $users = array();
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $users[] = array(
                    "id" => $row["ID"],
                    "Name" => $row["Name"],
                    "Email" => $row["Email"]
                );
            }
        } else {
            echo "0 results";
        }
        $conn->close();
        
        echo json_encode($users);
    }
    
    function getVeranstaltungen(){
        $conn = connect();
        $sql = "SELECT * FROM tblveranstaltungen";
        $result = $conn->query($sql);
        $html = "";
        if ($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                $html.= '<div class="row">';
                            if($row["Bild"] != null){
                                $html.='<div class="col-md-7">
                                            <a class="info" id="'.$row["ID"].'">
                                                <img class="img-responsive" src="'.$row["Bild"].'" alt="">
                                            </a>
                                        </div>';
                            }
                            
                            $html.= '<div class="col-md-5">
                                            <h3>'.$row["Name"].'</h3>
                                            <h4>Wo: '.$row["Ort"].'</h4>
                                            <p>'.$row["Beschreibung"].'</p>
                                            <a class="btn btn-primary info" id="'.$row["ID"].'">Mehr Infos <span class="glyphicon glyphicon-chevron-right"></span></a>
                                        </div>
                                    </div>
                                    <hr>';
            }
        } else {
            echo "0 results";
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
            $html.= '<div class="row">';
            if($row["Bild"] != null){
                $html.='<div class="col-md-7">
                                <img class="img-responsive" src="'.$row["Bild"].'" alt="">
                        </div>';
            }
            
            $html.= '<div class="col-md-5">
                            <h3>'.$row["Name"].'</h3>
                            <h4>Wo: '.$row["Ort"].'</h4>
                            <p>'.$row["Beschreibung"].'</p>
                        </div>
                    </div>
                    <hr>';
        } else {
            echo "0 results";
        }
        
        $html.= '<div class="col-md-5">
                <div class="btn-group btn-group" style="width:100%">
                    <a class="btn btn-primary" id="Z|'.$userid.'" style="width:50%">Zusagen</a>
                    <a class="btn btn-primary" id="A|'.$userid.'"style="width:50%">Absagen</a>
                </div></div> </br>';
        $html.= '<div class="col-md-5">
        <div class="btn-group-vertical" style="width:100%">
            <button type="button" class="btn btn-primary" id="Zugesagt">Bereits Zugesagt <span class="badge">'.zugesagt($verid).'</span></button>
            <button type="button" class="btn btn-primary" id="Abgesagt">Bereits Abgesagt <span class="badge">'.abgesagt($verid).'</span></button>
            <button type="button" class="btn btn-primary" id="Eingeladen">Eingeladen <span class="badge">'.eingeladen($verid).'</span></button>
        </div></div>';
        
        
        $conn->close();
        
        echo $html;          
    }
    
    function eingeladen($vid){
        $conn = connect();
        $sql = "SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage != NULL";
        $result = $conn->query($sql);
        if ($result->num_rows-1 > 0)
            $count = $result->num_rows-1;
        else
            $count = 0;
        $conn->close();
        return $count;   
    }
    
    function zugesagt($vid){
        $conn = connect();
        $sql = "SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage != 1";
        $result = $conn->query($sql);
        if ($result->num_rows-1 > 0)
            $count = $result->num_rows-1;
        else
            $count = 0;
        $conn->close();
        return $count;   
    }
    
    function abgesagt($vid){
        $conn = connect();
        $sql = "SELECT COUNT(*) FROM tbluserveranstaltungen WHERE fkveranstaltungenid = ".$vid." AND zusage != 0";
        $result = $conn->query($sql);
        if ($result->num_rows-1 > 0)
            $count = $result->num_rows-1;
        else
            $count = 0;
        $conn->close();
        return $count;   
    }

    function zusagen($vid,$uid){
        $conn = connect();
        $sql = "UPDATE tbluserveranstaltungen SET zusage = 1 WHERE fkveranstaltungenid = ".$vid." AND fkuserid = ". $uid;
        $result = $conn->query($sql);
        if ($result->num_rows-1 > 0)
            $count = $result->num_rows-1;
        else
            $count = 0;
        $conn->close();
        return $count;   
    }
    
    function absagen($vid,$uid){
        $conn = connect();
        $sql = "UPDATE tbluserveranstaltungen SET zusage = 0 WHERE fkveranstaltungenid = ".$vid." AND fkuserid = ". $uid;
        $result = $conn->query($sql);
        if ($result->num_rows-1 > 0)
            $count = $result->num_rows-1;
        else
            $count = 0;
        $conn->close();
        return $count;   
    }   
?>