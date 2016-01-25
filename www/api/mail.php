<?php
function sendMail($to,$key){
    $subject = "Regestierung abschließen";
    $txt = "Klicke <a href='beckmannjan.de/api/active.php?key=$key'>hier</a> um deinen Account zu aktivieren </br>";
    $headers = "From: info@beckmannjan.de";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    mail($to,$subject,$txt,$headers);
}
?>