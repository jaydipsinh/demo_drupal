<?php

namespace Drupal\general_section;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\user\Entity\User;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\redirect\Entity\Redirect;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;

/**
 * Defines an AutologoutManager service.
 */
class GeneralSectionManager implements GeneralSectionManagerInterface {

  /**
   * The module manager service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;
  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;
  /**
   * Constructs an AutologoutManager object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
//  public function __construct(ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
//    $this->moduleHandler = $module_handler;
//    //$this->autoLogoutSettings = $config_factory->get('autologout.settings');
//    $this->configFactory = $config_factory;
//  }

  /**
   * {@inheritdoc}
   */
  public function getcontentfromtaxo($tid) {
    global $base_url;
    $user = \Drupal::currentUser();
    $roles = $user->getRoles();
    //var_dump($roles); 
  }

}
