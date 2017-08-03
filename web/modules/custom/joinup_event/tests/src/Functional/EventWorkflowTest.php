<?php

namespace Drupal\Tests\joinup_event\Functional;

use Drupal\Tests\joinup_core\Functional\NodeWorkflowTestBase;

/**
 * Tests CRUD operations and workflow transitions for the event node.
 *
 * @group workflow
 */
class EventWorkflowTest extends NodeWorkflowTestBase {

  /**
   * {@inheritdoc}
   */
  protected function getEntityBundle() {
    return 'event';
  }

}
