<?php

namespace justcoded\yii2\filestorage\storage\adapters;

use League\Flysystem\Adapter\Local;
use yii\helpers\Url;

class LocalAdapter extends Local
{
	public $baseUrl;

	public function __construct(
		string $root,
		$writeFlags = LOCK_EX,
		int $linkHandling = self::DISALLOW_LINKS,
		array $permissions = []
	) {
		parent::__construct($root, $writeFlags, $linkHandling, $permissions);

		$this->baseUrl = $this->baseUrl ?? Url::base(true);
	}

	function applyPathPrefix($path)
	{
		return parent::applyPathPrefix($this->normalizePath($path));
	}

	public static function normalizePath($path)
	{
		$pathArray = explode(DIRECTORY_SEPARATOR, $path);

		$fileName = array_pop($pathArray);

		$fileNameSplit = preg_replace('/[.-]/i', '', $fileName);

		return implode(DIRECTORY_SEPARATOR, array_merge($pathArray, [
			$fileNameSplit{0} . $fileNameSplit{1},
			$fileNameSplit{2} . $fileNameSplit{3},
			$fileName
		]));
	}

	public function getPublicUrl($path)
	{
		return implode(DIRECTORY_SEPARATOR, [
			$this->baseUrl,
			$this->pathPrefix,
			$this->getMetadata($path)['path']
		]);
	}
}
