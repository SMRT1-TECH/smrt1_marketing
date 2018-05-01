<?php
/**
 * @file
 * Contains \Drupal\tagclouds\Plugin\Block\TagcloudsTermsBlock.
 */

namespace Drupal\tagclouds\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Provides a template for blocks based of each vocabulary.
 *
 * @Block(
 *   id = "tagclouds_block",
 *   admin_label = @Translation("Tagclouds terms"),
 *   category = @Translation("Tagclouds"),
 *   deriver = "Drupal\tagclouds\Plugin\Derivative\TagcloudsTermsBlock"
 * )
 *
 * @see \Drupal\tagclouds\Plugin\Derivative\TagcloudsTermsBlock
 */
class TagcloudsTermsBlock extends BlockBase {
  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return array(
      'cache' => array(
        'max_age' => 0,
        'contexts' => array(),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $terms_limit = isset($this->configuration['tags']) ? $this->configuration['tags'] : 0;
    $form['tags'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tags to show'),
      '#default_value' => $terms_limit,
      '#maxlength' => 3,
      '#description' => $this->t("The number of tags to show in this block. Enter '0' to display all tags."),
    );
    $form['vocabulary'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Vocabulary machine name'),
      '#default_value' => 'tags',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['tags'] = $form_state->getValue('tags');
    $this->configuration['vocabulary'] = $form_state->getValue('vocabulary');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    /** @var \Drupal\tagclouds\TagServiceInterface $tagService */
    $tag_service = \Drupal::service('tagclouds.tag');

    /** @var \Drupal\tagclouds\CloudBuilderInterface $cloud_builder */
    $cloud_builder = \Drupal::service('tagclouds.cloud_builder');

    $tags_limit = $this->configuration['tags'];
    $vocab_name = $this->configuration['vocabulary'];

    $content = [
      '#attached' => ['library' => 'tagclouds/clouds'],
    ];
    if ($voc = Vocabulary::load($vocab_name)) {
      $config = \Drupal::config('tagclouds.settings');
      $tags = $tag_service->getTags([$vocab_name], $config->get('levels'), $tags_limit);

      $tags = $tag_service->sortTags($tags);

      $content[] = [
        'tags' => $cloud_builder->build($tags),
      ];
      if (count($tags) >= $tags_limit && $tags_limit > 0) {
        $content[] = [
          '#type' => 'more_link',
          '#title' => $this->t('more tags'),
          '#url' => Url::fromRoute('tagclouds.chunk_vocs', ['tagclouds_vocs' => $voc->id()]
          ),
        ];
      }
    }

    return $content;
  }

}
