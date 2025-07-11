<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MemoryStorage::class)]
class MemoryStorageTest extends TestCase
{
	protected MemoryStorage $storage;

	public function assertCreateAndDelete(VersionId $versionId, Language $language): void
	{
		$this->storage->create($versionId, $language, []);

		$this->assertTrue($this->storage->exists($versionId, $language));

		$this->storage->delete($versionId, $language);

		$this->assertFalse($this->storage->exists($versionId, $language));
	}

	public function assertCreateAndRead(VersionId $versionId, Language $language): void
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create($versionId, $language, $fields);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($fields, $this->storage->read($versionId, $language));
	}

	public function assertCreateAndUpdate(VersionId $versionId, Language $language): void
	{
		$fields = [
			'title' => 'Foo',
			'text'  => 'Bar'
		];

		$this->storage->create($versionId, $language, []);

		$this->assertSame([], $this->storage->read($versionId, $language));

		$this->storage->update($versionId, $language, $fields);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($fields, $this->storage->read($versionId, $language));
	}

	public function setUpMultiLanguage(
		array|null $site = null
	): void {
		parent::setUpMultiLanguage(site: $site);

		$this->storage = new MemoryStorage($this->model);
	}

	public function setUpSingleLanguage(
		array|null $site = null
	): void {
		parent::setUpSingleLanguage(site: $site);

		$this->storage = new MemoryStorage($this->model);
	}

	public function testCreateAndReadChangesMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testCreateAndReadChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testCreateAndReadLatestMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::latest();
		$language  = $this->app->language('en');

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testCreateAndReadLatestSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertCreateAndRead($versionId, $language);
	}

	public function testDeleteNonExisting()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertFalse($this->storage->exists($versionId, $language));

		// test idempotency
		$this->storage->delete($versionId, $language);

		$this->assertFalse($this->storage->exists($versionId, $language));
	}

	public function testDeleteChangesMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testDeleteChangesSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testDeleteLatestMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::latest();
		$language  = $this->app->language('en');

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testDeleteLatestSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertCreateAndDelete($versionId, $language);
	}

	public function testExistsMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::latest();

		$this->assertFalse($this->storage->exists($versionId, $this->app->language('en')));
		$this->assertFalse($this->storage->exists($versionId, $this->app->language('de')));

		$this->storage->create($versionId, $this->app->language('en'), []);
		$this->storage->create($versionId, $this->app->language('de'), []);

		$this->assertTrue($this->storage->exists($versionId, $this->app->language('en')));
		$this->assertTrue($this->storage->exists($versionId, $this->app->language('de')));
	}

	public function testExistsSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::latest();
		$language  = Language::single();

		$this->assertFalse($this->storage->exists($versionId, $language));

		$this->storage->create($versionId, $language, []);

		$this->assertTrue($this->storage->exists($versionId, $language));
	}

	public function testExistsNoneExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('en')));
		$this->assertFalse($this->storage->exists(VersionId::changes(), $this->app->language('de')));
	}

	public function testExistsNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertFalse($this->storage->exists(VersionId::changes(), Language::single()));
	}

	public function testModifiedNoneExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), $this->app->language('en')));
		$this->assertNull($this->storage->modified(VersionId::latest(), $this->app->language('en')));
	}

	public function testModifiedNoneExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$this->assertNull($this->storage->modified(VersionId::changes(), Language::single()));
		$this->assertNull($this->storage->modified(VersionId::latest(), Language::single()));
	}

	public function testModifiedSomeExistingMultiLanguage()
	{
		$this->setUpMultiLanguage();

		$changes  = VersionId::changes();
		$language = $this->app->language('en');

		$this->storage->create($changes, $language, []);

		$this->assertIsInt($this->storage->modified($changes, $language));
		$this->assertNull($this->storage->modified(VersionId::latest(), $language));
	}

	public function testModifiedSomeExistingSingleLanguage()
	{
		$this->setUpSingleLanguage();

		$changes  = VersionId::changes();
		$language = Language::single();

		$this->storage->create($changes, $language, []);

		$this->assertIsInt($this->storage->modified($changes, $language));
		$this->assertNull($this->storage->modified(VersionId::latest(), $language));
	}

	public function testMoveToTheSameStorageLocation()
	{
		$this->setUpSingleLanguage();

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();

		// create some content to move
		$this->storage->create($versionId, $language, $content);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language));

		$this->storage->move(
			$versionId,
			$language,
			$versionId,
			$language
		);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language), 'The content should still be the same');
	}

	public function testMoveToTheSameStorageLocationWithAnotherStorageInstance()
	{
		$this->setUpSingleLanguage();

		$content   = ['title' => 'Test'];
		$versionId = VersionId::latest();
		$language  = Language::single();
		$storage   = new MemoryStorage($this->model);

		// create some content to move
		$this->storage->create($versionId, $language, $content);

		$this->assertTrue($this->storage->exists($versionId, $language));
		$this->assertSame($content, $this->storage->read($versionId, $language));

		$this->storage->move(
			$versionId,
			$language,
			$versionId,
			$language,
			$storage
		);

		$this->assertFalse($this->storage->exists($versionId, $language), 'The old storage entry should be gone now');

		$this->assertTrue($storage->exists($versionId, $language));
		$this->assertSame($content, $storage->read($versionId, $language));
	}

	public function testTouchMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$time = time();

		$this->storage->create($versionId, $language, []);
		$this->storage->touch($versionId, $language);

		$this->assertGreaterThanOrEqual($time, $this->storage->modified($versionId, $language));
	}

	public function testTouchSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$time = time();

		$this->storage->create($versionId, $language, []);
		$this->storage->touch($versionId, $language);

		$this->assertGreaterThanOrEqual($time, $this->storage->modified($versionId, $language));
	}

	public function testUpdateMultiLang()
	{
		$this->setUpMultiLanguage();

		$versionId = VersionId::changes();
		$language  = $this->app->language('en');

		$this->assertCreateAndUpdate($versionId, $language);
	}

	public function testUpdateSingleLang()
	{
		$this->setUpSingleLanguage();

		$versionId = VersionId::changes();
		$language  = Language::single();

		$this->assertCreateAndUpdate($versionId, $language);
	}
}
