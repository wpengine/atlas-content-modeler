<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration\Helpers;

use function WPE\AtlasContentModeler\ContentConnect\Helpers\get_registry;
use WPE\AtlasContentModeler\ContentConnect\Tests\Integration\ContentConnectTestCase;

class GetRegistryTest extends ContentConnectTestCase {

	public function test_get_registry_returns_registry() {
		$registry = get_registry();

		$this->assertInstanceOf( '\WPE\AtlasContentModeler\ContentConnect\Registry', $registry );
	}



}
