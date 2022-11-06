<?php

namespace Drupal\general_section;
use Drupal\encrypt\Entity\EncryptionProfile;

use Drupal\block\Entity\Block;
use Drupal\user\Entity\User;
use Drupal\node\Entity\Node;
/**
 * Class Custom_Replace.
 */
class TwigExtension extends \Twig_Extension {


  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'custom_replace';
  }

  public function getFunctions() {
    return [
      new \Twig_SimpleFunction(
        'custom_replace',
        [$this, 'custom_replace'],
        ['is_safe' => ['html']]
      ),
    ];
  }

  /**
   * {@inheritdoc}
   * @param $search can a array [`word1`,`work2`] or string `word`
   */
  public function custom_replace($id) { 
    return base64_encode($id);
  }
}