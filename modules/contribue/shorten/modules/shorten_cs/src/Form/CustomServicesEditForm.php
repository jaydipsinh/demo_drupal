<?php

namespace Drupal\shorten_cs\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormBuilderInterface;

/**
 * Settings form.
 */
class CustomServicesEditForm extends ConfigFormBase {

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
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * CustomServicesEditForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(
        ModuleHandlerInterface $module_handler,
        Connection $database,
        FormBuilderInterface $form_builder,
        ConfigFactoryInterface $config_factory
    ) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
    $this->database = $database;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('module_handler'),
          $container->get('database'),
          $container->get('form_builder'),
          $container->get('config.factory')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shorten_cs_edit';
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
  public function buildForm(array $form, FormStateInterface $form_state, $service = NULL) {
    $form = $this->formBuilder->getForm('Drupal\shorten_cs\Form\CustomServicesAddForm');
    $sid = $service;
    $service = $this->database->select('shorten_cs', 's')
      ->fields('s')
      ->condition('sid', intval($sid))
      ->execute()
      ->fetchAssoc();

    foreach (['name', 'url', 'type', 'tag'] as $key) {
      $form[$key]['#default_value'] = $service[$key];
      unset($form[$key]['#value']);
    }

    $form['sid'] = [
      '#type' => 'value',
      '#value' => $service['sid'],
    ];
    $form['old_name'] = [
      '#type' => 'value',
      '#value' => $service['name'],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $v = $form_state->getValues();
    $config_factory = $this->configFactory();
    $record = [];

    foreach (['name', 'url', 'type', 'tag', 'sid'] as $key) {
      $record[$key] = $v[$key];
    }

    $this->database->merge('shorten_cs')->fields($record)->key(['sid'])->execute();

    if ($v['old_name'] == $this->config('shorten.settings')->get('shorten_service', 'is.gd')) {
      $config_factory->getEditable('shorten.settings')->set('shorten_service', $v['name']);
    }

    if ($v['old_name'] == $this->config('shorten.settings')->get('shorten_service_backup', 'TinyURL')) {
      $config_factory->getEditable('shorten.settings')->set('shorten_service', $v['name']);
    }

    $this->messenger()->addStatus(
      $this->t(
              'The changes to service %service have been saved.',
              [
                '%service' => $record['name'],
              ]
          )
      );

    $form_state->setRedirect('shorten_cs.theme_shorten_cs_admin');
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

}
