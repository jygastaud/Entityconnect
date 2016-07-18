<?php

namespace Drupal\entityconnect\Element;

/**
 * Class EntityconnectSubmit
 * @package Drupal\entityconnect\Element
 *
 *  Generate Add and Edit buttons.
 */
class EntityconnectSubmit {

    public $widgetContainer;
    public $instance;
    public $entityReferenceInfo;

    public function __construct($instance, $widget_container, $entity_reference_info) {
        $this->instance = $instance;
        $this->widgetContainer = $widget_container;
        $this->entityReferenceInfo = $entity_reference_info;
    }

    public function getParents() {
        $parents = null;
        if (isset($this->widgetContainer['#field_parents'])) {
            foreach ($this->widgetContainer['#field_parents'] as $key1 => $parent) {
                if (!is_null($parents)) {
                    $parents = $parent;
                }
                else {
                    $parents .= "-" . $parent;
                }
            }
        }

        return $parents;
    }

    public function getEntityType() {
        return $this->entityReferenceInfo->field['settings']['target_type'];
    }

    public function getAcceptableBundles() {
        // Default value for acceptable type.
        $acceptable_types = NULL;

        $entity_type = $this->getEntityType();

        if (isset($this->entityReferenceInfo->field['settings']['handler_settings']['target_bundles'])) {
            $acceptable_types = $this->entityReferenceInfo->field['settings']['handler_settings']['target_bundles'];
        }
        elseif (isset($this->entityReferenceInfo->field['settings']['handler_settings']['view'])) {
            $name = $this->entityReferenceInfo->field['settings']['handler_settings']['view']['view_name'];
            $display = $this->entityReferenceInfo->field['settings']['handler_settings']['view']['display_name'];
            $views = views_get_view($name);
            $views_display = isset($views->display) ? $views->display : NULL;

            switch ($entity_type) {
                case 'taxonomy_term':
                    if (isset($views_display[$display]->display_options['filters']['machine_name']['table'])
                      && $views_display[$display]->display_options['filters']['machine_name']['table'] == 'taxonomy_vocabulary' ) {
                        $acceptable_types = $views_display[$display]->display_options['filters']['machine_name']['value'];
                    }
                    elseif (isset($views_display['default']->display_options['filters']['machine_name']['value'])) {
                        $acceptable_types = $views_display['default']->display_options['filters']['machine_name']['value'];
                    }
                    break;

                default:
                    if (isset($views_display[$display]->display_options['filters']['type'])) {
                        $acceptable_types = $views_display[$display]->display_options['filters']['type']['value'];
                    }
                    elseif (isset($views_display['default']->display_options['filters']['type']['value'])) {
                        $acceptable_types = $views_display['default']->display_options['filters']['type']['value'];
                    }
                    break;
            }
        }

        return $acceptable_types;
    }

    /**
     * @return bool
     *      true | false
     */
    public function isAddButtonAllowed() {
        return isset($this->instance['entityconnect']['buttons']['button_add'])
          ? $this->instance['entityconnect']['buttons']['button_add']
          : variable_get('entityconnect_unload_add_default', 1);
    }

    /**
     * @return bool
     *      true | false
     */
    public function isEditButtonAllowed() {
        return isset($this->instance['entityconnect']['buttons']['button_edit']) && user_access('entityconnect edit button')
          ? $this->instance['entityconnect']['buttons']['button_edit']
          : variable_get('entityconnect_unload_edit_default', 1);

    }

    /**
     * @return string
     */
    public function isOfWidgetType() {
        return isset($this->widgetContainer['#type']) ? $this->widgetContainer['#type'] : 'autocomplete';
    }

    /**
     * @return bool
     *      true | false
     */
    public function isViewWidget() {
        return isset($this->widgetContainer['view']) ? true : false;
    }

    /**
     * @return bool
     *      true | false
     */
    public function isMultiple() {
        return (!isset($this->widgetContainer['#cardinality']) || $this->widgetContainer['#cardinality'] == 1
          || (isset($this->widgetContainer['#multiple']) && $this->widgetContainer['#multiple'] == true))
          ? false
          : true;
    }

    public function addButtonType() {
        return isset($this->instance['entityconnect']['icons']['icon_add'])
          ? $this->instance['entityconnect']['icons']['icon_add']
          : variable_get('entityconnect_show_add_icon_default', 0);
    }

