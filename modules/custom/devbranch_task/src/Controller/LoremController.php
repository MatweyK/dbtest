<?php

namespace Drupal\devbranch_task\Controller;

/**
 * Controller for devbranch task.
 */

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Class for controller.
 */
class LoremController extends ControllerBase {

  /**
   * Function that build our devbranch_task.
   *
   * @return array
   *   Returns menu array to be rendered.
   */
  public function getMenuLinks() {
    $build['#source_links'] = [];
    $build['links']['form'] = [
      '#title' => $this
        ->t('Generator'),
      '#type' => 'link',
      '#url' => Url::fromRoute('devbranch_task.form'),
    ];
    $build['links']['settings'] = [
      '#title' => $this
        ->t('Pattern settings'),
      '#type' => 'link',
      '#url' => Url::fromRoute('devbranch_task.config'),
    ];
    $build['#source_links'][] = $build['links']['form'];
    $build['#source_links'][] = $build['links']['settings'];
    $build['#theme'] = 'devbranch_task_menu';

    return $build;
  }

}
