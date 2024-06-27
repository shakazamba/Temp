<?php
/**
 * Created by PhpStorm.
 * User: ilTrovatore
 * Date: 19/12/2018
 * Time: 15:31
 */

namespace Drupal\iscrizione_premio_arte\Utility;


class ErrorCodeMessage
{
    public static function getMessageByCode($code = 0){
        $messages = [
            0 => 'generic error',
            1 => 'invalid captcha',
            2 => 'missing required inputs',
            3 => 'invalid file extension',
        ];

        return $messages[$code];
    }


}
