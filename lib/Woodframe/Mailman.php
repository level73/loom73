<?php

namespace Loom73\Woodframe;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Mailman
{
    protected static PHPMailer $Mail;


    public static function sendMail(
        array $from,
        string $to,
        string $subject,
        string $body
    ): bool|Exception
    {

        self::$Mail = new PHPMailer(true);
        // Debug mode
        // $this->Mail->SMTPDebug  = 4;

        // SMTP Settings
        self::$Mail->isSMTP();
        self::$Mail->Host = $_SERVER['MAIL_SMTP_HOST'];
        self::$Mail->SMTPAuth = true;
        self::$Mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        self::$Mail->Username = $_SERVER['MAIL_SMTP_USERNAME'];
        self::$Mail->Password = $_SERVER['MAIL_SMTP_PASSWORD'];
        self::$Mail->Port = $_SERVER['MAIL_SMTP_PORT'];

        // Sender settings
        self::$Mail->setFrom($_SERVER['APPEMAIL'], $_SERVER['APPNAME']);

        self::$Mail->isHTML(true);
        self::$Mail->addAddress($to);
        self::$Mail->addReplyTo($from['email'], $from['name']);
        self::$Mail->Subject = $subject;
        self::$Mail->Body = $body;
        try {
            self::$Mail->send();
            return true;
        } catch (Exception $e) {
            Logger::error(
                'Mailman',
                'Email sending failed: ' . $e->getCode() . "::" . $e->getMessage()
            );
            return $e;
        }
    }
}