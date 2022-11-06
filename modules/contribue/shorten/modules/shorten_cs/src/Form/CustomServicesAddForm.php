<?php

namespace Drupal\shorten_cs\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Render\RendererInterface;

/**
 * Settings form.
 */
class CustomServicesAddForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

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
   * CustomServicesAddForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
        ModuleHandlerInterface $module_handler,
        Connection $database,
        RendererInterface $renderer,
        ConfigFactoryInterface $config_factory
    ) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->database = $database;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('module_handler'),
          $container->get('database'),
          $container->get('renderer'),
          $container->get('config.factory')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shorten_cs_add_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'shorten.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $config = $this->config('shorten.settings');

    $table = $this->shortenCsServicesTable();
    if (!empty($table)) {
      $form['custom_services'] = [
        '#markup' => $table,
      ];
    }

    $form['#attached']['library'][] = 'shorten_cs/shorten_cs';

    if (!isset($form) || !is_array($form)) {
      $form = [];
    }
    $form['#attributes'] = ['class' => 'shorten-cs-apply-js'];
    $form['name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#description' => $this->t('The name of the service'),
      '#required' => TRUE,
    ];
    $form['url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API endpoint URL'),
      '#description' => $this->t(
          'The URL of the API endpoint, with parameters,
           such that the long URL can be appended to the end.'
      ) . ' ' .
      $this->t('For example, the endpoint for TinyURL is') . ' <code>http://tinyurl.com/api-create.php?url=</code>. ' .
      $this->t(
          'Appending a long URL to the endpoint
          and then visiting that address will return data about the shortened URL.'
      ),
      '#required' => TRUE,
    ];
    $form['type'] = [
      '#type' => 'radios',
      '#title' => $this->t('Response type'),
      '#description' => $this->t('The type of response the API endpoint returns.'),
      '#required' => TRUE,
      '#default_value' => 'text',
      '#options' => [
        'text' => $this->t('Text'),
        'xml' => 'XML',
        'json' => 'JSON',
      ],
    ];
    $form['tag'] = [
      '#type' => 'textfield',
      '#title' => $this->t('XML tag or JSON key'),
      '#description' => $this->t('The XML tag or JSON key that identifies the full short URL in the API response.') . ' ' .
      $this->t('Only required for XML and JSON response types.') . '<br> ' .
      $this->t('For multidimensional JSON responses, a path can be specified using '
        . 'dot notation in order to specify the element in containing the '
        . 'short url. For example, the path \'data.url\' would point to the '
        . 'url value in the following JSON response: <br>'
        . '{"data":{"url":"http://ex.am/ple"}}<br>'
        . 'If a JSON element name itself contains a dot character, it can be '
        . 'wrapped in double quotes.'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $record = [];
    foreach (['name', 'url', 'type', 'tag'] as $key) {
      $record[$key] = $values[$key];
    }
    $this->database->insert('shorten_cs')->fields($record)->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $v = $form_state->getValues();
    if (($v['type'] == 'xml' || $v['type'] == 'json') && empty($v['tag'])) {
      $form_state->setErrorByName(
            'type',
        $this->t(
                'An XML tag or JSON key is required for services with a response type of XML or JSON.'
            )
        );
    }
    $exists = $this->database->query(
          "SELECT COUNT(sid) FROM {shorten_cs} WHERE name = :name",
          [
            ':name' => $v['name'],
          ]
      )->fetchField();
    if ($exists > 0) {
      $form_state->setErrorByName('name', $this->t('A service with that name already exists.'));
    }
    else {
      $all_services = $this->moduleHandler->invokeAll('shorten_service');
      $all_services['none'] = $this->t('None');
      foreach ($all_services as $key => $value) {
        if ($key == $v['name']) {
          $form_state->setErrorByName('name', $this->t('A service with that name already exists.'));
          break;
        }
      }
    }
  }

  /**
   * Displays the table of existing custom services.
   */
  public function shortenCsServicesTable() {
    $header = [$this->t('Name'), $this->t('URL'), $this->t('Type'), $this->t('XML/JSON tag'), $this->t('Actions')];
    $rows = [];
    $result = $this->database->query("SELECT * FROM {shorten_cs} ORDER BY name ASC")->fetchAll();
    foreach ($result as $service) {
      $service = (array) $service;
      $service = [
        'sid' => $service['sid'],
        'name' => Html::escape($service['name']),
        'url' => Html::escape($service['url']),
        'type' => $service['type'],
        'tag' => Html::escape($service['tag']),
      ];

      $options = ['absolute' => TRUE];
      $actions = [
        '#markup' => Link::createFromRoute(
            'edit',
            'shorten_cs.edit_form',
            ['service' => $service['sid']],
            $options
        )->toString() . ' ' . Link::createFromRoute(
            'delete',
            'shorten_cs.delete_form',
            ['service' => $service['sid']],
            $options
        )->toString(),
      ];
      $service['actions'] = $this->renderer->render($actions);

      unset($service['sid']);
      $rows[] = $service;
    }
    if (!empty($rows)) {
      $table = [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#attributes' => [
          'id' => 'shorten_custom_services',
        ],
      ];
      return $this->renderer->render($table);
    }
    return '';
  }

}
