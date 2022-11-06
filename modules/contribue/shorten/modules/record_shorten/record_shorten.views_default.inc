<?php

/**
 * @file
 * Holds the default Views for the Record Shortened URLs module.
 */

/**
 * Implements hook_views_default_views().
 */
function record_shorten_views_default_views() {
  $view = new view();
  $view->name = 'record_shorten';
  $view->description = t('Shortened URLs');
  $view->tag = '';
  $view->view_php = '';
  $view->base_table = 'record_shorten';
  $view->is_cacheable = FALSE;
  $view->api_version = 2;
  $view->disabled = FALSE;
  $handler = $view->new_display('default', 'Defaults', 'default');
  $handler->override_option('fields', [
    'original' => [
      'label' => 'Original',
      'alter' => [
        'alter_text' => 0,
        'text' => '',
        'make_link' => 1,
        'path' => '[original]',
        'link_class' => '',
        'alt' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ],
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'exclude' => 0,
      'id' => 'original',
      'table' => 'record_shorten',
      'field' => 'original',
      'relationship' => 'none',
    ],
    'short' => [
      'label' => 'Shortened URL',
      'alter' => [
        'alter_text' => 0,
        'text' => '',
        'make_link' => 1,
        'path' => '[short]',
        'external' => 1,
        'link_class' => '',
        'alt' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ],
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'exclude' => 0,
      'id' => 'short',
      'table' => 'record_shorten',
      'field' => 'short',
      'relationship' => 'none',
    ],
    'service' => [
      'label' => 'Service',
      'alter' => [
        'alter_text' => 0,
        'text' => '',
        'make_link' => 0,
        'path' => '',
        'link_class' => '',
        'alt' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ],
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'exclude' => 0,
      'id' => 'service',
      'table' => 'record_shorten',
      'field' => 'service',
      'relationship' => 'none',
    ],
    'created' => [
      'label' => 'Created',
      'alter' => [
        'alter_text' => 0,
        'text' => '',
        'make_link' => 0,
        'path' => '',
        'link_class' => '',
        'alt' => '',
        'prefix' => '',
        'suffix' => '',
        'target' => '',
        'help' => '',
        'trim' => 0,
        'max_length' => '',
        'word_boundary' => 1,
        'ellipsis' => 1,
        'html' => 0,
        'strip_tags' => 0,
      ],
      'empty' => '',
      'hide_empty' => 0,
      'empty_zero' => 0,
      'date_format' => 'small',
      'custom_date_format' => '',
      'exclude' => 0,
      'id' => 'created',
      'table' => 'record_shorten',
      'field' => 'created',
      'relationship' => 'none',
    ],
  ]);
  $handler->override_option('sorts', [
    'created' => [
      'order' => 'DESC',
      'id' => 'created',
      'table' => 'record_shorten',
      'field' => 'created',
      'relationship' => 'none',
    ],
    'sid' => [
      'order' => 'DESC',
      'id' => 'sid',
      'table' => 'record_shorten',
      'field' => 'sid',
      'relationship' => 'none',
    ],
  ]);
  $handler->override_option('filters', [
    'service' => [
      'operator' => 'or',
      'value' => [],
      'group' => '0',
      'exposed' => TRUE,
      'expose' => [
        'use_operator' => 0,
        'operator' => 'service_op',
        'identifier' => 'service',
        'label' => 'Service',
        'optional' => 1,
        'single' => 1,
        'remember' => 1,
        'reduce' => 0,
      ],
      'id' => 'service',
      'table' => 'record_shorten',
      'field' => 'service',
      'relationship' => 'none',
      'reduce_duplicates' => 0,
    ],
  ]);
  $handler->override_option('access', [
    'type' => 'perm',
    'perm' => 'administer site configuration',
  ]);
  $handler->override_option('cache', [
    'type' => 'none',
  ]);
  $handler->override_option('title', t('Shortened URLs'));
  $handler->override_option('empty', t('No URLs have been shortened yet.'));
  $handler->override_option('empty_format', '1');
  $handler->override_option('use_ajax', TRUE);
  $handler->override_option('use_pager', '1');
  $handler->override_option('style_plugin', 'table');
  $handler->override_option('style_options', [
    'grouping' => '',
    'override' => 1,
    'sticky' => 0,
    'order' => 'asc',
    'columns' => [
      'original' => 'original',
      'short' => 'short',
      'service' => 'service',
    ],
    'info' => [
      'original' => [
        'sortable' => 0,
        'separator' => '',
      ],
      'short' => [
        'sortable' => 0,
        'separator' => '',
      ],
      'service' => [
        'sortable' => 0,
        'separator' => '',
      ],
      'created' => [
        'sortable' => 1,
        'separator' => '',
      ],
    ],
    'default' => '-1',
  ]);
  $views[$view->name] = $view;
  return $views;
}
