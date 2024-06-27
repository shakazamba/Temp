<?php

namespace Drupal\iscrizione_premio_arte\Objects;


use Drupal\iscrizione_premio_arte\Utility\ErrorCodeMessage;
use Symfony\Component\HttpFoundation\Request;

class EmailContent
{
    private $subject = null;
    private $name = null;
    private $surname = null;
    private $email = null;
    private $address = null;
    private $zip_code = null;
    private $city = null;
    private $mobile_phone = null;
    private $presentation = null;
    private $cv_name = null;
    private $cv_path = null;
    public $invalid_input = array();
    public $is_valid_content = true;
    public $privacy = true;
    public $consenso_privacy = '';
    public $allowed_extension = array('pdf','doc','docx','ppt','pptx');




    public function __construct(Request $request)
    {
        $this->populate_if_is_valid_input($request);

    }

    public function populate_if_is_valid_input(Request $request)
    {

        //Required
        $subject = $request->get('subject');
        $name = $request->get('name');
        $surname = $request->get('surname');
        $email = $request->get('email');
        $privacy = $request->get('privacy');

        $address = $request->get('address');
        $zip_code = $request->get('zip_code');
        $city = $request->get('city');
        $mobile_phone = $request->get('mobile_phone');
        $presentation = $request->get('presentation');

        $cv_name = $_FILES["cv"]['name'];



        //Required check
        if ((!($name !== '' && $surname !== '' && $email !== '')) || $privacy !== 'acconsento') {
            $this->is_valid_content = false;
            array_push($this->invalid_input, ErrorCodeMessage::getMessageByCode(2));
            return false;
        } else {
            $this->subject = $subject;
            $this->name = $name;
            $this->surname = $surname;
            $this->email = $email;
            $this->consenso_privacy = 'acconsento';


        }

        //populate other fields
        if ($address !== '') {
            $this->address = $address;
        }

        if ($zip_code !== '') {
            $this->zip_code = $zip_code;
        }

        if ($city !== '') {
            $this->city = $city;
        }
        if ($mobile_phone !== '') {
            $this->mobile_phone = $mobile_phone;
        }
        if ($presentation !== '') {
            $this->presentation = $presentation;
        }
        #CHECK FILE EXTENSIONS
        if ($cv_name) {
            $this->cv_name = $cv_name;
            $this->cv_path = $_FILES["cv"]["tmp_name"];

            $info = new \SplFileInfo($cv_name);
            if(!in_array($info->getExtension(),$this->allowed_extension)){
                $this->is_valid_content = false;
                array_push($this->invalid_input, ErrorCodeMessage::getMessageByCode(3));
                return false;
            }
        }
        return true;
    }


    public function getConsensoPrivacy()
    {
        return $this->consenso_privacy;
    }

    /**
     * @return null
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param null $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }


    /**
     * @return null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param null $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return null
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * @param null $surname
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;
    }

    /**
     * @return null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param null $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param null $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return null
     */
    public function getZipCode()
    {
        return $this->zip_code;
    }

    /**
     * @param null $zip_code
     */
    public function setZipCode($zip_code)
    {
        $this->zip_code = $zip_code;
    }

    /**
     * @return null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param null $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @return null
     */
    public function getMobilePhone()
    {
        return $this->mobile_phone;
    }

    /**
     * @param null $mobile_phone
     */
    public function setMobilePhone($mobile_phone)
    {
        $this->mobile_phone = $mobile_phone;
    }

    /**
     * @return null
     */
    public function getPresentation()
    {
        return $this->presentation;
    }

    /**
     * @param null $presentation
     */
    public function setPresentation($presentation)
    {
        $this->presentation = $presentation;
    }

    /**
     * @return null
     */
    public function getCvName()
    {
        return $this->cv_name;
    }

    /**
     * @param null $cv_name
     */
    public function setCvName($cv_name)
    {
        $this->cv_name = $cv_name;
    }

    /**
     * @return null
     */
    public function getCvPath()
    {
        return $this->cv_path;
    }

    /**
     * @param null $cv_path
     */
    public function setCvPath($cv_path)
    {
        $this->cv_path = $cv_path;
    }

    /**
     * @return array
     */
    public function getInvalidInput()
    {
        return $this->invalid_input;
    }

    /**
     * @param array $invalid_input
     */
    public function setInvalidInput($invalid_input)
    {
        $this->invalid_input = $invalid_input;
    }

    /**
     * @return bool
     */
    public function isValidContent()
    {
        return $this->is_valid_content;
    }

    /**
     * @param bool $is_valid_content
     */
    public function setIsValidContent($is_valid_content)
    {
        $this->is_valid_content = $is_valid_content;
    }

}
