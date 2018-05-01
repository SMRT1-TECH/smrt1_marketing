<?php

namespace Drupal\tagclouds\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tagclouds\Controller\CsvToArrayTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller routines for user routes.
 */
class TagcloudsListVocs extends ControllerBase {

  use CsvToArrayTrait;

  /**
   * Renders a list of vocabularies.
   *
   * Vocabularys are wrapped in a series of boxes, labeled by name
   * description.
   *
   * @param string $tagclouds_vocs_str
   *   A comma separated list of vocabulary ids.
   *
   * @return array
   *   A render array.
   *
   * @throws NotFoundHttpException
   *   Thrown when any vocabulary in the list cannot be found.
   */
  public function listVocs($tagclouds_vocs_str = NULL) {
    $vocs = $this->csvToArray($tagclouds_vocs_str);
    if (empty($vocs)) {
      throw new NotFoundHttpException();
    }

    /* @var Drupal\tagclouds\TagServiceInterface $tagService */
    $tag_service = \Drupal::service('tagclouds.tag');

    /* @var Drupal\tagclouds\CloudBuilder $cloud_builder */
    $cloud_builder = \Drupal::service('tagclouds.cloud_builder');
    $boxes = [];
    foreach ($vocs as $vid) {
      $vocabulary = entity_load('taxonomy_vocabulary', $vid);

      if ($vocabulary == FALSE) {
        throw new NotFoundHttpException();
      }

      $config = $this->config('tagclouds.settings');
      $tags = $tag_service->getTags([$vid], $config->get('levels'), $config->get('page_amount'));
      $sorted_tags = $tag_service->sortTags($tags);

      $cloud = $cloud_builder->build($sorted_tags);

      if (!$cloud) {
        throw new NotFoundHttpException();
      }

      $boxes[] = [
        '#theme' => 'tagclouds_list_box',
        '#vocabulary' => $vocabulary,
        '#children' => $cloud,
      ];

    }

    // Wrap boxes in a div.
    $output = [
      '#attached' => ['library' => 'tagclouds/clouds'],
      '#type' => 'container',
      '#children' => $boxes,
      '#attributes' => ['class' => 'wrapper tagclouds'],
    ];

    return $output;
  }

}
