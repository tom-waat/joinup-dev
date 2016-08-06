<?php

namespace Drupal\form_auto_fill\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SettingsBlock' block.
 *
 * @Block(
 *  id = "form_auto_fill_settings_block",
 *  admin_label = @Translation("Auto fill settings"),
 * )
 */
class SettingsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('\Drupal\form_auto_fill\Form\SettingsForm');
  }

}
