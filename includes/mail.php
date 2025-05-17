<?php
function sendMail($to, $subject, $message) {
    $headers = "From: no-reply@lostfound.com\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    return mail($to, $subject, $message, $headers);
}