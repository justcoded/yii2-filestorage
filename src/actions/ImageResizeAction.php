<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\actions;

use app\models\File;
use justcoded\yii2\filestorage\helpers\FileHelper;
use justcoded\yii2\filestorage\helpers\ImageHelper;
use justcoded\yii2\filestorage\models\FlyFile;
use justcoded\yii2\filestorage\models\FlyFileImage;
use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;
use yii\web\Response;

/**
 * Class ImageResizeAction. Action for resize image files.
 *
 * @package justcoded\yii2\filestorage\actions
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
class ImageResizeAction extends Action
{
	/**
	 * @var string $fileAttribute Attribute which used to identify file.
	 */
	public $fileAttribute = 'id';
	/**
	 * @var string $sizeAttribute GET param which used for set file size.
	 */
	public $sizeAttribute = 'size';
	/**
	 * @var string $cropAttribute GET param which used for set crop options to file.
	 */
	public $cropAttribute = 'crop';

	/**
	 * {@inheritdoc}
	 *
	 * @throws NotFoundHttpException
	 * @throws \Throwable
	 * @throws \yii\base\ExitException
	 * @throws \yii\db\Exception
	 * @throws \yii\db\StaleObjectException
	 */
	public function run()
	{
		$size = Yii::$app->request->get('size');

		$file = $this->findFile(Yii::$app->request->get($this->fileAttribute));
		$image = $this->findOrCreateFileImage($file->id, $size);

		if (isset($image->file)) {
			$image->file->delete();
		}

		$cropOptions = Yii::$app->request->get('crop');

		$result = ImageHelper::resize($file, $size, $cropOptions);

		$image->crop = $cropOptions ?? implode(',', (array)$cropOptions);

		$size = ImageHelper::getRealSize($result, $file->filestorage);
		$image->width = $size->getWidth();
		$image->height = $size->getHeight();

		$transaction = Yii::$app->db->beginTransaction();

		$imageFile = new FlyFile();

		$imageFile->storage = $file->storage;
		$imageFile->name = FileHelper::addSuffix($file->name, $size);
		$imageFile->path = $result;
		$imageFile->url = $file->filestorage->getPublicUrl($imageFile->path);
		$imageFile->mime_type = $file->filestorage->getMimeType($imageFile->path);
		$imageFile->size = $file->filestorage->getSize($imageFile->path);

		$imageFile->save();

		$image->file_id = $imageFile->id;

		if ($image->isNewRecord) {
			$file->link('images', $image);
		} else {
			$image->save();
		}

		$transaction->commit();

		Yii::$app->response->format = Response::FORMAT_RAW;
		header("Content-Type: $imageFile->mime_type;");

		echo $imageFile->filestorage->read($imageFile->path);

		Yii::$app->end();
	}

	/**
	 * Find file model by id.
	 *
	 * @param integer $id Id for finding file.
	 *
	 * @return FlyFile|null
	 * @throws NotFoundHttpException
	 */
	public function findFile($id)
	{
		if (($model = FlyFile::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}

	/**
	 * Find or create file image model by original file id and size key.
	 *
	 * @param integer $fileId Id of original file.
	 * @param string $size Size key.
	 *
	 * @return FlyFileImage|null
	 */
	public function findOrCreateFileImage($fileId, $size)
	{
		if (($model = FlyFileImage::findOne([
				'file_id' => $fileId,
				'size_key' => $size
			])) === null) {
			$model = new FlyFileImage();

			$model->size_key = $size;
		}

		return $model;
	}

	/**
	 * Find or create file model for file image by file image.
	 *
	 * @param FlyFileImage $image File image model.
	 *
	 * @return FlyFile
	 */
	public function findOrCreateFileImageFile($image)
	{
		if (($model = $image->file) === null) {
			$model = new FlyFile();
		}

		return $model;
	}
}
