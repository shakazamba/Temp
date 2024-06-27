<?php

namespace Drupal\iscrizione_premio_arte\Library;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\iscrizione_premio_arte\Utility\NodeUtility;
use Drupal\node\Entity;
use Drupal\Core\Field\FieldItemList;

use Symfony\Component\HttpFoundation\RedirectResponse;

class PagamentoPremioArte
{

    private $paypal_sandbox;
    private $paypal_debug;

    private $paypal_sandbox_account = 'sb-gfaph1824932@personal.example.com';
    private $paypal_sandbox_cliend_id = 'AaxGHTHmxrbUBk0J6iXxfmO5EixQjUp2fYi8d-1JDxuI_rtedDXBu7NqKLBehG06dvhW_ry4iOIjQx5H';
    private $paypal_sandbox_secret = '';

    private $paypal_account = 'premioarte@cairoeditore.it';
    private $paypal_cliend_id = 'AVwrWwxEqcWliUdldXJm2rlR3f54mLYn2LjJGgZE756eElEVMH8yXDplw-4PHTIdOLF3Bm5QRHEXrL8K';
    private $paypal_secret = '';

    public function __construct($paypal_sandbox = false, $paypal_debug = false)
    {
        $this->paypal_sandbox = $paypal_sandbox;
        $this->paypal_debug = $paypal_debug;
    }

    /**
     * Ricavo lo stato del pagamento
     * @param $details
     * @return bool|mixed
     */
    public static function get_status_from_payment_details($details){
        if(isset($details['status'])){
            return $details['status'];
        }
        return false;
    }

    /**
     * Ricavo il nid della registrazione.
     *
     * @param $details
     * @return bool|int
     */
    public static function get_nid_from_payment_details($details){
        if(isset($details['purchase_units'][0]['custom_id'])){
            $tmp = $details['purchase_units'][0]['custom_id'];
            if(!empty($tmp)){
                $tmp = str_replace('id-', '', $tmp);
                $tmp = intval($tmp);
                if(!empty($tmp)){
                    return $tmp;
                }
            }
        }

        return false;
    }

    /**
     * Recupero l'array dei dati di accesso a paypal in abse se sto usando il sandbox o no
     *
     * @return array
     */
    public function get_paypal_data()
    {
        if ($this->paypal_sandbox) {
            $ret = array(
                '#env' => 'sandbox',
                '#debug' => $this->paypal_debug ? 'true' : 'false',
                '#account' => $this->paypal_sandbox_account,
                '#client_id' => $this->paypal_sandbox_cliend_id,
            );
        } else {
            $ret = array(
                '#env' => 'production',
                '#debug' => $this->paypal_debug ? 'true' : 'false',
                '#account' => $this->paypal_account,
                '#client_id' => $this->paypal_cliend_id,
            );

        }
        return $ret;
    }

}
?>
