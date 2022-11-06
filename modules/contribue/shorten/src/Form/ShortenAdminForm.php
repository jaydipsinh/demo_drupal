<?php

namespace Drupal\shorten\Form;

use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Settings form.
 */
class ShortenAdminForm extends ConfigFormBase {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ShortenAdminForm constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ModuleHandlerInterface $module_handler, ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
          $container->get('module_handler'),
          $container->get('config.factory')
      );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shorten_admin';
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
    $form['shorten_www'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use "www." instead of "http://"'),
      '#description' => $this->t('"www." is shorter, but "http://" is automatically link-ified by more services.'),
      '#default_value' => $this->config('shorten.settings')->get('shorten_www'),
    ];
    $methods = [];
    if (function_exists('file_get_contents')) {
      $methods['php'] = $this->t('PHP');
    }
    if (function_exists('curl_exec')) {
      $methods['curl'] = $this->t('cURL');
    }
    if ($this->config('shorten.settings')->get('shorten_method') != 'none') {
      $this->configFactory->getEditable('shorten.settings')
        ->set('shorten_method', _shorten_method_default())->save();
    }
    if (empty($methods)) {
      $form['shorten_method'] = [
        '#type' => 'radios',
        '#title' => $this->t('Method'),
        '#description' => '<p>' . $this->t('The method to use to retrieve the abbreviated URL.') . '</p>' .
        '<p><strong>' . $this->t(
            'Your PHP installation does not support the URL abbreviation feature of the Shorten module.'
        ) . '</strong> ' .
          $this->t(
            'You must compile PHP with either the cURL library or the file_get_contents()
                function to use this option.'
        ) . '</p>',
        '#default_value' => 'none',
        '#options' => ['none' => $this->t('None')],
        '#disabled' => TRUE,
      ];
      $form['shorten_service'] = [
        '#type' => 'radios',
        '#title' => $this->t('Service'),
        '#description' => $this->t('The default service to use to create the abbreviated URL.'),
        '#default_value' => 'none',
        '#options' => ['none' => $this->t('None')],
      ];
      $form['shorten_service_backup'] = [
        '#type' => 'radios',
        '#title' => $this->t('Backup Service'),
        '#description' => $this->t('The service to use to create the abbreviated URL if the primary service is down.'),
        '#default_value' => 'none',
        '#options' => ['none' => $this->t('None')],
      ];
    }
    else {
      $form['shorten_method'] = [
        '#type' => 'radios',
        '#title' => $this->t('Method'),
        '#description' => $this->t(
            'The method to use to retrieve the abbreviated URL. cURL is much faster, if available.'
        ),
        '#default_value' => $this->config('shorten.settings')->get('shorten_method'),
        '#options' => $methods,
      ];
      $all_services = $this->moduleHandler->invokeAll('shorten_service');
      $services = [];
      foreach ($all_services as $key => $value) {
        $services[$key] = $key;
      }
      $services['none'] = $this->t('None');
      $form['shorten_service'] = [
        '#type' => 'select',
        '#title' => $this->t('Service'),
        '#description' => $this->t('The default service to use to create the abbreviated URL.') . ' ' .
        $this->t('If a service is not shown in this list, you probably need to configure it in the Shorten API Keys tab.'),
        '#default_value' => $this->config('shorten.settings')->get('shorten_service'),
        '#options' => $services,
      ];
      $form['shorten_service_backup'] = [
        '#type' => 'select',
        '#title' => $this->t('Backup Service'),
        '#description' => $this->t(
            'The service to use to create the abbreviated URL if the primary or requested service is down.'
        ),
        '#default_value' => $this->config('shorten.settings')->get('shorten_service_backup'),
        '#options' => $services,
      ];
      $form['shorten_show_service'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Show the list of URL shortening services in the user interface'),
        '#default_value' => $this->config('shorten.settings')->get('shorten_show_service'),
        '#description' => $this->t('Allow users to choose which service to use in the Shorten URLs block and page.'),
      ];
    }
    $form['shorten_use_alias'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Shorten aliased URLs where possible'),
      '#description' => $this->t('Where possible, generate shortened URLs based on the aliased version of a URL.')
      . ' <strong>' . $this->t('Some integrated modules ignore this.') . '</strong>',
      '#default_value' => $this->config('shorten.settings')->get('shorten_use_alias'),
    ];
    $form['shorten_timeout'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Time out URL shortening requests after'),
      '#field_suffix' => ' ' . $this->t('seconds'),
      '#description' => $this->t(
          'Cancel retrieving a shortened URL if the URL
           shortening service takes longer than this amount of time to respond.'
      ) . ' ' .
      $this->t(
          'Lower values (or shorter timeouts)
          mean your site will respond more quickly if your URL shortening service is down.'
      ) . ' ' .
      $this->t(
          'However, higher values (or longer timeouts)
           give the URL shortening service more of a chance to return a value.'
      ) . ' ' .
      $this->t(
          'If a request to the primary service times out, the secondary service is used.
           If the secondary service times out, the original (long) URL is used.'
      ) . ' ' .
      $this->t('You must enter a nonnegative integer. Enter 0 (zero) to wait for a response indefinitely.'),
      '#size' => 3,
      '#required' => TRUE,
      '#default_value' => $this->config('shorten.settings')->get('shorten_timeout'),
    ];
    $form['shorten_cache_duration'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cache shortened URLs for'),
      '#field_suffix' => ' ' . $this->t('seconds'),
      '#description' => $this->t('Shortened URLs are stored after retrieval to improve performance.') . ' ' .
      $this->t('Enter the number of seconds for which you would like the shortened URLs to be stored.') . ' ' .
      $this->t('Leave this field blank to store shortened URLs indefinitely (although this is not recommended).') . ' ' .
      $this->t('The default value is 1814400 (3 weeks).'),
      '#size' => 11,
      '#default_value' => $this->config('shorten.settings')->get('shorten_cache_duration'),
    ];
    $form['shorten_cache_fail_duration'] = [
      '#type' => 'textfield',
      '#title' => $this->t('On failure, cache full URLs for'),
      '#field_suffix' => ' ' . $this->t('seconds'),
      '#description' => $this->t(
          'When a shortener service is unavilable,
           the full URL will be cached temporarily to prevent more requests from overloading the server.'
      ) . ' ' .
      $this->t(
          'Enter the number of seconds for which you would like
           to store these full URLs when shortening the URL fails.'
      ) . ' ' .
      $this->t('The default value is 1800 (30 minutes).'),
      '#size' => 11,
      '#required' => TRUE,
      '#default_value' => $this->config('shorten.settings')->get('shorten_cache_fail_duration'),
    ];
    $form['shorten_cache_clear_all'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Clear Shorten URLs cache when all Drupal caches are cleared.'),
      '#description' => $this->t(
          'Sometimes Drupal automatically clears all caches,
           such as after running database updates.'
      ) . ' ' .
      $this->t(
          'However, regenerating the cache of shortened URLs can be performance-intensive,
             and the cache does not affect Drupal behaviors.'
      ) . ' ' .
      $this->t('To avoid regenerating this cache after clearing all Drupal caches, un-check this option.') . ' ' .
      $this->t(
          'Note that if you need to completely clear this cache,
             un-checking this option will require that you do it manually.'
      ),
      '#default_value' => $this->config('shorten.settings')->get('shorten_cache_clear_all'),
    ];
    unset($services['none']);
    if (empty(unserialize($this->config('shorten.settings')->get('shorten_invisible_services')))) {
      $this->configFactory()->getEditable('shorten.settings')
        ->set('shorten_invisible_services', serialize([]))->save();
    }
    $form['shorten_invisible_services'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Disallowed services'),
      '#description' => $this->t(
          'Checking the box next to a service will make it <strong>unavailable</strong>
 for use in the Shorten URLs block and page.'
      ) . ' ' .
      $this->t('If you disallow all services, the primary service will be used.'),
      '#default_value' => unserialize($this->config('shorten.settings')->get('shorten_invisible_services')),
    // array_map('check_plain', $services),.
      '#options' => $services,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->config('shorten.settings')
      ->set('shorten_www', $values['shorten_www'])
      ->set('shorten_method', $values['shorten_method'])
      ->set('shorten_service', $values['shorten_service'])
      ->set('shorten_service_backup', $values['shorten_service_backup'])
      ->set('shorten_show_service', $values['shorten_show_service'])
      ->set('shorten_use_alias', $values['shorten_use_alias'])
      ->set('shorten_timeout', $values['shorten_timeout'])
      ->set('shorten_cache_duration', $values['shorten_cache_duration'])
      ->set('shorten_cache_fail_duration', $values['shorten_cache_fail_duration'])
      ->set('shorten_cache_clear_all', $values['shorten_cache_clear_all'])
      ->set('shorten_invisible_services', serialize($values['shorten_invisible_services']))
      ->save();

    // Changed settings usually mean that different URLs should be used.
    // cache_clear_all('*', 'cache_shorten', TRUE);.
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $v = $form_state->getValues();
    if ($v['shorten_service'] == $v['shorten_service_backup'] && $v['shorten_service_backup'] != 'none') {
      $form_state->setErrorByName(
            'shorten_service_backup',
            $this->t(
                'You must select a backup abbreviation service that is different than your primary service.'
            )
        );
    }
    elseif (($v['shorten_service'] == 'bit.ly' && $v['shorten_service_backup'] == 'j.mp') ||
      ($v['shorten_service'] == 'j.mp' && $v['shorten_service_backup'] == 'bit.ly')) {
      $form_state->setErrorByName(
            'shorten_service_backup',
            $this->t(
                'j.mp and bit.ly are the same service.'
            ) . ' ' .
            $this->t(
                'You must select a backup abbreviation service that is different than your primary service.'
            )
            );
    }
    if ($v['shorten_service'] == 'none' && $v['shorten_service_backup'] != 'none') {
      $form_state->setErrorByName(
            $this->t(
                'You have selected a backup URL abbreviation service, but no primary service.'
            ) . ' ' .
            $this->t(
                'Your URLs will not be abbreviated with these settings.'
            )
        );
    }
    if ($v['shorten_cache_duration'] !== '' && (
      !is_numeric($v['shorten_cache_duration']) ||
      round($v['shorten_cache_duration']) != $v['shorten_cache_duration'] ||
      $v['shorten_cache_duration'] < 0
      )) {
      $form_state->setErrorByName(
            'shorten_cache_duration',
            $this->t(
                'The cache duration must be a positive integer or left blank.'
            )
        );
    }
    if (!is_numeric($v['shorten_cache_fail_duration']) ||
      round($v['shorten_cache_fail_duration']) != $v['shorten_cache_fail_duration'] ||
      $v['shorten_cache_fail_duration'] < 0
      ) {
      $form_state->setErrorByName(
            'shorten_cache_fail_duration',
            $this->t(
                'The cache fail duration must be a positive integer.'
            )
        );
    }
    if (!is_numeric($v['shorten_timeout']) || round($v['shorten_timeout']) !=
        $v['shorten_timeout'] || $v['shorten_timeout'] < 0) {
      $form_state->setErrorByName(
            'shorten_timeout',
            $this->t(
                'The timeout duration must be a nonnegative integer.'
            )
        );
    }
  }

}
