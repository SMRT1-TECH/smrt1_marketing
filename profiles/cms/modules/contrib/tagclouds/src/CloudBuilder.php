<?php

namespace Drupal\tagclouds;

/**
 * Class CloudBuilder.
 *
 * @package Drupal\tagclouds
 */
class CloudBuilder implements CloudBuilderInterface {

  /**
   * {@inheritdoc}
   */
  public function build(array $terms) {
    $output = [];
    $config = \Drupal::config('tagclouds.settings');
    $display = $config->get('display_type');
    if (empty($display)) {
      $display = 'style';
    }

    if ($display == 'style') {
      foreach ($terms as $term) {
        $term_name = $term->name;
        $term_desc = $term->description__value;

        if ($term->count == 1 && $config->get("display_node_link")) {
          $output[$term->tid] = $this->displayNodeLinkWeight($term_name, $term->tid, $term->nid, $term->weight, $term_desc);
        }
        else {
          $output[$term->tid] = $this->displayTermLinkWeight($term_name, $term->tid, $term->weight, $term_desc);
        }
      }
    }
    else {
      foreach ($terms as $term) {
        $term_name = $term->name;
        $term_desc = $term->description__value;
        if ($term->count == 1 && $config->get("display_node_link")) {
          $output[$term->tid] = $this->displayNodeLinkCount($term_name, $term->tid, $term->nid, $term->count, $term_desc);
        }
        else {
          $output[$term->tid] = $this->displayTermLinkCount($term_name, $term->tid, $term->count, $term_desc);
        }
      }
    }
    return $output;
  }

  /**
 * Display Single Tag with Style
 */
private function displayTermLinkWeight($name, $tid, $weight, $description) {
  if ($term = entity_load('taxonomy_term', $tid)) {
    $uri = $term->urlInfo();
    $options = $uri->getOptions();
    $options['attributes']['class'][] = 'tagclouds';
    $options['attributes']['class'][] = 'level' . $weight;
    $uri->setOptions($options);

    $build = [
      '#type' => 'link',
      '#prefix' => '<span class="tagclouds-term">',
      '#title' => $name,
      '#url' => $uri,
      '#suffix' => '</span>',
    ];

    return $build;
  }
}

private function displayNodeLinkWeight($name, $tid, $nid, $weight, $description) {
  if ($term = entity_load('taxonomy_term', $tid) && $node = node_load($nid)) {
    $uri = $node->urlInfo();
    $options = $uri->getOptions();
    $options['attributes']['class'][] = 'tagclouds';
    $options['attributes']['class'][] = 'level' . $weight;
    $uri->setOptions($options);

    $build = [
      '#type' => 'link',
      '#prefix' => '<span class="tagclouds-term">',
      '#title' => $name,
      '#url' => $uri,
      '#suffix' => '</span>',
    ];

    return $build;
  }
}

/**
 * Display Single Tag with Style
 */
private function displayNodeLinkCount($name, $tid, $nid, $count, $description) {
  if ($term = entity_load('taxonomy_term', $tid) && $node = node_load($nid)) {
    $uri = $node->urlInfo();
    $options = $uri->getOptions();
    $options['attributes']['class'][] = 'tagclouds';

    $build = [
      '#type' => 'link',
      '#prefix' => '<span class="tagclouds-term">',
      '#title' => $name,
      '#url' => $uri,
      '#suffix' =>  " ($count)</span>",
    ];

    return $build;
  }
}

private function displayTermLinkCount($name, $tid, $count, $description) {
  if ($term = entity_load('taxonomy_term', $tid)) {
    $uri = $term->urlInfo();
    $options = $uri->getOptions();
    $options['options']['attributes']['class'][] = 'tagclouds';

    $build = [
      '#type' => 'link',
      '#prefix' => '<span class="tagclouds-term">',
      '#title' => $name,
      '#url' => $uri,
      '#suffix' =>  "($count) </span>",
    ];

    return $build;
  }
}

}
