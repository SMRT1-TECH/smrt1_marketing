<?php

namespace Drupal\glazed_builder\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'glazed_builder_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'glazed_builder.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('glazed_builder.settings');
    $form['bootstrap'] = [
      '#type' => 'radios',
      '#title' => $this->t('Include Bootstrap Files'),
      '#description' => $this->t('Bootstrap 3 is required. Bootstrap 3 Light is recommended if your theme has conflicts with Bootstrap 3 CSS. Bootstrap Light includes all grid and helper classes but doesn\'t contain normalize.css and some typography styles.'),
      '#options' => [
        0 => $this->t('No'),
        2 => $this->t('Load Bootstrap 3 Light'),
        1 => $this->t('Load Bootstrap 3 Full'),
      ],
      '#default_value' => $config->get('bootstrap'),
    ];
    $default = ['' => $this->t('None (Use basic file upload widget)')];
    if (\Drupal::moduleHandler()->moduleExists('entity_browser')) {
      $media_browsers = \Drupal::entityQuery('entity_browser')->execute();
      $media_browsers = $default + $media_browsers;
    }
    else {
      $media_browsers = $default;
    }
    $form['media_browser'] = [
      '#type' => 'radios',
      '#title' => $this->t('Media Browser'),
      '#description' => $this->t('Glazed Builder supports media image reusability via the Entity Browser module. The Entity Browser selected here will be used by the editor. The Entity Browser has to be using the iFrame display plugin.'),
      '#options' => $media_browsers,
      '#default_value' => $config->get('media_browser'),
    ];

    $form['development'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Development mode'),
      '#description' => $this->t('In Development mode Glazed Builder will use non-minified files to make debugging easier.'),
      '#default_value' => $config->get('development'),
    );

    $form['experimental'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Experimental Features'),
      '#description' => $this->t('Use with caution.'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['experimental']['format_filters'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Process Text Format Filters on Frontend Builder content'),
      '#description' => $this->t('If a field uses Glazed Builder as field formatter any filters that are set on the field\'s text format will be ignored. This is because when editing on the frontend, you are editing the raw field contents. With this setting enabled the Glazed editor still loads raw fields content, but users that don\'t have Glazed Builder editing permission will get a filtered field. Some filters will not work at all with Glazed Builder while others should work just fine.'),
      '#default_value' => $config->get('format_filters'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('glazed_builder.settings')
      ->set('bootstrap', $form_state->getValue('bootstrap'))
      ->set('development', $form_state->getValue('development'))
      ->set('format_filters', $form_state->getValue('format_filters'))
      ->set('media_browser', $form_state->getValue('media_browser'))
      ->save();

    parent::submitForm($form, $form_state);
  }
}
