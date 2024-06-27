<?php

namespace Drupal\chat_handle\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\File;
use Drupal\Core\Utility\Token;
use Drupal\field\Entity\FieldConfig;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

// function themeDump($var)
// {
//   $var_dumper = '\Symfony\Component\VarDumper\VarDumper';
//   if (class_exists($var_dumper)) {
//     call_user_func($var_dumper . '::dump', $var);
//   }
//   else {
//     trigger_error('Could not dump the variable because symfony/var-dumper component is not installed.', E_USER_WARNING);
//   }
// }

class ChatHandleForm extends FormBase
{

    public function getFormId()
    {
        return 'chat_handle_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {
        /**
         *  Fixes image upload bug in form. Does not work unless we
         * disable the cache while using PrivateTempStore
         **/

        $form_state->disableCache();

        $date = date('d/m/Y', time());

        $form['titolochat'] = array(
            '#type' => 'textfield',
            '#name' => 'titolochat',
            '#placeholder' => 'Chat - ' . $date,
        );

        $form['#token'] = FALSE;

        $form['actions']['#type'] = 'actions';
        $form['submit'] = array(
            '#type' => 'submit',
            '#title' => $this->t('Nuova chat'),
            '#value' => $this->t('Nuova chat'),
            // '#attributes' => array(
            //   'class' => array('action-button', 'js-send-form')
            // ),
        );

        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
        
        $titolo = $form_state->getValue('titolochat');
        if (!empty($titolo)) {
            $form_state->setValue('titolochat', $titolo);
        } else {
            $date = date('d/m/Y', time());
            $text = 'Chat - ' . $date;
            $form_state->setValue('titolochat', $text);
            
        }
        
        
    }
    
    public function submitForm(array &$form, FormStateInterface $form_state)
    {   
        $titolo = $form_state->getValue('titolochat');
        $userID = \Drupal::currentUser()->id();

        $node = \Drupal::entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->loadByProperties([
          'type' => 'chat',
          'field_user_pagina' => $userID,
        ]);

        $nodeID = key($node);
        $actual_node = Node::load($nodeID);

        $actual_node->set('field_titolo', $titolo);
        $actual_node->save();

        // redirect nodo chat con nuovo titolo
        $form_state->setRedirect('entity.node.canonical', ['node' => $nodeID]);

    }


}

?>
