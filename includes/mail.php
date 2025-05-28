<?php
function sendMail($to, $subject, $message) {
    // Sender email address (should match your domain)
    $headers = "From: no-reply@lostfound.com\r\n";
    $headers .= "Reply-To: support@lostfound.com\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    // Send mail
    return mail($to, $subject, $message, $headers);
}
?>