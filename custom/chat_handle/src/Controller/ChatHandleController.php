<?php

namespace Drupal\chat_handle\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\chat_handle\Utility\NodeUtility;
use Drupal\chat_handle\Utility\TaxonomyUtility;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\views\Views;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Datetime\DrupalDateTime;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Drupal\Core\Ajax\AjaxResponse;

use JsonSerializable;

function themeDump($var)
{
  $var_dumper = '\Symfony\Component\VarDumper\VarDumper';
  if (class_exists($var_dumper)) {
    call_user_func($var_dumper . '::dump', $var);
  }
  else {
    trigger_error('Could not dump the variable because symfony/var-dumper component is not installed.', E_USER_WARNING);
  }
}

/**
 * Defines CustomLoginController class.
 */
class ChatHandleController extends ControllerBase
{
    /**
     * Recupero i valori dei campi collegati a tassonomie per poi creare la form di registrazione al premio Cairo
     *
     * @return array
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public function create_new() {
      $user = User::load(\Drupal::currentUser()->id());
      if ($user->isAuthenticated()) {
        
        $formNewChat = \Drupal::formBuilder()->getForm('Drupal\chat_handle\Form\ChatHandleForm');
        
        return array(
            '#theme' => 'chat_new',
            '#formNewChat' => $formNewChat,
        );
      } else {
        return;
      }
    }

    public function save_chat(Request $request, $uid)
    {

      $node = \Drupal::entityTypeManager()
        ->getListBuilder('node')
        ->getStorage()
        ->loadByProperties([
          'type' => 'chat',
          'field_user_pagina' => $uid,
      ]);

      $nodeID = key($node);
      $actual_node = Node::load($nodeID);

      $now = date('d/m/Y');

      $dati = json_decode( $request->getContent(),TRUE);
      $titolo = $dati['titolo'];
      $chat = $dati['storicoChat'];
      $risorse = $dati['storicoRisorsa'];
      $db = $dati['db'];
      $dbnome = '';
      if ($db == 'PAR') {
        $dbnome = 'Pareri FiscalFocus';
      } elseif ($db == 'AGE') {
        $dbnome = 'Interpelli Agenzia Entrate';
      } elseif ($db == 'DOC') {
        $dbnome = 'Documentazione Interna';
      }
      
      $paragStorico = Paragraph::create([
        'type' => 'elemento_storico',
        'field_data' => $now,
        'field_database' => $dbnome,
        'field_domande_risposte' => $chat,
        'field_note' => '',
        'field_risorse' => $risorse,
        'field_titolo' => $titolo,
      ]);
      $paragStorico->save();

      // Grab any existing paragraphs from the node, and add this one 
      $current = $actual_node->get('field_storico')->getValue();
      $current[] = array(
        'target_id' => $paragStorico->id(),
        'target_revision_id' => $paragStorico->getRevisionId(),
      );

      // SALVA TITOLO
      $actual_node->set('field_titolo', $titolo);
      // Salva Parag
      $actual_node->set('field_storico', $current);
      // salva nodo
      $actual_node->save();

      $message = 'Storico salvato correttamente';

      return new JsonResponse(array('idNodo' => $nodeID, 'message' => $message));

    }
    public function load_chat(Request $request, $uid)
    {
      $dati = json_decode( $request->getContent(),TRUE);
      $nodoID = $dati['nodo'];
      $paragID = $dati['parag'] + 0;

      $actual_node = Node::load($nodoID);
      $ids_list = [];
      $actual_parag = [];
      $resp = [];

      $list = $actual_node->get('field_storico')->referencedEntities();
      foreach ($list as $key => $item) {
        $itemID = $item->id() + 0;
        $ids_list[] = $itemID;
      }

      if (in_array($paragID, $ids_list)) { 
          $parag = Paragraph::load($paragID);
          $titolo = $parag->get('field_titolo')->getValue();
          $chat = $parag->get('field_domande_risposte')->getValue();
          $risorse = $parag->get('field_risorse')->getValue();
          $db = $parag->get('field_database')->getValue();

          $resp = [
            'titolo' => $titolo,
            'chat' => $chat,
            'risorse' => $risorse,
            'db' => $db,
            'type' => 'load'
          ];          
      } else { 
          $resp = 'vado qui';
      } 

      return new JsonResponse($resp);

    }
    public function delete_chat(Request $request, $uid)
    {
      $dati = json_decode( $request->getContent(),TRUE);
      $nodoID = $dati['nodo'];
      $paragID = $dati['parag'] + 0;

      $actual_node = Node::load($nodoID);
      $ids_list = [];
      $actual_parag = [];
      $resp = [];

      $list = $actual_node->get('field_storico')->referencedEntities();
      foreach ($list as $key => $item) {
        $itemID = $item->id() + 0;
        $ids_list[] = $itemID;
      }

      if (in_array($paragID, $ids_list)) { 
          // $parag = Paragraph::load($paragID);
          // $parag = Paragraph::delete($paragID);
          // $parag->setValue(array());

          // $parag->delete();
          // $actual_node->delete($parag);
          $actual_node->get('field_storico')->setValue(array_filter($actual_node->get('field_storico')->getValue(), function ($value) use ($paragID) {
            return ($value['target_id'] != $paragID);
          }));

          // $list->removeItem($paragID);


          // salva nodo
          $actual_node->save();


          $resp = [
            'parag' => $parag,
            'type' => 'delete'
          ];          
      } else { 
          $resp = 'vado qui';
      } 

      return new JsonResponse($resp);

    }
    // public function edit_chat($uid)
    // {

    //   $node = \Drupal::entityTypeManager()
    //     ->getListBuilder('node')
    //     ->getStorage()
    //     ->loadByProperties([
    //       'type' => 'chat',
    //       'field_user_pagina' => $uid,
    //   ]);

    //   $nodeID = key($node);
    //   $actual_node = Node::load($nodeID);


    //   return array(
    //         '#theme' => 'chat_edit',
    //   );




    // }
}
