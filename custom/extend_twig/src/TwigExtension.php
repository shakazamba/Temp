<?php

namespace Drupal\extend_twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class TwigExtension extends AbstractExtension {

  /**
   * {@inheritdoc}
   * This function must return the name of the extension. It must be unique.
   */
  public function getName() {
    return 'extend_twig';
  }

  public function getFilters() {
      return array();
  }

  /**
   * In this function we can declare the extension function
   */
  public function getFunctions() {
    return array(
      new TwigFunction('extend_file_url', array($this, 'getFileUrl')),
      new TwigFunction('extend_image_style_url', array($this, 'getStyleUrl')),
      new TwigFunction('extend_image_width', array($this, 'getWidth')),
      new TwigFunction('extend_image_height', array($this, 'getHeight')),
      new TwigFunction('extend_img', array($this, 'getImg')),
      new TwigFunction('extend_img_theme', array($this, 'getImgTheme')),
      new TwigFunction('extend_page_url', array($this, 'getPageUrl')),
      new TwigFunction('extend_pathalias', array($this, 'getPathalias')),
      new TwigFunction('extend_get_content_type', array($this, 'getContentType')),
	 );
  }

  /**
   * The php function to load a given block
   */
  public function getStyleUrl($uri, $style = "thumbnail") {
    return \Drupal\image\Entity\ImageStyle::load($style)->buildUrl($uri);
  }
  public function getWidth($uri, $style = "thumbnail") {
  	$image = \Drupal::service('image.factory')->get($uri);
    return $image->getWidth();
  }
  public function getHeight($uri, $style = "thumbnail") {
  	$image = \Drupal::service('image.factory')->get($uri);
    return $image->getWidth();
  }
  public function getImg($entity, $style = "thumbnail", $attributes = null) {
  	$file = $entity->entity;
	$val = $entity->getValue();
  	$image = \Drupal::service('image.factory')->get($file->getFileUri());
	if ($image->isValid()) {

		isset($val['alt']) ? $alt = $val['alt'] : $alt = '';


	    // Set up the render array.
	    $conf = [
	      '#theme' => 'image_style',
	      '#width' => $image->getWidth(),
	      '#height' => $image->getHeight(),
	      '#style_name' => $style,
	      '#uri' => $file->getFileUri(),
	      '#alt' => $alt,
	      '#attributes' => $attributes
	    ];

	    // Cache logic.
	    $renderer = \Drupal::service('renderer');
	    $renderer->addCacheableDependency($conf, $file);

	    // Render image.
	    return \Drupal::service('renderer')->render($conf);
  	}
  }
  public function getImgTheme($imageUrl, $style = "thumbnail", $attributes = null) {
      $uri = 'public://' . $imageUrl;

      $styleUrl = \Drupal\image\Entity\ImageStyle::load($style)->buildUri($uri);
      $fs = \Drupal::service('file_system');
      $realPath = $fs->realpath($styleUrl);
      $size = [1,1];
      if (file_exists($realPath)) {
        $size = getimagesize($realPath);
      }

      $conf = [
        '#theme' => 'image_style',
        '#width' => $size[0],
        '#height' => $size[1],
        '#style_name' => $style,
        //'#uri' => $file->getFileUri(),
        '#uri' => $uri,
        '#alt' => '',
        '#attributes' => $attributes
      ];

      // Render image.
      return render($conf);
  }
  public function getPageUrl($entity_type, $nid) {
  	$options = array('absolute' => TRUE);

	if($entity_type == 'node'){
		$url = \Drupal\Core\Url::fromRoute('entity.node.canonical', ['node' => $nid], $options);
		$url = $url->toString();
	}
    return $url;
  }
  public function getFileUrl($uri) {

	$url = \Drupal::service('file_url_generator')->generateAbsoluteString($uri);

    return $url;
  }
  public function getPathalias($entity_type, $nid) {
  	$options = array('absolute' => TRUE);

	if($entity_type == 'node'){
		$alias = \Drupal::service('path_alias.manager')->getAliasByPath('/node/' . $nid);
		// rimuovo eventuale primo slash
		if($alias[0] == '/') $alias = substr($alias, 1);
	}
    return $alias;
  }
  public function getContentType($nid) {

	$node = \Drupal\node\Entity\Node::load($nid);
	$type	= $node->getType();

    return $type;
  }



}
