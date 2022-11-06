<?php

namespace Drupal\record_shorten\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;

/**
 * Default controller for the record_shorten module.
 */
class DefaultController extends ControllerBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * DefaultController constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(Connection $database, RendererInterface $renderer) {
    $this->database = $database;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('database'),
          $container->get('renderer')
      );
  }

  /**
   * Page build for Record Shorten.
   */
  public function page() {
    $build = [];
    $total = $this->database->query("SELECT COUNT(sid) FROM {record_shorten}")->fetchField();

    $build['summary']['#markup'] = '<p>' . $this->formatPlural(
          $total,
          '1 shortened URL has been recorded.',
          '@count shortened URLs have been recorded.'
      );

    $build['records_table']['#markup'] = record_shorten_records_table();

    $form = $this->formBuilder()->getForm('Drupal\record_shorten\Form\RecordshortenClearAll');
    $build['clear']['#markup'] = $this->renderer->render($form);

    return $build;
  }

}
