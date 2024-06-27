<?php

namespace Drupal\chat_access\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Drupal\node\Entity\Node;
use Drupal\user\Entity\User;

// function themeDump($var) {
//   $var_dumper = '\Symfony\Component\VarDumper\VarDumper';
//   if (class_exists($var_dumper)) {
//     call_user_func($var_dumper . '::dump', $var);
//   }
//   else {
//     trigger_error('Could not dump the variable because symfony/var-dumper component is not installed.', E_USER_WARNING);
//   }
// }

/**
 * Controller for the Basic Custom module example.
 */
class ChatAccessController extends ControllerBase {

  /**
   * Hello World controller method.
   *
   * @return array
   *   Return just an array with a piece of markup to render in screen.
   */
  public function helloWorld() {

    return [
      '#markup' => $this->t('Hello World, I am just a basic custom example.'),
    ];
  }

}