    public function editButtonType() {
        return isset($this->instance['entityconnect']['icons']['icon_edit'])
          ? $this->instance['entityconnect']['icons']['icon_edit']
          : variable_get('entityconnect_show_edit_icon_default', 0);
    }

    /**
     * @return string
     */
    public function setClasses() {
        $classes = implode(' ', array(
          $this->isOfWidgetType(),
          $this->cardinalityClass(),
          $this->multipleClass(),
          $this->addIconClass(),
          $this->editIconClass(),
          $this->addTextClass(),
          $this->editTextClass(),
        ));

        return $classes;
    }

    /**
     * @return string
     */
    public function cardinalityClass() {
        return (!isset($this->widgetContainer['#cardinality']) || $this->widgetContainer['#cardinality'] == 1) ? 'single-value' : 'multiple-values';
    }

    /**
     * @return string
     */
    public function multipleClass() {
        return (isset($this->widgetContainer['#multiple']) && $this->widgetContainer['#multiple'] == TRUE) ? 'multiple-selection' : 'single-selection';
    }

    /**
     * @return null|string
     */
    private function addIconClass() {
        return $this->addButtonType() < 2 ? 'add-icon' : null;
    }

    /**
     * @return null|string
     */
    private function editIconClass() {
        return $this->editButtonType() < 2 ? 'edit-icon' : null;
    }

    /**
     * @return null|string
     */
    private function addTextClass() {
        return $this->addButtonType() == 1 ? 'add-text' : null;
    }

    /**
     * @return null|string
     */
    private function editTextClass() {
        return $this->editButtonType() == 1 ? 'edit-text' : null;
    }

    /**
     * Here we attach a "Add" submit button.
     */
    public function setAddButtonInfo(&$widget_element, $language, $field_name, $key, $entity_type, $acceptable_types = NULL) {
        $parents = $this->getParents();
        $widget_element["add_entityconnect__{$field_name}_{$key}_{$parents}"] = array(
          '#type' => 'submit',
          '#limit_validation_errors' => array(),
          '#value' => t('New content'),
          '#name' => "add_entityconnect__{$field_name}_{$key}_{$parents}",
          '#prefix' => "<div class = 'entityconnect-add {$this->setClasses()}'>",
          '#suffix' => '</div>',
          '#key' => $key,
          '#field' => $field_name,
          '#entity_type_target' => $entity_type,
          '#acceptable_types' => $acceptable_types,
          '#add_child' => TRUE,
          '#language' => $language,
          '#submit' => array('entityconnect_include_form', 'entityconnect_add_edit_button_submit'),
          '#weight' => -2,
          '#attached' => array(
            'js' => array(
              drupal_get_path('module', 'entityconnect') . "/theme/js/entityconnect.js",
            ),
            'css' => array(
              drupal_get_path('module', 'entityconnect') . "/theme/css/entityconnect.css",
            ),
          ),
          '#attributes' => array(
            'title' => t('Add'),
              // Support Clientside Validation.
            'class' => array('cancel')
          ),
        );
    }

    /**
     * Here we attach a "Edit" submit button.
     */
    public function setEditButtonInfo(&$widget_element, $language, $field_name, $key, $entity_type) {
        $parents = $this->getParents();
        $widget_element["edit_entityconnect__{$field_name}_{$key}_{$parents}"] = array(
          '#type' => 'submit',
          '#limit_validation_errors' => array(array($field_name)),
          '#value' => t('Edit content'),
          '#name' => "edit_entityconnect__{$field_name}_{$key}_{$parents}",
          '#prefix' => "<div class = 'entityconnect-edit {$this->setClasses()}'>",
          '#suffix' => '</div>',
          '#key' => $key,
          '#field' => $field_name,
          '#entity_type_target' => $entity_type,
          '#add_child' => FALSE,
          '#language' => $language,
          '#submit' => array('entityconnect_include_form', 'entityconnect_add_edit_button_submit'),
          '#weight' => -2,
          '#attached' => array(
            'js' => array(
              drupal_get_path('module', 'entityconnect') . "/theme/js/entityconnect.js",
            ),
            'css' => array(
              drupal_get_path('module', 'entityconnect') . "/theme/css/entityconnect.css",
            ),
          ),
          '#attributes' => array(
            'title' => t('Edit'),
              // Support Clientside Validation.
            'class' => array('cancel')
          ),
        );
    }
}
