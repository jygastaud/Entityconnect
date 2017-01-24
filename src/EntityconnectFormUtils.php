<?php

/**
 * @author Agnes Chisholm <amaria@66428.no-reply.drupal.org>
 */

namespace Drupal\entityconnect;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Url;
use Drupal\entityconnect\Form\AdministrationForm;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\FieldStorageConfigInterface;
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
    foreach (Element::getVisibleChildren($form) as $child) {
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

  /**
   * Alters child create form.
   *
   * We add a value field to hold the parent build_cache_id
   * then we add a cancel button that run entityconnect_child_form_cancel
   * and a new submit button.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param string $form_id
   * @param string $cache_id
   */
  public static function childFormAlter(array &$form, FormStateInterface $form_state, $form_id, $cache_id) {
    // Exclude some forms to be processed.
    $exclude_forms = array(
      'search_block_form'
    );
    // Allow other modules to alter exclude forms list.
    \Drupal::moduleHandler()->alter('entityconnect_exclude_forms', $exclude_forms);

    if (in_array($form_id, $exclude_forms)) {
      return;
    }

    $form['parent_build_cache_id'] = array(
      '#type' => 'value',
      '#value' => $cache_id,
    );
    $form['actions']['cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#submit' => array(array(
        '\Drupal\entityconnect\EntityconnectFormUtils',
        'childFormCancel',
      )),
      '#parent_build_cache_id' => $cache_id,
      '#limit_validation_errors' => array(),
      '#weight' => 1000,
    );

    switch ($form_id) {

      case 'node_delete_confirm':
        $form['actions']['submit']['#submit'] = $form['#submit'];
        $form['#submit'] = array();
        break;

      default:
        break;
    }

    if (isset($form['submit']['#submit'])) {
      $form['submit']['#submit'][] = array(
        '\Drupal\entityconnect\EntityconnectFormUtils',
        'childFormSubmit',
      );
    }
    else {
      foreach (array_keys($form['actions']) as $action) {
        if ($action != 'preview' && isset($form['actions'][$action]['#type']) && $form['actions'][$action]['#type'] === 'submit') {
          $form['actions'][$action]['#submit'][] = array(
            '\Drupal\entityconnect\EntityconnectFormUtils',
            'childFormSubmit',
          );
        }
      }
    }
   // Sets delete button on child create form.
   // On deletion submission of a child form we set:
   // our build cache id query params on the delete link url.
   if (strpos($form_id, '_confirm_delete') === false && strpos($form_id, 'delete_form') === false) {
      /** @var Url $url */
      $url = &$form['actions']['delete']['#url'];
      $url->setOption('query', array(
        'build_cache_id' => $cache_id,
        'child' => 1,
      ));
    }

    $data = array(
      'form' => &$form,
      'form_state' => &$form_state,
      'form_id' => $form_id
    );
    \Drupal::moduleHandler()->alter('entityconnect_child_form', $data);
  }

  /**
   * Complete entityreference field on parent form with the target_id value.
   *
   * This is for when we return to the parent page
   * we find the cached form and form_state clean up the form_state a bit
   * and mark it to be rebuilt.
   *
   * If the cache has a target_id we set that in the input.

   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param array $cache_data
   */
  public static function returnFormAlter(array &$form, FormStateInterface $form_state, array $cache_data) {
    if (empty($form_state->get('#entityconnect_processed'))) {
      $old_form = $cache_data['form'];
      /** @var FormStateInterface $old_form_state */
      $old_form_state = $cache_data['form_state'];

      // Save the storage and input from the original form state.
      $form_state->setStorage($old_form_state->getStorage());
      $form_state->setUserInput($old_form_state->getUserInput());

      $triggeringElement = $old_form_state->getTriggeringElement();
      // Gets the parents of the triggering element (our entityconnect button)
      // which is at the same level as the reference field. Since we will be
      // traversing the actual form, use #array_parents as opposed to #parents.
      $parents = is_array($triggeringElement) && !empty($triggeringElement['#array_parents']) ? $triggeringElement['#array_parents']: NULL;

      $key_exists = NULL;
      // Now get the reference field container.
      $widget_container = EntityconnectNestedArray::getValue($old_form, $parents, $key_exists);

      if ($key_exists) {
        if (isset($widget_container['widget'])) {
          $widget_container = $widget_container['widget'];
        }
      } else {
        // @ToDo: probably need something to happen in this case.
        return;
      }

      // Now get the actual parents for traversing user input.
      $parents = $widget_container['#parents'];

      $widget_container_type = isset($widget_container['#type']) ? $widget_container['#type'] : 'autocomplete';

      /** @var FieldStorageConfigInterface $field_info */
      $field_info = $cache_data['field_info'];

      if (isset($cache_data['target_id']) && empty($cache_data['cancel'])) {

        // Load the the target entity.
        $entity_type = $cache_data['target_entity_type'];
        $entity_storage = \Drupal::entityTypeManager()->getStorage($entity_type);
        $entity = $entity_storage->load($cache_data['target_id']);

        if ($cache_data['target_id']) {
          $target_id = $entity ? $entity->id() : '';
          // ['#default_value'] should have differents build
          // function of the widget type.
          switch ($widget_container_type) {

            // Select list.
            case 'select':
              if ($widget_container['#multiple'] == FALSE) {
                $element['target_id'] = $target_id;
              } else {
                $element['target_id'] = $widget_container['#value'] + array($target_id => $target_id);
              }
              break;

            // Radios widget.
            case 'radios':
              $element['target_id'] = $target_id;
              break;

            // Checkboxes widget.
            case 'checkboxes':
              $element['target_id'] = $widget_container['#value'] + array($target_id => $target_id);
              break;

            default:
              if ($field_info->getType() == 'entity_reference') {
                $element['target_id'] = $entity ? sprintf('%s (%s)', $entity->label(), $target_id) : '';
                // Autocomplete tags style.
                if (!empty($widget_container['target_id']['#tags']) && !empty ($widget_container['target_id']['#value'])) {
                  $element['target_id'] .= ', ' . $widget_container['target_id']['#value'];
                }
              }
              break;
          }
        }

        // This is the input we already got from the old form state.
        $input = $form_state->getUserInput();

        if (isset($element)) {
          self::_alter_form_state_input($input, $widget_container_type, $parents, $element['target_id']);
        }
        elseif (!empty($data['element_value'])) {
          self::_alter_form_state_input($input, $widget_container_type, $parents, $data['element_value']);
        }

        // Include the alterations from above.
        $form_state->setUserInput($input);

      }

      // Rebuild the form.
      $form_state->setRebuild();

      // The combination of having user input and rebuilding the form means
      // that it will attempt to cache the form state which will fail if it is
      // a GET request.
      $form_state->setRequestMethod('POST');

      // Return processing is complete.
      $form_state->set('#entityconnect_processed', TRUE);

    }
  }

  /**
   * Sets the redirect to a admin/entityconnect/redirect page.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public static function childFormCancel(array $form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();
    $cache_id = $triggeringElement['#parent_build_cache_id'];
    if ($cache_id && ($cache_data = \Drupal::getContainer()->get('entityconnect.cache')->get($cache_id))) {
      $form_state->setRedirect('entityconnect.return', array('cache_id' => $cache_id));
    }
  }

  /**
   * Form API callback: Submit callback for child form.
   *
   * Sets submit button on child create form.
   *
   * On submission of a child form we set:
   * the target_id in the cache entry
   * the redirect to our redirect page.
   *
   * @param array $form
   * @param FormStateInterface $form_state
   */
  public static function childFormSubmit(array $form, FormStateInterface $form_state) {
    $cache_id = $form_state->getValue('parent_build_cache_id');
    if ($cache_id && ($cache_data = \Drupal::getContainer()->get('entityconnect.cache')->get($cache_id))) {

      $entity_type = $cache_data['target_entity_type'];
      $entity = $form_state->getFormObject()->getEntity();
      $cache_data['target_id'] = $entity->id();

      \Drupal::getContainer()->get('entityconnect.cache')->set($cache_id, $cache_data);
      $form_state->setRedirect('entityconnect.return', array('cache_id' => $cache_id));
    }

  }

  /**
   * Used to update the form state value.
   *
   * Form state value is updated for entityreference after adding a new entity.
   *
   * @param array $input
   *   The user input from form state that we need to change.
   * @param string $widget_type
   *   The type of the widget used for reference field.
   * @param array $parents
   *   The array of all parents of the field.
   *   We used them to change the value to the right level in the array.
   * @param array $element
   *   The value we need to insert.
   */
  public static function _alter_form_state_input(array &$input, $widget_type, $parents, $element) {
    switch ($widget_type) {
      case 'autocomplete':
      case 'multiple_selects':
        array_push($parents, 'target_id');
        break;

      case 'textfield':
      case 'radios':
      case 'checkboxes':
      default:
        break;
    }
    EntityconnectNestedArray::setValue($input, $parents, $element);
  }

}
