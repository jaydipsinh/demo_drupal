<?php

namespace Drupal\shorten_cs\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Database\Connection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Settings form.
 */
class CustomServicesDeleteForm extends ConfirmFormBase {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * CustomServicesDeleteForm constructor.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(Connection $database, ConfigFactoryInterface $config_factory) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('database'),
          $container->get('config.factory')
      );
  }

  /**
   * The ID of the item to delete.
   *
   * @var string
   */
  protected $id;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shorten_cs_delete';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the custom service %id?', ['%id' => $this->id]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('shorten_cs.theme_shorten_cs_admin');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('This action cannot be undone.');
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
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL, $service = NULL) {

    $item = $this->database->select('shorten_cs', 's')
      ->fields('s')
      ->condition('sid', intval($service))
      ->execute()
      ->fetchAssoc();

    $this->id = $item['name'];

    $form['service'] = [
      '#type' => 'value',
      '#value' => $item['name'],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $service = $form_state->getValues()['service'];
    $config_factory = $this->configFactory();

    if ($service == $this->config('shorten.settings')->get('shorten_service')) {
      if ($this->config('shorten.settings')->get('shorten_service_backup')) {
        $config_factory->getEditable('shorten.settings')->set('shorten_service', 'TinyURL')->save();
      }
      else {
        $config_factory->getEditable('shorten.settings')->set('shorten_service', 'is.gd')->save();
      }
      $this->messenger()->addStatus(
        $this->t(
                'The default URL shortening service was deleted,
                    so it has been reset to @service.',
                [
                  '@service' => \Drupal::config(
                          'shorten.settings'
                  )->get(
                          'shorten_service'
                  ),
                ]
            )
        );
    }

    if ($service == $this->config('shorten.settings')->get('shorten_service_backup')) {
      if ($this->config('shorten.settings')->get('shorten_service')) {
        $config_factory->getEditable('shorten.settings')->set('shorten_service_backup', 'is.gd')->save();
      }
      else {
        $config_factory->getEditable('shorten.settings')->set('shorten_service_backup', 'TinyURL')->save();
      }
      $this->messenger()->addStatus(
        $this->t(
                'The backup URL shortening service was deleted,
                     so it has been reset to @service.',
                [
                  '@service' => \Drupal::config(
                          'shorten.settings'
                  )->get(
                          'shorten_service_backup'
                  ),
                ]
            )
        );
    }

    $this->database->delete('shorten_cs')
      ->condition('name', $service)
      ->execute();
    $this->messenger()->addStatus($this->t('The service %service has been deleted.', ['%service' => $service]));
    $form_state->setRedirect('shorten_cs.theme_shorten_cs_admin');
  }

}
