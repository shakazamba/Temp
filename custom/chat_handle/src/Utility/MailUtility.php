<?php
/**
 * Created by PhpStorm.
 * User: ilansimonetti
 * Date: 2019-02-20
 * Time: 16:31
 */

namespace Drupal\iscrizione_premio_arte\Utility;


use Drupal\iscrizione_premio_arte\PhpMailer\PHPMailer;

class MailUtility
{

    public static function send($configuration)
    {


        try {
            $email = new PHPMailer();
            $email->isSMTP();
            $email->Host = $configuration['host'];
            $email->SMTPAuth = true;
            $email->Username = $configuration['username'];
            $email->Password = $configuration['password'];
            $email->SMTPSecure = '';
            $email->SMTPAutoTLS = false;
            $email->Port = 25;
            $email->CharSet = 'UTF-8';
            $email->SetFrom($configuration['email_from'], $configuration['name_from']);
            $email->Subject = $configuration['subject'];
            $email->Body = $configuration['body'];
            $email->IsHTML($configuration['is_html']);
            if (isset($configuration['cc']))
                $email->AddCC($configuration['cc']);
            $email->AddAddress($configuration['email_to'], $configuration['name_to']);
            if (isset($configuration['file_path'])) {
                $file_to_attach = $configuration['file_path'];
                if (is_array($file_to_attach)) {
                    foreach ($file_to_attach as $k => $file_path) {
                        $name = isset($configuration['file_name'][$k]) && $configuration['file_name'][$k] ? $configuration['file_name'][$k] : basename($file_path);
                        $email->AddAttachment($file_path, $name);
                    }
                } else {
                    $email->AddAttachment($file_to_attach, $configuration['file_name']);
                }
            }

            $email->Send();
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}
