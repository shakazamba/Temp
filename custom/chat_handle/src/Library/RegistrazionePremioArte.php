<?php

namespace Drupal\iscrizione_premio_arte\Library;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Token;
use Drupal\field\Entity\FieldConfig;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
use Drupal\iscrizione_premio_arte\Utility\MailUtility;
use Drupal\iscrizione_premio_arte\Utility\NodeUtility;
use Drupal\node\Entity;
use Drupal\Core\Field\FieldItemList;
use Drupal\file\Plugin\Field\FieldType\FileItem;
//use Drupal\file\Plugin\Field\FieldType\FileItem;
use Drupal\Core\TypedData\Exception\MissingDataException;
use Drupal\file\Entity\File;

use Symfony\Component\HttpFoundation\RedirectResponse;

class RegistrazionePremioArte
{
    private $nid;
    private $node;
    private $file_pdf_info;

    public function __construct($nid = 0)
    {
        if ($nid) {
            $this->setNodeByNid($nid);
        }
    }

    /**
     * Ritorna se il pagamento è già ok
     *
     * @return bool
     * @throws \Exceptioncrea_scheda_pdf_iscrizione
     */
    public function is_stato_pagamento_ok()
    {
        $node = $this->getNode();
        if (!empty($node)) {
            return !!$node->get('field_status_pagamento')->value;
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }

    /**
     * Recupero di dati dal nodo della registrazione da mostrare nelle pagine:
     * - pagamento
     *
     * @return array
     * @throws \Exception
     */
    public function getDatiRegistrazione()
    {
        return array();
    }

    public function getAnnoNome()
    {
        $node = $this->getNode();
        if (!empty($node)) {

            $anno = $node->get('field_anno_premio')->getString();
            $anno_nome = '';
            if (!empty($anno)) {
                $vid = 'anno_premio';
                $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
                foreach ($terms as $term) {
                    $el = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
                    if ($anno == $term->tid) {
                        $anno_nome = strtolower($term->name);
                        break;
                    }
                }
            }

            return $anno_nome;
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }

    /**
     * prendo il costo dell'iscrizione
     *
     * @return float
     * @throws \Exception
     */
    public function getCostoIscrizione()
    {
        $node = $this->getNode();
        if (!empty($node)) {
            /** @var FieldItemList $f_costo_iscrizione */
            $f_costo_iscrizione = $node->get('field_costo_iscrizione');
            $costo_iscrizione = $f_costo_iscrizione->get(0)->getValue()['value'];
            $costo_iscrizione = str_replace(',', '.', $costo_iscrizione);
            return floatval($costo_iscrizione);
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }


    public function getTipologiaUtente()
    {
        $node = $this->getNode();
        $tipologia = '';
        if (!empty($node)) {
            //Dati necessari per il calcolo del prezzo
            /** @var \Drupal\Core\Field\FieldItemList $studente_accademia */
            $studente_accademia = $node->get('field_studente_accademia');
            $studente_accademia = $studente_accademia->get(0)->getValue()['value'];

            /** @var \Drupal\Core\Field\EntityReferenceFieldItemList $nazione_di_residenza */
            $obj_nazione_di_residenza = $node->get('field_nazione_di_residenza');          //italia -> id: 1017
            $nazione_di_residenza = $obj_nazione_di_residenza->getValue()[0]['target_id'];

            if ($nazione_di_residenza != 1017) {
                $tipologia = 'estero';
            } else if ($studente_accademia == 1) {
                $tipologia = 'studente';
            } else {
                $tipologia = 'standard';
            }

            return $tipologia;
        } else {
            throw new \Exception('Nodo non caricato');
        }

    }

    /**
     * prendo testo conferma iscrizione
     */
    public function getConfermaIscrizioneText()
    {
        $node = $this->getNode();
        if (!empty($node)) {
            $text = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', 'iscrizione_premio')["field_email_conferma_iscrizione"]->toArray()['description'];
            return $text;
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }

    /**
     * Memorizzo il responso da parte di PayPal
     *
     * @param $riposta_transazione
     * @return bool
     * @throws \Exception
     */
    public function setRispostaTransazione($riposta_transazione)
    {
        $node = $this->getNode();
        if (!empty($node)) {
            if (isset($riposta_transazione['purchase_units'][0]['payments']['captures'][0]['id'])) {
                $field_paypal_code = $riposta_transazione['purchase_units'][0]['payments']['captures'][0]['id'];
            } else {
                $field_paypal_code = '';
            }

            $node->set('field_paypal_code', $field_paypal_code);
            $node->set('field_risposta_transazione', serialize($riposta_transazione));
            $node->save();

            //Setto il nuovo node
            $this->setNode($node);

            return true;
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }


    /**
     * Convalido l'iscrizione
     * @throws \Exception
     */
    public function setIscrizionePagamentoCompleti()
    {
        $node = $this->getNode();
        if (!empty($node)) {
            $status_pagamento = $node->get('field_status_pagamento')->value;
            $status_iscrizione = $node->get('field_status_iscrizione')->value;

            if (($status_pagamento == 0) && ($status_iscrizione == 0)) {
                $node->set('field_status_pagamento', 1);
                $node->set('field_status_iscrizione', 1);
                $node->save();

                //Setto il nuovo node
                $this->setNode($node);
                return true;
            }
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }


    /**
     * Genero il pdf dell'iscrizione e l'associa all'iscrizione
     *
     * @param $idRegistrazione
     * @throws \Exception
     */
    public function crea_scheda_pdf_iscrizione()
    {
        $node = $this->getNode();

        if (!empty($node)) {
            // GENERA PDF
            $pdf = NodeUtility::genera_pdf_by_node_id($this->nid);
            $file_id = $pdf['file_id'];
            $node->field_file_scheda_pdf[] = ['target_id' => $file_id];
            $node->save();
            // dd(__LINE__);

            //Setto il nuovo node
            $this->setNode($node);
            $this->file_pdf_info = $pdf;
            return true;
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }

    public function setFile_pdf_info($pdf_info){
        $this->file_pdf_info = $pdf_info;
    }

    /**
     * Invio email
     *
     * @throws \Exception
     */
    public function sendEmail($entity = null)
    {
        //Nel node save gli passo direttamente l'entity
        if (empty($entity)) {
            $node = $this->getNode();
        } else {
            $node = $entity;
        }

        if (!empty($node)) {
            $arr_file_path = array();
            $arr_file_name = array();

            #Impostazione email
            $subject = 'Segreteria Premio Arte | Iscrizione Accettata';
            $body = $node->get('field_email_conferma_iscrizione')->value;
            $body = NodeUtility::get_body_html($body, $node);
            $email = $node->get('field_email')->getString();

            $nome = $node->get('field_nome_artista')->getString() . ' ' . $node->get('field_cognome_artista')->getString();

            #File da allegare
            //Pdf generato
            $file_id = $this->file_pdf_info["file_id"];
            $file_location = $this->file_pdf_info["file_location"];
            $file_name = $this->file_pdf_info["file_name"];

            $file_location = \Drupal::service('file_system')->realpath($file_location);

            $arr_file_path[] = $file_location;
            $arr_file_name[] = $file_name;

            //Immagini allegate
            $field_immagine_opera = $node->get('field_immagine_opera')->getValue();
            if (!empty($field_immagine_opera)) {
                foreach ($field_immagine_opera as $k => $v) {
                    $fid = $v['target_id'];
                    $file = File::load($fid);
                    $file_uri = \Drupal::service('file_system')->realpath($file->getFileUri());
                    $file_name = basename($file_uri);
                    $arr_file_path[] = $file_uri;
                    $arr_file_name[] = $file_name;
                }
            }

            // dd($email);
            $conf_email = NodeUtility::get_email_conf($subject, $body, $email, $nome, $arr_file_path, $arr_file_name, true);

            return MailUtility::send($conf_email);
        } else {
            throw new \Exception('Nodo non caricato');
        }
    }

    ####################################################################################################################

    /**
     * Carico il nodo corrispondente tramite nid
     *
     * @param int $nid
     */
    public function setNodeByNid($nid = 0)
    {
        //Default settato a null
        $this->node = null;

        if (!empty($nid)) {
            $this->setNid($nid);
        }

        $nid = $this->getNid();
        if (!empty($nid)) {
            $node = Node::load($nid);
            if (!empty($node)) {
                $this->node = $node;
            }
        }
    }

    /**
     * @param null $node
     */
    public function setNode($node = null)
    {
        if (!empty($node)) {
            $this->node = $node;
        }
    }

    /**
     * @return mixed
     */
    public function getNode()
    {
        return $this->node;
    }

    /**
     * @return int
     */
    public function getNid()
    {
        return $this->nid;
    }

    /**
     * @param int $nid
     */
    public function setNid($nid)
    {
        $this->nid = intval($nid);
    }


}

?>
