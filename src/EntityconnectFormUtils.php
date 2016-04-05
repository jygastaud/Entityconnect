<?php
/**
 * @file
 * Contains \Drupal\entityconnect\EntityconnectWidgetProcessor.
 *
 * @author Agnes Chisholm <amaria@66428.no-reply.drupal.org>
 */

namespace Drupal\entityconnect;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\entityconnect\Form\AdministrationForm;
use Drupal\field\Entity\FieldConfig;
use Drupal\views\Views;

/**
 * Contains form alter, callbacks and general form utility methods for
 * entityconfig module.
 */
class EntityconnectFormUtils {

  /**
   * Adds entityconnect settings as 3rd party settings to the entity reference
   * field config.
   *
   * @param array $form
   *   The form to add to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  public static function fieldConfigEditFormAlter(array &$form, FormStateInterface $form_state) {
    $field = $form_state->getFormObject()->getEntity();
    $type = $field->getType();

    if ($type == 'entity_reference') {
      $defaults = $field->getThirdPartySettings('entityconnect');
      if (!$defaults) {
        $config = \Drupal::config('entityconnect.administration_config');
        $defaults = $config->get();
      }
      AdministrationForm::attach($form['third_party_settings'], $defaults);
    }
  }

  /**
   * Add the entityconnect button(s) to the form here since we only have access
   * to the actual widget element in hook_field_widget_form_alter().
   *
   * @param array $form
   *   The form to add to.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  public static function entityFormAlter(array &$form, FormStateInterface $form_state) {

    // Get the applicable entity reference fields from the form.
    $ref_fields = static::getReferenceFields($form, $form_state);

    // Attach our custom process callback to each entity reference field element.
    if ($ref_fields) {
      foreach ($ref_fields as $field) {
        // Add our #process callback
        $form[$field]['#process'][] = array(
          '\Drupal\entityconnect\EntityconnectWidgetProcessor',
          'process',
        );

        // Add our #validate callback to the entity form.
        // This prevents the exception on entityconnect elements caused by submitting
        // the form without using the entityconnect buttons.
        $form['#validate'] = !isset($form['#validate']) ? array() : $form['#validate'];
        array_unshift($form['#validate'], array(
          '\Drupal\entityconnect\EntityconnectFormUtils',
          'validateForm',
        ));
      }
    }

  }

  /**
   * Form API #validate callback for a form with entity_reference fields.
   *
   * Removes the entityconnect button values from form_state to prevent
   * exceptions.
   *
   * @param array $form
   *   The form to validate.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   */
  public static function validateForm(array &$form, FormStateInterface $form_state) {
    $ref_fields = static::getReferenceFields($form, $form_state);

    foreach ($ref_fields as $field) {
      // Extract the values for this field from $form_state->getValues().
      $path = array_merge($form['#parents'], array($field));
      $key_exists = NULL;
      $ref_values = NestedArray::getValue($form_state->getValues(), $path, $key_exists);

      if ($key_exists) {
        foreach ($ref_values as $key => $value) {
          if (strpos($key, '_entityconnect') !== FALSE) {
            $form_state->unsetValue(array_merge($path, [$key]));
          }
        }
      }
    }
  }

  /**
   * Extracts all reference fields from the given form.
   *
   * @param array $form
   *   The form to extract fields from.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   */
  public static function getReferenceFields(array &$form, FormStateInterface $form_state) {

    $ref_fields = array();
    $entity = NULL;

    // Get the entity if this is an entity form.
    if (method_exists($form_state->getFormObject(), 'getEntity')) {
      $entity = $form_state->getFormObject()->getEntity();
    }

    // Bail out if not a fieldable entity form.
    if (empty($entity) || !$entity->getEntityType()->isSubclassOf('\Drupal\Core\Entity\FieldableEntityInterface')) {
      return $ref_fields;
    }

    // Get the entity reference elements from this form.
    $field_defs = $entity->getFieldDefinitions();
    foreach (Element::children($form) as $child) {
      if (!isset($field_defs[$child])) {
        continue;
      }
      $field_definition = $field_defs[$child];
      if ($field_definition->getType() == 'entity_reference') {
        // Fields must be configurable.
        if ($field_definition instanceof FieldConfig) {
          $ref_fields[] = $child;
        }
      }
    }

    return $ref_fields;

  }

}
