<?php
/**
 * @file
 * Contains \Drupal\entityconnect\Element\EntityconnectSubmit.
 *
 * @author Agnes Chisholm <amaria@66428.no-reply.drupal.org>
 */

namespace Drupal\entityconnect\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Submit;

/**
 * Provides a custom form submit button for entityconnect.
 *
 * Submit buttons are processed the same as regular buttons, except they trigger
 * the form's submit handler.
 *
 * Properties:
 * - #submit: Specifies an alternate callback for form submission when the
 *   submit button is pressed.  Use '::methodName' format or an array containing
 *   the object and method name (for example, [ $this, 'methodName'] ).
 * - #value: The text to be shown on the button.
 * - #key: 'all' |  The delta of the item within a multi-item field.
 * - #field: The field name.
 * - #entity_type_target: The target entity type.
 * - #acceptable_types: List of acceptable target bundles.
 * - #add_child: Boolean - Whether or not an entity is being added.
 * - #language: The language of the entity.
 *
 * Usage Example:
 * @code
 * $form['actions']['submit'] = array(
 *   '#type' => 'entityconnect_submit,
 *   '#value' => $this->t('Save'),
 * );
 * @endcode
 *
 * @see \Drupal\Core\Render\Element\Submit
 *
 * @FormElement("entityconnect_submit")
 */
class EntityconnectSubmit extends Submit {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return array(
      '#key' => '',
      '#field' => '',
      '#entity_type_target' => 'node',
      '#acceptable_types' => array(),
      '#add_child' => FALSE,
      '#language' => \Drupal\Core\Language\LanguageInterface::LANGCODE_DEFAULT,
      '#validate' => array(
        array($class, 'validateSubmit'),
      ),
      '#submit' => array(
          array($class, 'addEditButtonSubmit'),
      ),
      '#weight' => 1,
      '#limit_validation_errors' => array(),
    ) + parent::getInfo();
  }

  /**
   * {@inheritdoc}
   */
  public static function preRenderButton($element) {
    $element = parent::preRenderButton($element);

    // Attach entityconnect assets.
    $element['#attached']['library'][] = 'entityconnect/entityconnect';

    // Support Clientside Validation.
    $element['#attributes']['class'][] = 'cancel';
    if (empty($element['#attributes']['title'])) {
      $element['#attributes']['title'] = $element['#add_child'] ? t('Add') : t('Edit');
    }

    return $element;
  }

  /**
   * Form #validate callback for the entityconnect_submit element.
   *
   * Used to bypass validation of the parent form.
   *
   * @param array $form
   *   The parent form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public static function validateSubmit($form, FormStateInterface $form_state) {
    // Ignore all validation.
    // @todo: Probably should validate the fields that were entered.
  }

  /**
   * Button #submit callback: Call when an entity is to be added or edited.
   *
   * We cache the current state and form
   * and redirect to the add or edit page with an append build_cached_id.
   *
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public static function addEditButtonSubmit($form, FormStateInterface $form_state) {
    // @FIXME Implement the submit handler.
  }

}