<?php

namespace Drupal\id_awesome_module\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SettingsBlock' block.
 *
 * @Block(
 *  id = "id_awesome_module_settings_block",
 *  admin_label = @Translation("Auto fill settings"),
 * )
 */
class SettingsBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    return \Drupal::formBuilder()->getForm('\Drupal\id_awesome_module\Form\SettingsForm');
  }

}
