<?php

namespace Drupal\partial_date\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Provides a form element for partial date widget.
 *
 * @FormElement("checkbox_with_options")
 * @author CosminFr
 */
class CheckboxWithOptionsElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [[$class, 'processElement']],
      '#element_validate' => [[$class, 'validateElement']],
      '#theme' => 'checkbox_with_options',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Process callback.
   */
  public static function processElement(&$element) {

    $options = $element['#options'] ?? [];
    $optionType = $element['#option_type'] ?? 'radios';
    $cbValue = $element['#checkbox_value'] ?? '1';
    $defaultValue = $element['#default_value'] ?? $cbValue;
    unset($options[$cbValue]);
    // Using "master" checkbox instead of the title!
    $element['#title_display'] = 'invisible';
    $element['master'] = [
      '#type' => 'checkbox',
      '#id' => 'master_checkbox',
      '#title' => '<b>' . $element['#title'] . '</b>',
      '#default_value' => ($cbValue != $defaultValue),
    // '#value' => $cbValue,
    ];
    // #states property not working for radios element.
    // Must add a container to show/hide details.
    $element['details'] = [
      '#type' => 'container',
      '#states' => [
        'visible' => [
          ':input[id="master_checkbox"]' => ['checked' => TRUE],
        ],
      ],
      '#prefix' => '<div style="margin-left:25px">',
      '#sufix' => '</div>',
    ];
    $element['details']['options'] = [
      '#type' => $optionType,
      '#options' => $options,
      '#default_value' => ($cbValue != $defaultValue) ? $defaultValue : NULL,
    ];
    return $element;
  }

  /**
   * #element_validate callback.
   * {@inheritdoc}
   */
  public static function validateElement(&$element, FormStateInterface $form_state, &$complete_form) {
    $value = $element['#value'];
    // The valueCallback function (below) does set the element's #value property
    // BUT not to the $form_state leading to the setting value (for which the
    // element corresponds) to be not as expected.
    // Thus setValueForElement is forced here. (in valueCallback would not have worked!)
    if (!empty($value)) {
      // \Drupal::logger('partial_date')->debug('Setting result: ' . $value);
      $form_state->setValueForElement($element, $value);
    }
  }

  /**
   *
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    if ($input === FALSE) {
      $element += ['#default_value' => ''];
      return $element['#default_value'];
    }
    if (empty($input['master'])) {
      return $element['#checkbox_value'] ?? FALSE;
    }
    elseif (is_array($input['details']['options'])) {
      $result = [];
      foreach ($input['details']['options'] as $key => $value) {
        if (isset($value) && $value != 0) {
          $result[$key] = $value;
        }
      }
      return $result;
    }
    elseif (isset($input['details']['options'])) {
      return $input['details']['options'];
    }
    return NULL;
  }

}
