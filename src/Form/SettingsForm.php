<?php

namespace Drupal\bootstrap_styles\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\bootstrap_styles\StylesGroup\StylesGroupManager;
use Drupal\bootstrap_styles\Style\StyleManager;

/**
 * Configure Bootstrap Styles settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The styles group plugin manager.
   *
   * @var \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager
   */
  protected $stylesGroupManager;

  /**
   * The styles plugin manager.
   *
   * @var \Drupal\bootstrap_styles\Style\StyleManager
   */
  protected $styleManager;

  /**
   * Constructs a SettingsForm object.
   *
   * @param \Drupal\bootstrap_styles\StylesGroup\StylesGroupManager $styles_group_manager
   *   The styles group plugin manager.
   * @param \Drupal\bootstrap_styles\Style\StyleManager $style_manager
   *   The styles plugin manager.
   */
  public function __construct(StylesGroupManager $styles_group_manager, StyleManager $style_manager) {
    $this->stylesGroupManager = $styles_group_manager;
    $this->styleManager = $style_manager;

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.bootstrap_styles_group'),
      $container->get('plugin.manager.bootstrap_styles')
    );
  }

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'bootstrap_styles.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'bootstrap_styles_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $plugins_elements = [];
    foreach ($this->stylesGroupManager->getStylesGroups() as $group_plugin_id => $style_group) {
      if (isset($style_group['styles'])) {
        foreach ($style_group['styles'] as $style_plugin_id => $style) {
          $style_instance = $this->styleManager->createInstance($style_plugin_id);
          $plugins_elements = array_merge_recursive($plugins_elements, $style_instance->buildConfigurationForm($form, $form_state));

        }
      }
    }
    $form += $plugins_elements;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // $this->configFactory->getEditable(static::SETTINGS)
    //   ->set('background_colors', $form_state->getValue('background_colors'))
    //   ->save();
    parent::submitForm($form, $form_state);
    foreach ($this->stylesGroupManager->getStylesGroups() as $group_plugin_id => $style_group) {
      if (isset($style_group['styles'])) {
        foreach ($style_group['styles'] as $style_plugin_id => $style) {
          $style_instance = $this->styleManager->createInstance($style_plugin_id);
          $style_instance->submitConfigurationForm($form, $form_state);
        }
      }
    }
  }

}
