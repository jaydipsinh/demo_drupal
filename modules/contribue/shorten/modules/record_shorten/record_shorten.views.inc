<?php

/**
 * @file
 * Provide views data and handlers for the Record Shortened URLs module.
 */

/**
 * Implements hook_views_data().
 */
function record_shorten_views_data() {
  $data = [];
  $data['record_shorten'] = [];
  $data['record_shorten']['table'] = [];

  // Basic table information.
  $data['record_shorten']['table']['group'] = t('Shortened URLs');

  $data['record_shorten']['table']['provider'] = 'record_shorten';

  // Advertise this table as a possible base table.
  $data['record_shorten']['table']['base'] = [
    'field' => 'sid',
    'title' => t('Shortened URLs'),
    'help' => t('Listings of URLs shortened by the Shorten URLs module.'),
    'weight' => 10,
  ];
  $data['users']['table']['join'] = [
    'record_shorten' => [
      'left_field' => 'uid',
      'field' => 'uid',
      'type' => 'LEFT',
    ],
  ];

  $data['record_shorten']['sid'] = [
    'title' => t('Shorten ID'),
    'help' => t('The ID of the recorded URL.'),
    'field' => [
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'handler' => 'views_handler_filter_numeric',
    ],
    'sort' => [
      'handler' => 'views_handler_sort',
    ],
    'argument' => [
      'handler' => 'views_handler_argument_numeric',
    ],
  ];

  $data['record_shorten']['original'] = [
    'title' => t('Original URL'),
    'help' => t('The original, long, unshortened URL.'),
    'field' => [
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'handler' => 'views_handler_filter_string',
    ],
    'sort' => [
      'handler' => 'views_handler_sort',
    ],
    'argument' => [
      'handler' => 'views_handler_argument_string',
    ],
  ];

  $data['record_shorten']['short'] = [
    'title' => t('Shortened URL'),
    'help' => t('The new, computed, shortened URL.'),
    'field' => [
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'handler' => 'views_handler_filter_string',
    ],
    'sort' => [
      'handler' => 'views_handler_sort',
    ],
    'argument' => [
      'handler' => 'views_handler_argument_string',
    ],
  ];

  $data['record_shorten']['service'] = [
    'title' => t('Service'),
    'help' => t('The service used to shorten the URL.'),
    'field' => [
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'handler' => 'RecordShortenViewsHandlerFilterStringService',
    ],
    'sort' => [
      'handler' => 'views_handler_sort',
    ],
    'argument' => [
      'handler' => 'views_handler_argument_string',
    ],
  ];

  $data['record_shorten']['uid'] = [
    'title' => t('User ID'),
    'help' => t('The User ID of the user who created the shortened URL.'),
    'field' => [
      'handler' => 'views_handler_field',
      'click sortable' => TRUE,
    ],
    'filter' => [
      'handler' => 'views_handler_filter_numeric',
    ],
    'sort' => [
      'handler' => 'views_handler_sort',
    ],
    'argument' => [
      'handler' => 'views_handler_argument_numeric',
    ],
  ];

  $data['record_shorten']['hostname'] = [
    'title' => t('IP Address'),
    'help' => t('The IP address of the user who created the shortened URL.'),
    'field' => [
      'handler' => 'RecordShortenViewsHandlerFieldHostname',
      'click sortable' => FALSE,
    ],
    'filter' => [
      'handler' => 'views_handler_filter_string',
    ],
  ];

  $data['record_shorten']['created'] = [
    'title' => t('Created time'),
    'help' => t('The time the shortened URL was created.'),
    'field' => [
      'handler' => 'views_handler_field_date',
      'click sortable' => TRUE,
    ],
    'sort' => [
      'handler' => 'views_handler_sort',
    ],
    'filter' => [
      'handler' => 'views_handler_filter_date',
    ],
  ];

  return $data;
}

/**
 * Implements hook_views_handlers().
 */
function record_shorten_views_handlers() {
  return [
    'info' => [
      'path' => drupal_get_path('module', 'record_shorten'),
    ],
    'handlers' => [
      'RecordShortenViewsHandlerFilterStringService' => [
        'parent' => 'views_handler_filter_many_to_one',
      ],
      'RecordShortenViewsHandlerFieldHostname' => [
        'parent' => 'views_handler_field',
      ],
    ],
  ];
}
