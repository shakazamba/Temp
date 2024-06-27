<?php

namespace Drupal\iscrizione_premio_arte\Utility;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\Node;
use Drupal\iscrizione_premio_arte\Utility\MailUtility;
use Drupal\Core\File\FileSystemInterface;

class NodeUtility
{

    public static function create_node_from_conf($configuration)
    {
        $node = Node::create($configuration);
        try {
            $node->save();
            return $node->id();
        } catch (\Exception $e) {
            dd($e);
            return false;
        }
    }

    /**
     * @param $node_id
     * @return array
     */
    public static function genera_pdf_by_node_id($node_id)
    {
        // load node
        $node = Node::load($node_id);

        /** @var DomPdf $print_engine */
        //use module to get pdf from node display
        $print_engine = \Drupal::service('plugin.manager.entity_print.print_engine')->createSelectedInstance('pdf');
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
        $build = $view_builder->view($node);

        /** @var \Drupal\Core\Render\Renderer $render */
        $render = \Drupal::service('renderer');
        $html = $render->renderPlain($build);


        $print_engine->addPage((string)$html);
        $dompdf = $print_engine->getPrintObject();
        $dompdf->render();
        $output = $dompdf->output();
        //save the file in private folder
        $file_save_path_stream_directory = 'private://pdf-iscrizioni-premio';
        \Drupal::service('file_system')->prepareDirectory($file_save_path_stream_directory, FileSystemInterface::CREATE_DIRECTORY);

        //File_name
        $filename_artista_nome = preg_replace('/[^a-zA-Z0-9]/', '', $node->get('field_nome_artista')->getString());
        $filename_artista_cognome = preg_replace('/[^a-zA-Z0-9]/', '', $node->get('field_cognome_artista')->getString());
        $filename_data = date('Y-m-d');
        $file_name_rand = rand(100, 999);
        $file_name = date('Y') . '-iscrizione_premio_arte_' . $filename_artista_cognome . '-' . $filename_artista_nome . '_' . $filename_data . '-' . $node_id . '-' . $file_name_rand . '.pdf';
        $file_location = $file_save_path_stream_directory . '/' . $file_name;
        $file = file_save_data($output, $file_location, FILE_EXISTS_RENAME);

        return array('file_id' => $file->id(), 'file_location' => $file_location, 'file_name' => $file_name);
    }
    public static function genera_pdf_by_node($node)
    {
        $node_id = $node->id();

        /** @var DomPdf $print_engine */
        //use module to get pdf from node display
        $print_engine = \Drupal::service('plugin.manager.entity_print.print_engine')->createSelectedInstance('pdf');
        $view_builder = \Drupal::entityTypeManager()->getViewBuilder('node');
        $build = $view_builder->view($node);

        /** @var \Drupal\Core\Render\Renderer $render */
        $render = \Drupal::service('renderer');
        $html = $render->renderPlain($build);


        $print_engine->addPage((string)$html);
        $dompdf = $print_engine->getPrintObject();
        $dompdf->render();
        $output = $dompdf->output();
        //save the file in private folder
        $file_save_path_stream_directory = 'private://pdf-iscrizioni-premio';
        \Drupal::service('file_system')->prepareDirectory($file_save_path_stream_directory, FileSystemInterface::CREATE_DIRECTORY);

        //File_name
        $filename_artista_nome = preg_replace('/[^a-zA-Z0-9]/', '', $node->get('field_nome_artista')->getString());
        $filename_artista_cognome = preg_replace('/[^a-zA-Z0-9]/', '', $node->get('field_cognome_artista')->getString());
        $filename_data = date('Y-m-d');
        $file_name_rand = rand(100, 999);
        $file_name = date('Y') . '-iscrizione_premio_arte_' . $filename_artista_cognome . '-' . $filename_artista_nome . '_' . $filename_data . '-' . $node_id . '-' . $file_name_rand . '.pdf';
        $file_location = $file_save_path_stream_directory . '/' . $file_name;
        $file = file_save_data($output, $file_location, FILE_EXISTS_RENAME);

        return array('file_id' => $file->id(), 'file_location' => $file_location, 'file_name' => $file_name);
    }

    public static function get_email_conf($subject, $body, $email, $nome, $file_path, $file_name, $is_html)
    {
        $configuration = array();

        $configuration['host'] = 'mail.iltrovatore.it';
        $configuration['username'] = 'premioarte@cairoeditore.it';
        $configuration['password'] = 'm9e41o641';
        $configuration['email_from'] = 'premioarte@cairoeditore.it';
        $configuration['name_from'] = 'Segreteria Premio Arte';
        $configuration['subject'] = $subject;
        $configuration['body'] = $body;
        $configuration['bcc'] = 'premioarte@cairoeditore.it';
        $configuration['email_to'] = $email;
        $configuration['is_html'] = $is_html;
        $configuration['name_to'] = $nome;
        $configuration['file_path'] = $file_path;
        $configuration['file_name'] = $file_name;
        return $configuration;
    }

    public static function get_email_conf_for_changed_states($actual_node, $subject, $body)
    {
        //email fields
        $email = $actual_node->get('field_email')->getString();
        $nome = $actual_node->get('field_nome_artista')->getString() . ' ' . $actual_node->get('field_cognome_artista')->getString();

        return $email_configuration = self::get_email_conf($subject, $body, $email, $nome, null, null, true);
    }

    public static function get_body_html($html_string, $node)
    {
        $cognome_artista = $node->get('field_cognome_artista')->getString();
        $nome_artista = $node->get('field_nome_artista')->getString();

        $anno = $node->get('field_anno_premio') ? $node->get('field_anno_premio')->getString() : '';
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

        $titolo_opera = $node->get('field_titolo_opera')->getString();

        $disciplina = $node->get('field_disciplina_artistica') ? $node->get('field_disciplina_artistica')->getString() : '';
        $disciplina_nome = '';
        if (!empty($anno)) {
            $vid = 'discipline_artistiche';
            $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
            foreach ($terms as $term) {
                $el = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->load($term->tid);
                if ($disciplina == $term->tid) {
                    $disciplina_nome = strtolower($term->name);
                    break;
                }
            }
        }

        $html_string = str_replace("{{field_nome_artista}}", $nome_artista, $html_string);
        $html_string = str_replace("{{field_cognome_artista}}", $cognome_artista, $html_string);
        $html_string = str_replace("{{field_anno_premio}}", $anno_nome, $html_string);
        $html_string = str_replace("{{field_titolo_opera}}", $titolo_opera, $html_string);
        $html_string = str_replace("{{field_disciplina_artistica}}", $disciplina_nome, $html_string);

        return $html_string;

    }

    public static function is_changed_state($node_id, EntityInterface $entity)
    {
        //load the node before is saved
        $before_node = Node::load($node_id);

        // field_status_iscrizione field_status_pagamento
        $status_pagamento = $entity->get('field_status_pagamento')->value;
        $status_iscrizione = $entity->get('field_status_iscrizione')->value;

        if (($status_pagamento == 1) && ($status_iscrizione == 1)) {
            if ($before_node->get('field_status_pagamento')->value == 0) {
                $body = $entity->get('field_email_conferma_iscrizione')->value;
                $body = self::get_body_html($body, $entity);
                $conf_email = self::get_email_conf_for_changed_states($entity, 'Segreteria Premio Arte | Iscrizione Accettata', $body);
                MailUtility::send($conf_email);
            }
        }
    }
}
