<?php


namespace Drupal\iscrizione_premio_arte\Objects;
use Drupal\iscrizione_premio_arte\PhpMailer\PHPMailer;
use Symfony\Component\HttpFoundation\Request;

class PrepareDataForEmailAssemblea extends PrepareDataForEmail
{
    public $name;
    public $surname;
    public $email;
    public $consenso_privacy;
    public $mobile_phone;
    public $presa_visione_istruzioni;
    public $delega;
    public $delega_path;
    public $attestazione;
    public $attestazione_path;
    public $allowed_extension = array('pdf','jpg','jpeg');
    public $done = false;

    const EMAIL_TO_ADDRESS = 'simonetti.ilan.feltri@gmail.com';
    const EMAIL_TO_NAME = 'Ilan Simonetti';
    const EMAIL_FROM_ADDRESS = 'svr@cairocommunication.it';
    const EMAIL_FROM_NAME = 'Cairo Editore';
    const TITOLO_ASSEMBLEA = 'DOCUMENTI ASSEMBLEA';

    public function  __construct(Request $request)
    {

        if($this->is_valid_captcha_check($this->getCaptcha($request))) {
            $this->is_valid_captcha = true;
            if($this->populate_if_is_valid_input($request)){
                if($this->sendEmail()){
                    $this->done = true;
                }
            }

        }

    }

    public function populate_if_is_valid_input(Request $request)
    {

        //Required

        $name = $request->get('name');
        $surname = $request->get('surname');
        $email = $request->get('email');
        $privacy = $request->get('privacy');
        $mobile_phone = $request->get('mobile_phone');
        $presa_visione_istruzioni = $request->get('preva_vis_istr');

        // Not Required
        $delega = $_FILES["delega"]['name'];
        $attestazione = $_FILES["attestazione"]['name'];



        //Required check
        if ((!($name !== '' && $surname !== '' && $email !== '' && $mobile_phone !== '')) || ($privacy !== 'acconsento' && $presa_visione_istruzioni !== 'presa-visione-istruzioni')) {
            return false;
        } else {

            $this->name = $name;
            $this->surname = $surname;
            $this->email = $email;
            $this->mobile_phone = $mobile_phone;
            $this->consenso_privacy = 'acconsento';
            $this->presa_visione_istruzioni = 'è stata presa visione delle istruzioni';


        }

        #CHECK FILE EXTENSIONS
        if ($delega) {
            $this->delega = $delega;
            $this->delega_path = $_FILES["delega"]["tmp_name"];

            $info = new \SplFileInfo($delega);
            if(!in_array($info->getExtension(),$this->allowed_extension)){
                return false;
            }
        }

        if ($attestazione) {
            $this->attestazione = $attestazione;
            $this->attestazione_path = $_FILES["attestazione"]["tmp_name"];

            $info = new \SplFileInfo($delega);
            if(!in_array($info->getExtension(),$this->allowed_extension)){
                return false;
            }
        }
        return true;
    }

    public  function sendEmail()
    {
        // Message
        $message = '
            <html>
            <head>
              <title>CairoCommunication</title>
            </head>
            <body>
              <table>
                <tr>
                    <th>Nome</th>
                  <td>' . $this->name . '</td>
                </tr>
                <tr>
                    <th>Cognome</th>
                  <td>' . $this->surname . '</td>
                </tr>
                <tr>
                  <th>Email</th>
                  <td>' . $this->email . '</td>
                </tr>
                <tr>
                  <th>Numero di telefono</th>
                  <td>' . $this->mobile_phone . '</td>
                </tr>
                <tr>
                  <th>Visione delle istruzioni</th>
                  <td>' . $this->presa_visione_istruzioni . '</td>
                </tr>
                <tr>
                <th>Consenso Privacy</th>
                  <td>' . $this->consenso_privacy . '</td>
                </tr>
              </table>
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
            $email->Subject = self::TITOLO_ASSEMBLEA;
            $email->Body = $message;
            $email->IsHTML(true);
            $email->AddCC($this->email);
            $email->AddAddress(self::EMAIL_TO_ADDRESS,self::EMAIL_TO_NAME);
            if ($this->delega) {
                $file_to_attach = $this->delega_path;
                $email->AddAttachment($file_to_attach, $this->delega);
            }
            if ($this->attestazione) {
                $file_to_attach = $this->attestazione_path;
                $email->AddAttachment($file_to_attach, $this->attestazione);
            }

            $email->Send();
            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

}
