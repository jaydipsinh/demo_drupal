<?php

namespace Drupal\shorten\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Shorten URL for Current Page' block.
 *
 * @Block(
 *   id = "shorten_short",
 *   admin_label = @Translation("Short URL"),
 *   category = @Translation("Forms")
 * )
 */
class ShortenCurrentPage extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * Constructs a new ShortenCurrentPage.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(
        array $configuration,
        $plugin_id,
        $plugin_definition,
        FormBuilderInterface $form_builder
    ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
          $configuration,
          $plugin_id,
          $plugin_definition,
          $container->get('form_builder')
      );
  }

  /**
   * {@inheritdoc}
   */
  // Protected function blockAccess(AccountInterface $account) {
  // return $account->hasPermission('use Shorten URLs page');
  // }.

  /**
   * {@inheritdoc}
   */
  public function build() {
    // drupal_set_message(t('This block displays the short URL for the page on
    // which it is shown, which can slow down uncached pages in some
    // instances.'), 'warning');.
    return $this->formBuilder->getForm('Drupal\shorten\Form\ShortenFormCurrentPage');
  }

}
