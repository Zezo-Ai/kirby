<?php

namespace Kirby\Form\Field;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Form\FieldClass;
use Kirby\Form\Form;
use Kirby\Form\Mixin\EmptyState;
use Kirby\Form\Mixin\Max;
use Kirby\Form\Mixin\Min;
use Kirby\Toolkit\Str;

/**
 * Main class file of the entries field
 *
 * @package   Kirby Field
 * @author    Ahmet Bora <ahmet@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class EntriesField extends FieldClass
{
	use EmptyState;
	use Max;
	use Min;

	protected array $field;
	protected bool  $sortable = true;

	public function __construct(array $params = [])
	{
		parent::__construct($params);

		$this->setEmpty($params['empty'] ?? null);
		$this->setField($params['field'] ?? null);
		$this->setMax($params['max'] ?? null);
		$this->setMin($params['min'] ?? null);
		$this->setSortable($params['sortable'] ?? true);
	}

	public function field(): array
	{
		return $this->field;
	}

	public function fill(mixed $value = null): void
	{
		parent::fill(Data::decode($value, 'yaml'));
	}

	public function form(array $values = []): Form
	{
		return new Form([
			'fields' => [$this->field()],
			'values' => $values,
			'model'  => $this->model
		]);
	}

	public function props(): array
	{
		return [
				'empty'    => $this->empty(),
				'field'    => $this->field(),
				'max'      => $this->max(),
				'min'      => $this->min(),
				'sortable' => $this->sortable(),
			] + parent::props();
	}

	protected function setField(array|string|null $attrs = null): void
	{
		if (is_string($attrs) === true) {
			$attrs = ['type' => $attrs];
		}

		$attrs ??= ['type' => 'text'];

		if (in_array($attrs['type'], $this->supports()) === false) {
			throw new InvalidArgumentException(
				key: 'entries.supports',
				data: ['type' => $attrs['type']]
			);
		}

		$this->field = $attrs;
	}

	public function supports(): array
	{
		return [
			"email",
			"number",
			"slug",
			"tel",
			"text",
			"url"
		];
	}

	protected function setSortable(bool|null $sortable = true): void
	{
		$this->sortable = $sortable;
	}

	public function sortable(): bool
	{
		return $this->sortable;
	}

	public function toFormValue(bool $default = false): mixed
	{
		$value = parent::toFormValue($default);

		if ($value === null) {
			return null;
		}

		return Data::decode($value, 'yaml');
	}

	public function toStoredValue(bool $default = false): mixed
	{
		$value = parent::toStoredValue($default);

		if ($value === null) {
			return null;
		}

		return Data::encode($value, 'yaml');
	}

	public function validations(): array
	{
		return [
			'min',
			'max',
			'entries' => function ($values) {
				if (empty($values) === true) {
					return true;
				}

				foreach ($values as $index => $value) {
					$form = $this->form([$value]);

					foreach ($form->fields() as $field) {
						$errors = $field->errors();

						if ($errors !== []) {
							throw new InvalidArgumentException(
								key: 'entries.validation',
								data: [
									'field' => $this->label() ?? Str::ucfirst($this->name()),
									'index' => $index + 1
								]
							);
						}
					}
				}
			}
		];
	}
}
