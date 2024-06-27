<?php

namespace Drupal\iscrizione_premio_arte\Objects;


use Drupal\iscrizione_premio_arte\PhpMailer\PHPMailer;
use Drupal\iscrizione_premio_arte\Utility\ErrorCodeMessage;
use Symfony\Component\HttpFoundation\Request;
/**
 * Class SendEmailWorkWithUs
 * @package Drupal\iscrizione_premio_arte\Objects
 */
class SendEmailWorkWithUs {
    const EMAIL_TO_ADDRESS = 'recruiting@cairocommunication.it';
    const EMAIL_TO_NAME = 'Recruiting Cairocommunication';
    const EMAIL_FROM_ADDRESS = 'svr@cairocommunication.it';
    const EMAIL_FROM_NAME = 'Cairo Editore';
    const FIRST_PART_SUBJ = 'WORK WITH US';

    public static function send(Request $request)
    {   $response = array();
        $data_prepared = new PrepareDataForEmail($request);
        if($data_prepared->is_valid_captcha){
            $email_content = $data_prepared->getEmailContent();
            if($email_content->is_valid_content){
                $response['code'] =   self::sendEmail($email_content) ? 200 : 400;
                $response['result'] = $response['code'] === 200 ? 'success' : 'false';
            } else {
                $response['result']='error';
                $message = '';
                if(is_array( $email_content->invalid_input)){
                    foreach ($email_content->invalid_input as $act_message){
                        $message .= $act_message;
                    }
                }
                $response['message'] = $message;
                $response['code'] = 400;
            }
        } else {
            $response['result']='error';
            $response['message'] = ErrorCodeMessage::getMessageByCode(1);
            $response['code'] = 400;
        }

        return $response;
    }

    public static function sendEmail(EmailContent $email_content)
    {
        // Message
        $message = '
            <html>
            <head>
              <title>CairoCommunication</title>
            </head>
            <body>
              <p>' . $email_content->getSubject() . '</p>
              <table>
                <tr>
                    <th>Nome</th>
                  <td>' . $email_content->getName() . '</td>
                </tr>
                <tr>
                    <th>Cognome</th>
                  <td>' . $email_content->getSurname() . '</td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td>' . $email_content->getEmail() . '</td>
                </tr>
                <tr>
                  <th>Indirizzo</th>
                  <td>' . $email_content->getAddress() . '</td>
                </tr>
                <tr>
                  <th>Cap</th>
                  <td>' . $email_content->getZipCode() . '</td>
                </tr>
                <tr>
                  <th>Città</th>
                  <td>' . $email_content->getCity() . '</td>
                </tr>
                <tr>
                  <th>Numero di telefono</th>
                  <td>' . $email_content->getMobilePhone() . '</td>
                </tr>
                <tr>
                  <th>Consenso Privacy</th>
                  <td>' . $email_content->getConsensoPrivacy() . '</td>
                </tr>
                <tr>
                </tr>
              </table>
              <p><strong>Presentazione:</p>
              <p>' . $email_content->getPresentation() . '</p>
            </body>
            </html>
            ';

        try {
            $email = new PHPMailer();
            $email->isSMTP();                                      // Set mailer to use SMTP
            $email->Host = 'mail.iltrovatore.it';  // Specify main and backup SMTP servers
            $email->SMTPAuth = true;                               // Enable SMTP authentication
            $email->Username = 'svr@cairocommunication.it';    //SMTP username
            $email->Password = '2bE988a7fb';  // SMTP password
            $email->SMTPSecure = '';
            $email->SMTPAutoTLS = false;                     // Enable TLS encryption, `ssl` also accepted
            $email->Port = 25;
            $email->CharSet = 'UTF-8';
            $email->SetFrom(self::EMAIL_FROM_ADDRESS, self::EMAIL_FROM_NAME);
            $email->Subject = $email_content->getSubject();
            $email->Body = $message;
            $email->IsHTML(true);
            $email->AddCC($email_content->getEmail());
            //$email->AddAddress(self::EMAIL_TO_ADDRESS,self::EMAIL_TO_NAME);
            $email->AddAddress($email_content->getEmail(),$email_content->getName() . ' '.$email_content->getSurname());
            if ($email_content->getCvName()) {
                $file_to_attach = $email_content->getCvPath();
                $email->AddAttachment($file_to_attach, $email_content->getCvName());
            }

            $email->Send();
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }
}
