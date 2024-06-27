<?php

namespace Drupal\iscrizione_premio_arte\Objects;


use Symfony\Component\HttpFoundation\Request;

class PrepareDataForEmail {
    const SECRET_CAPTCHA = '6LcfpXUUAAAAAD7GsoSpIYPk-V8wx9i0oZZRNsYQ';
    const URL_VERIFY_CAPTCHA = 'https://www.google.com/recaptcha/api/siteverify';
    const NAME_CAPTCHA_ = 'g-recaptcha-response';

    public $is_valid_captcha = false;
    public $email_content = null;
    /**
     * PrepareDataForEmail constructor.
     * @param Request $request
     */
    public function  __construct(Request $request)
    {

        if($this->is_valid_captcha_check($this->getCaptcha($request))) {
            $this->is_valid_captcha = true;
            $this->email_content = new EmailContent($request);
        }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function getCaptcha(Request $request){
        return $request->get($this::NAME_CAPTCHA_);
    }

    /**
     * @param $recaptcha
     * @return bool
     */
    public function is_valid_captcha_check($recaptcha){
        $secret = $this::SECRET_CAPTCHA;
        $url_verify_captcha = $this::URL_VERIFY_CAPTCHA;
        if(!$recaptcha){
            return false;
        } else {
            //prepare content
            $post_data = http_build_query(
                array(
                    'secret' => $secret,
                    'response' => $recaptcha,
                    'remoteip' => $_SERVER['REMOTE_ADDR'],
                )
            );
            //prepare options
            $options = array(
                'http' => array(
                    'method' => 'POST',
                    'header' => 'Content-type: application/x-www-form-urlencoded',
                    'content' => $post_data,
                ),
            );
            //create context
            $context = stream_context_create($options);
            //get result in json
            $result_json = file_get_contents($url_verify_captcha, false, $context);
            //get result
            $result = json_decode($result_json);
            //check if captcha is valid
            if(property_exists($result,'success')){
                if($result->success == 1){
                 return true;
                }
            }
            return false;
        }
    }

    /**
     * @return bool
     */
    public function isValidCaptcha()
    {
        return $this->is_valid_captcha;
    }

    /**
     * @return EmailContent|null
     */
    public function getEmailContent()
    {
        return $this->email_content;
    }

}
