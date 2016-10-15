<?php

namespace Drupal\Tests\field_default_token\Kernel;

use Drupal\entity_test\Entity\EntityTest;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests that tokens in field default values get replaced correctly.
 */
class FieldDefaultTokenTest extends KernelTestBase  {

  /**
   * The site name of the test site.
   *
   * @var string
   */
  protected $siteName;

  /**
   * The ID of the entity type used in the test.
   *
   * @var string
   */
  protected $entityTypeId = 'entity_test';

  /**
   * The name of the field used in the test.
   *
   * @var string
   */
  protected $fieldName = 'field_default_token_test';

  /**
   * {@inheritdoc}
   */
  public static $modules = ['entity_test', 'field', 'field_default_token', 'system', 'user'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema($this->entityTypeId);

    /** @var \Drupal\Core\Config\ConfigFactoryInterface $config_factory */
    $config_factory = $this->container->get('config.factory');
    $this->siteName = $config_factory->get('system.site')->get('name');
  }

  /**
   * {@inheritdoc}
   */
  public function testTokenReplacement() {
    FieldStorageConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityTypeId,
      'type' => 'string',
    ])->save();
    $field = FieldConfig::create([
      'field_name' => $this->fieldName,
      'entity_type' => $this->entityTypeId,
      'bundle' => 'entity_test',
    ]);
    $field->setDefaultValue('This is the site name: [site:name]');
    $field->save();
    $this->assertEquals('field_default_token_default_value_callback', $field->getDefaultValueCallback());

    $entity = EntityTest::create();
    $entity->save();

    $expected = [['value' => 'This is the site name: ' . $this->siteName]];
    $this->assertEquals($expected, $field->getDefaultValue($entity));
  }

}
