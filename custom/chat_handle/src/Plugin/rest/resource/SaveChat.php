<?php
namespace Drupal\chat_handle\Plugin\rest\resource;

use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\ResourceResponse;
use Drupal\node\Entity\Node;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a Savechat Resource
 *
 * @RestResource(
 *   id = "savechat_resource",
 *   label = @Translation("Savechat Resource"),
 *   uri_paths = {
 *     "canonical" = "/rest/api/post/savechat-create",
 *     "create" = "/rest/api/post/savechat-create"
 *   }
 * )
 */
class SavechatResource extends ResourceBase {
  
  /**
   * Responds to POST requests.
   *
   * @param mixed $data
   *   Data to create the node.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\HttpException
   *   Throws exception expected.
   */
    public function post($data) {

        dd($data);
        return array(
        '#theme' => 'chat_save',
      );
    }

}