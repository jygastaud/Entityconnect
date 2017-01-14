<?php

namespace Drupal\entityconnect\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Class EntityconnectController.
 *
 * @package Drupal\entityconnect\Controller
 */
class EntityconnectController extends ControllerBase {
  /**
   * Return.
   *
   * @return string
   *   Return Hello string.
   */
  public function return_to($param_1) {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Implement method: return with parameter(s): @param_1', array('@param_1' => $param_1))
    ];
  }
  /**
   * Edit.
   *
   * @return string
   *   Return Hello string.
   */
  public function edit($param_1) {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Implement method: return with parameter(s): @param_1', array('@param_1' => $param_1))
    ];
  }
  /**
   * Add.
   *
   * @return string
   *   Return Hello string.
   */
  public function add($param_1) {
    return [
        '#type' => 'markup',
        '#markup' => $this->t('Implement method: return with parameter(s): @param_1', array('@param_1' => $param_1))
    ];
  }

}
