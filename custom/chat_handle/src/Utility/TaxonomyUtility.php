<?php
/**
 * Created by PhpStorm.
 * User: ilansimonetti
 * Date: 2019-02-14
 * Time: 15:44
 */

namespace Drupal\iscrizione_premio_arte\Utility;

class TaxonomyUtility
{
    /**
     * Creazione lista termini tassonomia
     *
     * @param $vid
     * @return array
     * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
     * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
     */
    public static function get_taxonomy_list_from_vocabulary_name($vid, $sort_by_name = false)
    {
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadTree($vid);
        $term_data = array();
        foreach ($terms as $term) {
            $term_data[] = array(
                'id' => $term->tid,
                'name' => $term->name
            );
        }

        if($sort_by_name){
            usort($term_data, function ($a, $b) { return strcmp($a["name"], $b["name"]); } );
        }

        return $term_data;
    }
}
