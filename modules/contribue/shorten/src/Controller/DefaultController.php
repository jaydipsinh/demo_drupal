<?php

namespace Drupal\shorten\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Default controller for the shorten module.
 */
class DefaultController extends ControllerBase {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * DefaultController constructor.
   *
   * @param Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('renderer')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function shortenAdminForm() {
    $form = $this->formBuilder()->getForm('shorten_admin');
    return $this->renderer->render($form);
  }

  /**
   * {@inheritdoc}
   */
  public function shortenKeysForm() {
    $form = $this->formBuilder()->getForm('shorten_keys');
    return $this->renderer->render($form);
  }

  /**
   * {@inheritdoc}
   */
  public function shortenFormShortenForm() {
    $form = $this->formBuilder()->getForm('shorten_form_shorten');
    return $this->renderer->render($form);
  }

}
