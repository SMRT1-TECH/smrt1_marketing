<?php

namespace Drupal\tagclouds\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\tagclouds\Controller\CsvToArrayTrait;

/**
 * Controller routines for user routes.
 */
class TagcloudsPageChunk extends ControllerBase {

  use CsvToArrayTrait;

  /**
   * Renders a list of vocabularies.
   *
   * @param string $tagclouds_vocs_str
   *   A comma separated list of vocabulary ids.
   *
   * @return array
   *   A render array.
   */
  public function chunk($tagclouds_vocs_str = '') {
    /** @var Drupal\tagclouds\TagServiceInterface $tag_service */
    $tag_service = \Drupal::service('tagclouds.tag');

    /* @var Drupal\tagclouds\CloudBuilder $cloud_builder */
    $cloud_builder = \Drupal::service('tagclouds.cloud_builder');

    $vocs = $this->csvToArray($tagclouds_vocs_str);
    if (empty($vocs)) {
      $query = \Drupal::entityQuery('taxonomy_vocabulary');
      $all_ids = $query->execute();
      foreach (Vocabulary::loadMultiple($all_ids) as $vocabulary) {
        $vocs[] = $vocabulary->id();
      }
    }
    $config = $this->config('tagclouds.settings');
    $tags = $tag_service->getTags($vocs, $config->get('levels'), $config->get('page_amount'));

    $sorted_tags = $tag_service->sortTags($tags);

    $output = [
      '#attached' => ['library' => 'tagclouds/clouds'],
      '#theme' => 'tagclouds_weighted',
      '#children' => $cloud_builder->build($sorted_tags),
    ];

    return $output;
  }

}
