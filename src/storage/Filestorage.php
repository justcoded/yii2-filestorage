<?php

namespace justcoded\yii2\filestorage\storage;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Yii;
use yii\base\Component;
use yii\helpers\Url;
use yii\web\UploadedFile;

class Filestorage extends Component
{
	/**
	 * @var \League\Flysystem\Config|array|string|null
	 */
	public $config;
	/**
	 * @var \League\Flysystem\FilesystemInterface
	 */
	protected $filesystem;

	public $adapter;

	public $adapterConfig = [];

	public function initAdapter()
	{
		$this->adapter = Yii::createObject($this->adapter, $this->adapterConfig);
	}

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->initAdapter();

		$this->filesystem = new Filesystem($this->adapter, $this->config);
	}

	/**
	 * @param string $path
	 * @param string $fileModel
	 *
	 * @return bool|string
	 * @throws \yii\base\Exception
	 */
	public function save($path, $dstPath)
	{
		$stream = fopen($path, 'rb+');

		if ($this->filesystem->putStream($dstPath, $stream)) {
			return $this->filesystem->getMetadata($dstPath);
		}

		return false;
	}

	public function getPublicUrl($path)
	{
		$adapter = $this->filesystem->getAdapter($path);

		if (method_exists($adapter, 'getPublicUrl')) {
			return $adapter->getPublicUrl($path);
		}
		if (is_a($adapter, Local::class)) {
			return Url::base(true) . '/' . $this->adapterConfig[0] . '/' . $adapter->getMetadata($path)['path'];
		} /*elseif () {  // for S3
		}*/
	}

	/**
	 * @param string $method
	 * @param array $parameters
	 *
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		return call_user_func_array([
			$this->filesystem,
			$method
		], $parameters);
	}
}
