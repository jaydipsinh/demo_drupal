<?php

/**
 * @file
 * A Views field handler for users' IP address.
 */

/**
 * Restricts access to viewing the IP address field.
 * There doesn't seem to be a standard for what permissions are necessary.
 */
class RecordShortenViewsHandlerFieldHostname extends views_handler_field {

  /**
   * {@inheritdoc}
   */
  public function optionsForm(&$form, &$form_state) {
    parent::optionsForm($form, $form_state);
    $form['notice'] = [
      '#value' => '<p class="form-item"><strong>' .
      $this->t('Users must have both the "administer users" and "access site reports" permission to view this field.')
      . '</strong></p>',
      '#weight' => -10,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($values) {
    if (!\Drupal::currentUser()->hasPermission(
          'administer users'
      ) || !\Drupal::currentUser()->hasPermission(
          'access site reports'
      )
      ) {
      return;
    }
    return parent::render($values);
  }

}
