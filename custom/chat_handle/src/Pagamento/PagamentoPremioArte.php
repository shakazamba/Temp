<?php

namespace Drupal\iscrizione_premio_arte\Pagamento;

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

class PagamentoPremioArte {

    private $paypal_sandbox = true;

    private $paypal_debug = 'true';
    private $paypal_sandbox_account = 'giorgio.maitti-merchant@gmail.com';
    private $paypal_sandbox_cliend_id = 'AfSuCEz5q2AKPZqoGmIwodJUA3sOua8iYe0CEa6CtpGCPQPzt6uNSmhXGp9X6_BQAA8sQCpeCpi2I2fk';
    private $paypal_sandbox_secret = 'EFQ2ftJqirulELEzPOe3RecwaXFBLSAH8_5_cNAHB_g7Ux2keMIhut0PbImTm9VxS5bm7BgN0UGc2274';

    private $paypal_account = 'giorgio.maitti-merchant@gmail.com';
    private $paypal_cliend_id = 'AfSuCEz5q2AKPZqoGmIwodJUA3sOua8iYe0CEa6CtpGCPQPzt6uNSmhXGp9X6_BQAA8sQCpeCpi2I2fk';
    private $paypal_secret = 'EFQ2ftJqirulELEzPOe3RecwaXFBLSAH8_5_cNAHB_g7Ux2keMIhut0PbImTm9VxS5bm7BgN0UGc2274';

    public static function render_payment_page($idRegistrazione) {
      $actual_node = Node::load($idRegistrazione);

      // ....

      return $actual_node;
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $buildInfo = $form_state->getBuildInfo();
        if(isset($buildInfo['args'][0]['idRegistrazione'])){
            $idRegistrazione = $buildInfo['args'][0]['idRegistrazione'];
            $form['paypal'] = $idRegistrazione;
            $node = Node::load($idRegistrazione);
            /** @var FieldItemList $f_costo_iscrizione */
            $f_costo_iscrizione = $node->get('field_costo_iscrizione');
            $costo_iscrizione = $f_costo_iscrizione->get(0)->getValue()['value'];
            $costo_iscrizione = str_replace(',', '.', $costo_iscrizione);
            $costo_iscrizione = floatval($costo_iscrizione);
        } else {
            //todo gio: mandare in errore
        }

        if ($this->paypal_sandbox === true) {
            $form['paypal'] = array(
                '#debug' => $this->paypal_sandbox_debug,
                '#account' => $this->paypal_sandbox_account,
                '#client_id' => $this->paypal_sandbox_cliend_id,
                '#data_order_id' => $idRegistrazione,
                '#costo' => $costo_iscrizione,
            );
        } else {
            $form['paypal'] = array(
                '#debug' => $this->paypal_debug,
                '#account' => $this->paypal_account,
                '#client_id' => $this->paypal_cliend_id,
                '#data_order_id' => $idRegistrazione,
                '#costo' => $costo_iscrizione,
            );
        }

        $form['actions']['#type'] = 'actions';
        $form['submit'] = array(
            '#type' => 'submit',
            '#title' => $this->t('Invia'),
            '#value' => $this->t('Invia'),
        );
        return $form;
    }

    public static function crea_scheda_pdf_iscrizione($idRegistrazione) {
      $actual_node = Node::load($idRegistrazione);

      // GENERA PDF
      $pdf = NodeUtility::genera_pdf_by_node_id($idRegistrazione);
      $file_id = $pdf['file_id'];
      $actual_node->field_file_scheda_pdf[] = ['target_id' => $file_id];
      $actual_node->save();

      return $actual_node;
    }

    public static function convalida_iscrizione($idRegistrazione) {
      $actual_node = Node::load($idRegistrazione);

      $status_pagamento = $actual_node->get('field_status_pagamento')->value;
      $status_iscrizione = $actual_node->get('field_status_iscrizione')->value;



      if(($status_pagamento == 0) && ($status_iscrizione == 0)){

        $actual_node->set('field_status_pagamento', 1);
        $actual_node->set('field_status_iscrizione', 1);
        $actual_node->save();

      }

      $body = $actual_node->get('field_email_conferma_iscrizione')->value;
      $body = NodeUtility::get_body_html($body,$actual_node);
      $conf_email = NodeUtility::get_email_conf_for_changed_states($actual_node,'Segreteria Premio Arte | Iscrizione Accettata',$body);

      // TODO Vale: inserire allegato scheda pdf generata

      MailUtility::send($conf_email);

    }


}

?>
