<?php

namespace Drupal\shorten_cs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Default controller for the shorten_cs module.
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
  public function shortenCsEditForm($service) {
    $form = $this->formBuilder()->getForm('shorten_cs_edit', $service);
    return $this->renderer->render($form);
  }

  /**
   * {@inheritdoc}
   */
  public function shortenCsDeleteForm($service) {
    $form = $this->formBuilder()->getForm('shorten_cs_delete', $service);
    return $this->renderer->render($form);
  }

}
