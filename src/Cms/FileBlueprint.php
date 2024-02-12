<?php

namespace Kirby\Cms;

use Kirby\Filesystem\F;
use Kirby\Filesystem\Mime;
use Kirby\Toolkit\Str;

/**
 * Extension of the basic blueprint class
 * to handle all blueprints for files.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileBlueprint extends Blueprint
{
	/**
	 * `true` if the default accepted
	 * types are being used
	 */
	protected bool $defaultTypes = false;

	public function __construct(array $props)
	{
		parent::__construct($props);

		// normalize all available page options
		$this->props['options'] = $this->normalizeOptions(
			$this->props['options'] ?? true,
			// defaults
			[
				'access'         => null,
				'changeName'     => null,
				'changeTemplate' => null,
				'create'     	 => null,
				'delete'     	 => null,
				'list'     	     => null,
				'read'       	 => null,
				'replace'    	 => null,
				'update'     	 => null,
			]
		);

		// normalize the accept settings
		$this->props['accept'] = $this->normalizeAccept($this->props['accept'] ?? []);
	}

	public function accept(): array
	{
		return $this->props['accept'];
	}

	/**
	 * Returns the list of all accepted MIME types for
	 * file upload or `*` if all MIME types are allowed
	 *
	 * @deprecated 4.2.0 Use `acceptAttribute` instead
	 */
	public function acceptMime(): string
	{
		// don't disclose the specific default types
		if ($this->defaultTypes === true) {
			return '*';
		}

		$accept       = $this->accept();
		$restrictions = [];

		if (is_array($accept['mime']) === true) {
			$restrictions[] = $accept['mime'];
		} else {
			// only fall back to the extension or type if
			// no explicit MIME types were defined
			// (allows to set custom MIME types for the frontend
			// check but still restrict the extension and/or type)

			if (is_array($accept['extension']) === true) {
				// determine the main MIME type for each extension
				$restrictions[] = array_map(
					[Mime::class, 'fromExtension'],
					$accept['extension']
				);
			}

			if (is_array($accept['type']) === true) {
				// determine the MIME types of each file type
				$mimes = [];
				foreach ($accept['type'] as $type) {
					if ($extensions = F::typeToExtensions($type)) {
						$mimes[] = array_map(
							[Mime::class, 'fromExtension'],
							$extensions
						);
					}
				}

				$restrictions[] = array_merge(...$mimes);
			}
		}

		if ($restrictions !== []) {
			if (count($restrictions) > 1) {
				// only return the MIME types that are allowed by all restrictions
				$mimes = array_intersect(...$restrictions);
			} else {
				$mimes = $restrictions[0];
			}

			// filter out empty MIME types and duplicates
			return implode(', ', array_filter(array_unique($mimes)));
		}

		// no restrictions, accept everything
		return '*';
	}


	/**
	 * Returns the list of all accepted file extensions
	 * for file upload or `*` if all extensions are allowed
	 *
	 * If a mime type is specified in the blueprint, the extensions and types options are ignored for the browser.
	 * Extensions and Types, however, are still used to validate an uploaded file on the server.
	 * This behavior might change in the future to better represent what file extensions are actually allowed.
	 *
	 * If no mime type is specified, the intersection between manually defined extensions and the Kirby "file types" is returned.
	 * If the intersection is empty, an empty string is returned.
	 * This behavior might change in the future to instead return the union of mime, extensions and types.
	 *
	 * @since 4.2.0
	 * @return string
	 */
	public function acceptAttribute(): string
	{
		// don't disclose the specific default types
		if ($this->defaultTypes === true) {
			return '*';
		}

		$accept = $this->accept();

		// get extensions from "mime" option
		if (is_array($accept['mime']) === true) {
			// determine the extensions for each MIME type
			$extensions = array_map(
				fn ($pattern) => Mime::toExtensions($pattern, true),
				$accept['mime']
			);

			$fromMime = array_unique(array_merge(...$extensions));

			// return early to ignore the other options
			return implode(',', array_map(fn ($ext) => ".$ext", $fromMime));
		}

		$restrictions = [];

		// get extensions from "type" option
		if (is_array($accept['type']) === true) {
			$extensions = array_map(
				F::typeToExtensions(...),
				$accept['type']
			);

			// F::typeToExtensions might return null instead of empty arrays,
			// we need to filter those out
			$fromType = array_merge(...array_filter($extensions));
			$restrictions[] = $fromType;
		}

		// get extensions from "extension" option
		if (is_array($accept['extension']) === true) {
			$fromExtension = $accept['extension'];
			$restrictions[] = $fromExtension;
		}

		// intersect all restrictions
		if (count($restrictions) > 1) {
			$list = array_intersect(...$restrictions);
		} else {
			$list = $restrictions[0];
		}

		$list = array_unique($list);

		// format the list to include a leading dot on each extension
		return implode(',', array_map(fn ($ext) => ".$ext", $list));

		// unknown restrictions, accept everything
		return '*';
	}

	protected function normalizeAccept(mixed $accept = null): array
	{
		$accept = match (true) {
			is_string($accept) 		=> ['mime' => $accept],
			// explicitly no restrictions at all
			$accept === true 		=> ['mime' => null],
			// no custom restrictions
			empty($accept) === true => [],
			// custom restrictions
			default 				=> $accept
		};

		$accept = array_change_key_case($accept);

		$defaults = [
			'extension'   => null,
			'mime'        => null,
			'maxheight'   => null,
			'maxsize'     => null,
			'maxwidth'    => null,
			'minheight'   => null,
			'minsize'     => null,
			'minwidth'    => null,
			'orientation' => null,
			'type'        => null
		];

		// default type restriction if none are configured;
		// this ensures that no unexpected files are uploaded
		if (
			array_key_exists('mime', $accept) === false &&
			array_key_exists('extension', $accept) === false &&
			array_key_exists('type', $accept) === false
		) {
			$defaults['type']   = ['image', 'document', 'archive', 'audio', 'video'];
			$this->defaultTypes = true;
		}

		$accept = array_merge($defaults, $accept);

		// normalize the MIME, extension and type from strings into arrays
		if (is_string($accept['mime']) === true) {
			$accept['mime'] = array_map(
				fn ($mime) => $mime['value'],
				Str::accepted($accept['mime'])
			);
		}

		if (is_string($accept['extension']) === true) {
			$accept['extension'] = array_map(
				'trim',
				explode(',', $accept['extension'])
			);
		}

		if (is_string($accept['type']) === true) {
			$accept['type'] = array_map(
				'trim',
				explode(',', $accept['type'])
			);
		}

		return $accept;
	}
}
