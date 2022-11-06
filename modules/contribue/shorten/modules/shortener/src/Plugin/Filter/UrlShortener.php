<?php

namespace Drupal\shortener\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a filter to limit allowed HTML tags.
 *
 * @Filter(
 *   id = "url_shortener",
 *   title = @Translation("URL shortener"),
 *   description = @Translation("Replaces URLs with a shortened version."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE,
 *   settings = {
 *     "shortener_url_behavior" = "short",
 *     "shortener_url_length" = 72
 *   },
 *   weight = -20
 * )
 */
class UrlShortener extends FilterBase {

  /**
   * Builds the settings form for the input filter.
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['shortener_url_behavior'] = [
      '#type' => 'radios',
      '#title' => $this->t('Behavior'),
      '#default_value' => $this->settings['shortener_url_behavior'],
      '#options' => [
        'short' => $this->t('Display the shortened URL by default, and add an "(expand)"/"(shorten)" link'),
        'strict' => $this->t('Display the shortened URL by default, and do not allow expanding it'),
        'long' => $this->t('Display the full URL by default, and add a "(shorten)"/"(expand)" link'),
      ],
    ];
    $form['shortener_url_length'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Maximum link text length'),
      '#default_value' => $this->settings['shortener_url_length'],
      '#maxlength' => 4,
      '#description' => $this->t(
          'URLs longer than this number of characters will be truncated to prevent long strings that break formatting.
            The link itself will be retained; just the text portion of the link will be truncated.'
      ),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {

    $length = $this->settings['shortener_url_length'];
    // Pass length to regexp callback.
    _filter_url_trim('', $length);
    // Pass behavior to regexp callback.
    $this->shortenerUrlBehavior(NULL, FALSE, $this->settings['shortener_url_behavior'], $length);

    $text = ' ' . $text . ' ';

    // Match absolute URLs.
    $text = preg_replace_callback(
          "`(
<p>|<li>|<br\s*/?>|[ \n\r\t\(]
)
(
(
http://|https://
)
(
[a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+*~#&=/;-]
)
)
(
[.,?!]*?
)
(
?=
(
</p>|</li>|<br\s*/?>|[ \n\r\t\)]
)
)`i",
          [
            get_class(
                $this
            ),
            'shortenerUrlBehavior',
          ],
          $text
      );

    // Match www domains/addresses.
    $text = preg_replace_callback(
          "`(
<p>|<li>|[ \n\r\t\(]
)
(
www\.[a-zA-Z0-9@:%_+*~#?&=.,/;-]*[a-zA-Z0-9@:%_+~#\&=/;-]
)
(
[.,?!]*?
)
(
?=
(
</p>|</li>|<br\s*/?>|[ \n\r\t\)]
)
)
`i",
          [
            get_class(
              $this
            ),
            'shortenerUrlParsePartialLinks',
          ],
          $text
      );
    $text = substr($text, 1, -1);

    // Return new FilterProcessResult($text);
    $result = new FilterProcessResult($text);
    $result->setAttachments([
      'library' => ['shortener/shortener'],
    ]);

    return $result;
  }

  /**
   * Processes matches on partial URLs and returns the "fixed" version.
   */
  public function shortenerUrlParsePartialLinks($match) {
    return $this->shortenerUrlBehavior($match, TRUE);
  }

  /**
   * Determines the link caption based on the filter behavior setting.
   */
  public function shortenerUrlBehavior($match, $partial = FALSE, $behavior = NULL, $max_length = NULL) {
    static $_behavior;
    if ($behavior !== NULL) {
      $_behavior = $behavior;
    }
    static $_max_length;
    if ($max_length !== NULL) {
      $_max_length = $max_length;
    }

    if (!empty($match)) {
      $match[2] = Html::decodeEntities($match[2]);
      $caption = '';
      $href = $match[2];
      $title = UrlHelper::filterBadProtocol($match[2]);
      if ($_behavior == 'short' || $_behavior == 'strict') {
        $caption = shorten_url($match[2]);
        $href = $caption;
      }
      else {
        $caption = Html::escape(_filter_url_trim($match[2]));
        if ($partial) {
          $href = 'http://' . UrlHelper::filterBadProtocol($match[2]);
        }
        $title = shorten_url($match[2]);
      }
      return $match[1] . '<a href="' . $href . '" title="' . $title . '" class="shortener-length-'
              . $_max_length . ' shortener-link shortener-' . $_behavior . '">' . $caption .
              '</a>' . $match[$partial ? 3 : 5];
    }
    return '';
  }

}
