<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

function sendOTP($email, $otp) {

    $mail = new PHPMailer(true);

    try {
      
        $mail->SMTPDebug = 0;
        $mail->Debugoutput = 'html';

       
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'karlouispineda@gmail.com'; 
        $mail->Password   = 'egbo blxs abwm wpeo ';       
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        
        $mail->Timeout = 60;

      
        $mail->setFrom('karlouispineda@gmail.com', 'OTP Request');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "
            <h3>Your OTP Code is:</h3>
            <h1>$otp</h1>
            <p>Expires in 5 minutes.</p>
        ";

        $mail->send();
        return true;

    } catch (Exception $e) {
        echo "<h3>MAILER ERROR:</h3>";
        echo $mail->ErrorInfo;
        return false;
    }
}
?>