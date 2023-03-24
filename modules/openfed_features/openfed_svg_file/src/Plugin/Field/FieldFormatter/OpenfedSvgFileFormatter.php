<?php

namespace Drupal\openfed_svg_file\Plugin\Field\FieldFormatter;

use DomDocument;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Plugin\Field\FieldFormatter\FileFormatterBase;

/**
 * Plugin implementation of the 'openfed_svg_file' formatter.
 *
 * @FieldFormatter(
 *   id = "openfed_svg_file_formatter",
 *   label = @Translation("Openfed SVG file Formatter"),
 *   field_types = {
 *     "openfed_svg_file"
 *   }
 * )
 */
class OpenfedSvgFileFormatter extends FileFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings();
  }

  /**
   * @todo Possible useless method overriding , check if necessary.
   * 
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state);
  }

  /**
   * @todo Possible useless method overriding , check if necessary.
   *
   * {@inheritdoc}
   */
  public function settingsSummary() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $attributes = [];

    foreach ($items as $delta => $item) {
      if ($item->entity) {
        $type = $item->type;
        $svg_data = '';

        $attributes['width'] = $item->width;
        $attributes['height'] = $item->height;
        if ($item->alt && $type != 'object') {
          $attributes['alt'] = $item->alt;
        }
        if ($item->title) {
          $attributes['title'] = $item->title;
        }
        $uri = $item->entity->getFileUri();

        if ($type == 'inline') {
          $svg_data = NULL;
          $svg_file = file_exists($uri) ? file_get_contents($uri) : NULL;
          if ($svg_file) {
            $dom = new DomDocument();
            libxml_use_internal_errors(TRUE);
            $dom->loadXML($svg_file);
            if (isset($dom->documentElement)) {
              $dom->documentElement->setAttribute('height', $attributes['height']);
              $dom->documentElement->setAttribute('width', $attributes['width']);
            }
            $svg_data = $dom->C14N();
          }
        }

        $elements[$delta] = [
          '#theme' => 'openfed_svg_file__' . $type,
          '#attributes' => $attributes,
          '#uri' => ($type == 'inline') ? NULL : $uri,
          '#alt_text' => ($type == 'object') ? $item->alt : NULL,
          '#svg_data' => ($type == 'inline') ? $svg_data : NULL,
        ];
      }
    }

    return $elements;
  }

}
