<?php
/**
 * Created by PhpStorm.
 * User: jygastaud
 * Date: 27/08/15
 * Time: 11:44
 */

namespace Drupal\entityconnect\Access;

use Drupal\Core\Access\AccessCheckInterface;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

class CustomAccessCheck implements AccessCheckInterface {

  /**
   * {{ @inheritdoc }}
   */
  public function applies(Route $route) {
    return $route->hasRequirement('_entityconnect_access_check');
  }

  /**
   * A custom access check.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   *   Run access checks for this account.
   */
  public function access(AccountInterface $account) {
    // Check permissions and combine that with any custom access checking needed. Pass forward
    // parameters from the route and/or request as needed.
    return AccessResultAllowed::allowedIfHasPermissions(
      $account,
      array('entityconnect add button', 'entityconnect edit button'),
      'OR');
  }
}
