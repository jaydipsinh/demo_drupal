<?php

namespace Drupal\shorten\Form;

use Drupal\Core\Form\FormBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds a form which allows shortening of a URL via the UI.
 */
class ShortenShortenForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'shorten_form_shorten';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $form_state_values = $form_state->getValues();
    $storage = &$form_state->getStorage();

    $form['#attached']['library'][] = 'shorten/shorten';

    // Form elements between ['opendiv'] and ['closediv'] will be refreshed
    // via AHAH on form submission.
    $form['opendiv'] = [
      '#markup' => '<div id="shorten_replace">',
    ];
    if (empty($storage)) {
      $storage = ['step' => 0];
    }
    if (isset($storage['short_url'])) {
      // This whole "step" business keeps the form element from being cached.
      $form['shortened_url_' . $storage['step']] = [
        '#type' => 'textfield',
        '#title' => $this->t('Shortened URL'),
        '#default_value' => $storage['short_url'],
        '#size' => 25,
        '#attributes' => ['class' => ['shorten-shortened-url']],
      ];
    }
    $form['url_' . $storage['step']] = [
      '#type' => 'textfield',
      '#title' => $this->t('URL'),
      '#default_value' => '',
      '#required' => TRUE,
      '#size' => 25,
      '#maxlength' => 2048,
      '#attributes' => ['class' => ['shorten-long-url']],
    ];
    // Form elements between ['opendiv'] and ['closediv'] will be refreshed
    // via AHAH on form submission.
    $form['closediv'] = [
      '#markup' => '</div>',
    ];
    $last_service = NULL;
    if (isset($storage['service'])) {
      $last_service = $storage['service'];
    }
    $service = _shorten_service_form($last_service);

    if (is_array($service)) {
      $form['service'] = $service;
    }
    $form['shorten'] = [
      '#type' => 'submit',
      '#value' => $this->t('Shorten'),
      '#ajax' => [
        'callback' => 'shorten_save_js',
        'wrapper' => 'shorten_replace',
        'effect' => 'fade',
        'method' => 'replace',
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $storage = &$form_state->getStorage();

    $url = $values['url_' . $storage['step']];
    if (mb_strlen($url) > 4) {
      if (!strpos($url, '.', 1)) {
        $form_state->setErrorByName('url', $this->t('Please enter a valid URL.'));
      }
    }
    else {
      $form_state->setErrorByName('url', $this->t('Please enter a valid URL.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $storage = &$form_state->getStorage();

    $service = '';
    if (isset($values['service'])) {
      $service = $values['service'];
    }
    $shortened = shorten_url($values['url_' . $storage['step']], $service);
    if (isset($values['service'])) {
      $_SESSION['shorten_service'] = $values['service'];
    }
    $this->messenger()->addStatus(
          $this->t(
              '%original was shortened to %shortened',
              [
                '%original' => $values['url_' . $storage['step']],
                '%shortened' => $shortened,
              ]
          )
      );

    $form_state->setRebuild();

    if (empty($storage)) {
      $storage = [];
    }
    $storage['short_url'] = $shortened;
    $storage['service']   = empty($values['service']) ? '' : $values['service'];
    if (isset($storage['step'])) {
      $storage['step']++;
    }
    else {
      $storage['step'] = 0;
    }

    $form_state->setStorage($storage);
  }

}
