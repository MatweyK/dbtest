<?php

namespace Drupal\devbranch_task\Controller;

/**
 * Controller for devbranch task.
 */

use Drupal\Core\Controller\ControllerBase;

/**
 * Class for controller.
 */
class LoremController extends ControllerBase {

  /**
   * Function that build our devbranch_task.
   *
   * @return array
   */
  public function get_menu_links() {
    $menu_tree = \Drupal::menuTree();
    $menu_name = 'main';

    // Build the typical default set of menu tree parameters.
    $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);

    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);

    // Finally, build a renderable array from the transformed tree.
    $menu = $menu_tree->build($tree);
    return $menu;
  }

}
