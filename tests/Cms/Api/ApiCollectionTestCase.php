<?php

namespace Kirby\Cms\Api;

use Kirby\Cms\Api;
use Kirby\Cms\App;
use Kirby\Cms\TestCase;

class ApiCollectionTestCase extends TestCase
{
	protected Api $api;

	public function setUp(): void
	{
		$this->app = new App([
			'roots' => [
				'index' => $this->hasTmp() ? static::TMP : '/dev/null',
			],
		]);

		$this->api = $this->app->api();
	}

	public function tearDown(): void
	{
		App::destroy();
		$this->tearDownTmp();
	}
}
