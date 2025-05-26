<?php
function sendMail($to, $subject, $message) {
    // Gönderen e-posta adresi (alan adınızla uyumlu olmalı)
    $headers = "From: no-reply@lostfound.com\r\n";
    $headers .= "Reply-To: support@lostfound.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Mail gönderimi
    return mail($to, $subject, $message, $headers);
}