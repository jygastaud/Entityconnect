<?php
/**
 * @file
 *   Define utilitary functions for Entity Connect.
 */

namespace Drupal\entityconnect;


class Entityconnect {

  public function userAccessCheck($permission) {
    return user_access($permission);
  }

  /**
   * Helper function to retieve all allowed entityreference fields.
   */
  public static function getRefFields() {
    $ref_fields = array();

    foreach (field_info_fields() as $id => $field) {
      // Add support for Entity reference module.
      if ($field['type'] == 'entityreference' && $field['module'] == 'entityreference') {
        $entity_reference_info = entityreference_get_selection_handler($field);
        $entity_type = $entity_reference_info->field['settings']['target_type'];
        $target_bundle = isset($entity_reference_info->field['settings']['handler_settings']['target_bundles']) ? $entity_reference_info->field['settings']['handler_settings']['target_bundles'] : NULL;

        if (self::userAccessCheck('entityconnect add button') || self::userAccessCheck('entityconnect edit button')) {
          switch ($entity_type) {
            case 'user':
              if (self::userAccessCheck('administer users')) {
                $ref_fields[$id] = $field;
              }
              break;

            case 'node':
              if (isset($target_bundle) && count($target_bundle) == 1) {
                if ((self::userAccessCheck('create ' . array_pop($target_bundle) . ' content') || self::userAccessCheck('administer nodes'))) {
                  $ref_fields[$id] = $field;
                }
              }
              else {
                $ref_fields[$id] = $field;
              }

              break;

            case 'taxonomy_term':
              $ref_fields[$id] = $field;
              break;

            case 'taxonomy_vocabulary':
              $ref_fields[$id] = $field;
              break;

            default:
              break;
          }
        }
      }
    }

    drupal_alter('entityconnect_ref_fields', $ref_fields);

    return $ref_fields;
  }

  /**
   * Helpers to know modules that are allowed to support add and edit buttons.
   *
   * @return array
   */
  public static function getReferencesFieldTypeList() {
    $modules_list = array(
      'entityreference',
    );
    drupal_alter('entityconnect_field_type_list', $modules_list);

    return $modules_list;
  }
}