<?php

/**
 * @file
 * Contains \Drupal\Tests\joinup\Kernel\UniqueTitleInBundleConstraintValidatorTest.
 */

namespace Drupal\Tests\joinup\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Tests validation constraints for UniqueTitleInBundleConstraintValidatorTest.
 *
 * @group joinup
 */
class UniqueTitleInBundleConstraintValidatorTest extends KernelTestBase {

  /**
   * @var string
   */
  private $entityType;

  public static $modules = array('rdf_entity', 'collection', 'solution', 'text', 'user');

  protected function setUp() {
    parent::setUp();
    // Create a field and storage of type 'test_field', on the 'entity_test'
    // entity type.
    $this->entityType = 'rdf_entity';
    $this->installEntitySchema('user');
    $this->installEntitySchema('rdf_entity');
  }

  /**
   * Tests bundle constraint validation.
   */
  public function testValidation() {
    // Test that the same title cannot apply twice in an entity.
    $this->assertValidation('collection');
    // Test that the same title can apply in a different bundle, but again
    // not twice.
    $this->assertValidation('solution');
  }

  /**
   * Executes the BundleConstraintValidator test for a given bundle.
   *
   * @param string|array $bundle
   *   Bundle/bundles to use as constraint option.
   */
  protected function assertValidation($bundle) {
    $title = 'Some random text';

    $enity_1 = entity_create('rdf_entity', [
      'rid' => $bundle,
      'label' => $title,
    ]);
  }
}
