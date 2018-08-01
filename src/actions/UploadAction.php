<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\actions;

use app\models\ActiveRecord;
use diversen\imageRotate;
use justcoded\yii2\filestorage\helpers\ImageHelper;
use justcoded\yii2\filestorage\models\FlyFile;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\base\Model;
use yii\di\Instance;
use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\web\BadRequestHttpException;
use yii\web\Response;
use yii\web\UploadedFile;
use justcoded\yii2\filestorage\storage\Filestorage;

/**
 * Class UploadAction. Action for upload file(s).
 *
 * @package justcoded\yii2\filestorage\actions
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
class UploadAction extends Action
{
	/** @var Filestorage|string $storage Storage component for process files. */
	public $storage;
	/**
	 * @var string $storageName Name of Storage component.
	 */
	protected $storageName;

	/**
	 * @var string $path Base path to save files. Added to base Storage path.
	 */
	public $path;

	/**
	 * @var ActiveRecord $model Class name for relation model.
	 */
	public $model;
	/**
	 * @var ActiveRecord $modelObj Temporally created model object for use model methods.
	 */
	protected $modelObj;

	/**
	 * @var string $relation Relation attribute from model.
	 */
	public $relation;

	/**
	 * @var string $attribute Form attribute which used to get file.
	 */
	public $attribute;

	/**
	 * @var bool $multiple Flag for multiple files upload.
	 */
	public $multiple = false;

	/**
	 * @var string $response Response format for action.
	 */
	public $response = Response::FORMAT_JSON;

	/**
	 * {@inheritdoc}
	 *
	 * @throws InvalidConfigException
	 */
	public function init()
	{
		if (!isset($this->storage)) {
			throw new InvalidConfigException('Config for ' . self::class . ' have not required attribute "storage"');
		}

		if (is_string($this->storage)) {
			$this->storageName = $this->storage;
			$this->storage = Yii::$app->get($this->storage);
		}

		if ($this->model !== null) {
			$this->modelObj = Yii::createObject($this->model);

			if (!($this->modelObj instanceof Model)) {
				throw new InvalidConfigException('Config for ' . self::class . ' have not required attribute "model"');
			}

			$this->attribute = $this->attribute ?? Html::getInputName($this->modelObj, $this->relation);
		}

		if ($this->attribute === null) {
			throw new InvalidConfigException('Config for ' . self::class . ' have not required attribute "attribute"');
		}

		if ($this->path === null) {
			if ($this->model !== null) {
				$this->path = $this->model::tableName();
			} else {
				throw new InvalidConfigException('Config for ' . self::class . ' have not required attribute "path"');
			}
		}

		Yii::$app->response->statusCode = 201;
		\Yii::$app->response->format = $this->response;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @return array
	 * @throws BadRequestHttpException
	 * @throws \yii\base\Exception
	 */
	public function run()
	{
		$fileable = $this->getFileable();

		$uploadedFiles = UploadedFile::getInstancesByName($this->attribute);

		if (empty($uploadedFiles)) {
			throw new BadRequestHttpException("Param '$this->attribute' must not be NULL");
		}

		$savedFiles = [];

		$storage = $this->storage;

		foreach ($uploadedFiles as $uploadedFile) {
			/* @var \yii\web\UploadedFile $uploadedFile */

			/* @var FlyFile $file */
			if (isset($fileable) && !$this->multiple) {
				$file = $this->getNewFile($fileable, $this->relation);
			} else {
				$file = Instance::of(FlyFile::class)->get();
			}

			$file->storage = $this->storageName;
			$file->name = $this->normalizeFileName($uploadedFile->name);

			$path = implode(DIRECTORY_SEPARATOR, [
				$this->path,
				$file->name
			]);

			ImageHelper::fixOrientation($uploadedFile);

			$metaData = $storage->save($uploadedFile->tempName, $path);

			$file->path = $path;
			$file->url = $storage->getPublicUrl($path);
			$file->mime_type = $storage->getMimeType($path);
			$file->size = $storage->getSize($path);
			$file->meta = $metaData;

			if ($file->save()) {
				$savedFiles[] = $file;

				if (isset($fileable)) {
					$fileable->link($this->relation, $file);
				}
			}
		}

		if (count($savedFiles) == 1) {
			$file = $savedFiles[0];

			return compact('file');
		} else {
			$files = $savedFiles;

			return compact('files');
		}
	}

	/**
	 * Get model which will be attached to uploaded file.
	 *
	 * @return ActiveRecord|null
	 */
	protected function getFileable()
	{
		if ($this->model !== null) {
			return $fileable = $this->model::findOne(Yii::$app->request->get($this->model::primaryKey()[0]));
		}

		return null;
	}

	/**
	 * Unlink all files from model and get new file model.
	 *
	 * @param ActiveRecord $fileable Linked model.
	 * @param string $relation Relation attribute.
	 *
	 * @return FlyFile
	 */
	protected function getNewFile($fileable, $relation)
	{
		if (($model = $fileable->$relation) !== null) {
			$fileable->unlinkAll($relation, true);
		}

		$model = new FlyFile();

		return $model;
	}

	/**
	 * Format file name before saving.
	 *
	 * @param string $fileName Base file name.
	 *
	 * @return string
	 */
	protected function normalizeFileName($fileName)
	{
		$fileNameArray = explode('.', $fileName);

		$extension = array_pop($fileNameArray);

		return implode('.', [
			Inflector::slug(implode('.', $fileNameArray)),
			time(),
			$extension
		]);
	}
}
