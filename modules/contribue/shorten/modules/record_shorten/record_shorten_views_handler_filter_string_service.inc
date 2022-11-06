<?php

/**
 * @file
 * Allows choosing the Shorten URLs service.
 */

/**
 * Sets up a form for choosing the Shorten URLs service.
 */
class RecordShortenViewsHandlerFilterStringService extends views_handler_filter_many_to_one {

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    $this->value_options = array_combine(
          array_keys(
              \Drupal::moduleHandler()
                ->invokeAll(
                  'shorten_service'
              )
          ),
          array_keys(
              \Drupal::moduleHandler()->invokeAll(
                  'shorten_service'
              )
          )
      );
  }

}
