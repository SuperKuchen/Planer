<?php
function sendMail($email,$key){
    
    $link= '<a href="http://www.beckmannjan.de/api/active.php?mail='.$email.'&key='.$key.'">Hier</a>';
    $to = $email;
    $subject = "Regestierung abschließen";
    $txt = "Klicke $link um deinen Account zu aktivieren";
    $headers = "From: noreply@beckmannjan.de\n";
    $headers.= "MIME-version: 1.0\n";
    $headers.= "Content-type: text/html; charset= iso-8859-1\n";

    mail($to,$subject,$txt,$headers);
}
?>