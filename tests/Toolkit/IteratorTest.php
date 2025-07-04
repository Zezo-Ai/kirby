<?php

namespace Kirby\Toolkit;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Iterator::class)]
class IteratorTest extends TestCase
{
	public function testConstruct(): void
	{
		$iterator = new Iterator($expected = [
			'one' => 'eins',
			'two' => 'zwei',
		]);

		$this->assertSame($expected, $iterator->data);
	}

	public function testKey(): void
	{
		$iterator = new Iterator([
			'one' => 'eins',
			'two' => 'zwei',
		]);

		$this->assertSame('one', $iterator->key());
	}

	public function testKeys(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame([
			'one',
			'two',
			'three'
		], $iterator->keys());
	}

	public function testCurrent(): void
	{
		$iterator = new Iterator([
			'one' => 'eins',
			'two' => 'zwei',
		]);

		$this->assertSame('eins', $iterator->current());
	}

	public function testPrevNext(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame('eins', $iterator->current());

		$iterator->next();
		$this->assertSame('zwei', $iterator->current());

		$iterator->next();
		$this->assertSame('drei', $iterator->current());

		$iterator->prev();
		$this->assertSame('zwei', $iterator->current());

		$iterator->prev();
		$this->assertSame('eins', $iterator->current());
	}

	public function testRewind(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$iterator->next();
		$iterator->next();
		$this->assertSame('drei', $iterator->current());

		$iterator->rewind();
		$this->assertSame('eins', $iterator->current());
	}

	public function testValid(): void
	{
		$iterator = new Iterator([]);
		$this->assertFalse($iterator->valid());

		$iterator = new Iterator(['one' => 'eins']);
		$this->assertTrue($iterator->valid());
	}

	public function testCount(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);
		$this->assertSame(3, $iterator->count());

		$iterator = new Iterator(['one' => 'eins']);
		$this->assertSame(1, $iterator->count());

		$iterator = new Iterator([]);
		$this->assertSame(0, $iterator->count());
	}

	public function testIndexOf(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame(0, $iterator->indexOf('eins'));
		$this->assertSame(1, $iterator->indexOf('zwei'));
		$this->assertSame(2, $iterator->indexOf('drei'));
	}

	public function testKeyOf(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei',
			'three' => 'drei'
		]);

		$this->assertSame('one', $iterator->keyOf('eins'));
		$this->assertSame('two', $iterator->keyOf('zwei'));
		$this->assertSame('three', $iterator->keyOf('drei'));
	}

	public function testHas(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);

		$this->assertTrue($iterator->has('one'));
		$this->assertTrue($iterator->has('two'));
		$this->assertFalse($iterator->has('three'));
	}

	public function testIsset(): void
	{
		$iterator = new Iterator([
			'one'   => 'eins',
			'two'   => 'zwei'
		]);

		$this->assertTrue(isset($iterator->one));
		$this->assertTrue(isset($iterator->two));
		$this->assertFalse(isset($iterator->three));
	}

	public function testDebugInfo(): void
	{
		$array = [
			'one'   => 'eins',
			'two'   => 'zwei'
		];

		$iterator = new Iterator($array);
		$this->assertSame($array, $iterator->__debugInfo());
	}
}
