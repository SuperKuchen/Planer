<?php

    if(isset($_GET['mail']) && isset($_GET['key'])){
        
        $conn = connect();

        if (!($stmt = $conn->prepare("SELECT Active FROM tbluser WHERE Email = '".$_GET['mail']."' AND tbluser.key = ".$_GET['key']))) {
            echo "Prepare failed: (".$conn->errno.") ".$conn->error;
        }
        if (!$stmt->execute()) {
            echo "Execute failed: (".$conn->errno.") ".$conn->error;
        }

        if (!$stmt->bind_result($out_active)) {
            echo "Binding output parameters failed: (".$stmt->errno.") ".$stmt->error;
        }

        while ($stmt->fetch()) {
        }
        
        if(!$out_active){

            if (!($stmt = $conn->prepare("UPDATE tbluser SET Active = ? WHERE Email = '".$_GET['mail']."' AND tbluser.key = ".$_GET['key']))) {
                echo "Prepare failed: (".$conn->errno.") ".$conn->error;
            }
            $stmt->bind_param('i', $ja);
            $ja = 1;
            if (!$stmt->execute()) {
                echo "Execute failed: (".$conn->errno.") ".$conn->error;
            }
        }
        $stmt->close();
    }
    header('Location: http://www.beckmannjan.de/');
    
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

?>