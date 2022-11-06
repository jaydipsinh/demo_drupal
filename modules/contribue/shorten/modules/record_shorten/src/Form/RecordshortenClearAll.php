<?php

namespace Drupal\record_shorten\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Report form.
 */
class RecordshortenClearAll extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'record_shorten_clear_all';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['warning'] = [
      '#markup' => '<p><strong>' . $this->t(
          'Warning: there is no confirmation page. Cleared records are permanently deleted.'
      ) . '</strong></p>',
    ];
    $form['note'] = [
      '#markup' => '<p>' . $this->t('Note: clearing records does not clear the Shorten URLs cache.') . ' ' .
      $this->t('Also, URLs already in the cache are not recorded again.') . '</p>',
    ];
    $form['clear'] = [
      '#type' => 'submit',
      '#value' => $this->t('Clear all records'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::database()->query("TRUNCATE TABLE {record_shorten}");
    $this->messenger()->addStatus('Shorten Url records cleared.');
  }

}
