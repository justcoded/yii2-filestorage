<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\helpers;

use Imagine\Gmagick\Imagine;
use Imagine\Image\AbstractImage;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use Imagine\Image\ManipulatorInterface;
use Imagine\Image\Point;
use Imagine\Image\ProfileInterface;
use justcoded\yii2\filestorage\models\FlyFile;
use justcoded\yii2\filestorage\storage\Filestorage;
use lsolesen\pel\PelJpeg;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\imagine\Image;
use yii\web\UnauthorizedHttpException;
use yii\web\UploadedFile;

/**
 * Class ImageHelper. Helper for uploaded image files.
 *
 * @package justcoded\yii2\filestorage\helpers
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
class ImageHelper
{
	/**
	 * String for mark auto height or width.
	 */
	const SIZE_AUTO = 'auto';

	/**
	 * Resize image by FlyFile object or path using storage component.
	 *
	 * @param string|FlyFile $file File path or model.
	 * @param string|array $size Width and height in string or array.
	 * @param array $crop Crop options.
	 * @param string|null $savePath Output path for file.
	 * @param Filestorage|null $storage Storage component.
	 *
	 * @return mixed
	 */
	public static function resize($file, $size, $crop = [], $savePath = null, $storage = null)
	{
		$sizes = self::normalizeSizeParam($size);

		if (is_string($file)) {
			$path = $file;
		} else {
			$path = $file->path;
			$storage = $file->filestorage;
		}

		if (ArrayHelper::isIn(self::SIZE_AUTO, $sizes)) {
			$image = Image::getImagine()->read($storage->readStream($path));

			$fileSize = $image->getSize();

			$width = $fileSize->getWidth();
			$height = $fileSize->getHeight();

			$ratio = $width / $height;

			$box = new Box(($sizes[0] === self::SIZE_AUTO ? round($sizes[1] * $ratio) : $sizes[0]),
				($sizes[1] === self::SIZE_AUTO ? round($sizes[0] / $ratio) : $sizes[1]));

			$image->resize($box);
		} else {
			$image = Image::resize($storage->readStream($path), $sizes[0], $sizes[1]);
			//$image = Image::crop($storage->readStream($path), $size[0], $size[1],ImageInterface::THUMBNAIL_INSET);

			if ($crop === true || $crop === 'true') {
				$box = new Box($sizes[0], $sizes[1]);

				$image->crop(new Point(0, 0), $box);

			} else {
				if (!empty($crop)) {
					//$image = $image->crop(new Point($crop));
				}
			}
		}

		$savePath = $savePath ?? FileHelper::addSuffix($file->path, $size);

		if (self::save($storage, $image, $savePath)) {
			return $savePath;
		}

		return false;
	}

	/**
	 * Save image in Storage.
	 *
	 * @param Filestorage $storage Storage component.
	 * @param AbstractImage $image Imagine image.
	 * @param string $path Path to save.
	 *
	 * @return bool
	 */
	public static function save($storage, $image, $path)
	{
		$tempPath = '/tmp/' . time();

		$image->save($tempPath);

		return $storage->save($tempPath, $path);
	}

	/**
	 * Convert size string to array of 2 sizes.
	 *
	 * @param string $param Size string.
	 * @param string $delimiter Delimiter for explode.
	 *
	 * @return array
	 */
	public static function normalizeSizeParam($param, $delimiter = 'x')
	{
		return explode($delimiter, $param, 2);
	}

	/**
	 * Get width and height of FlyFile.
	 *
	 * @param string|FlyFile $file File path or model.
	 * @param Filestorage|null $storage Storage component.
	 *
	 * @return \Imagine\Image\BoxInterface
	 */
	public static function getRealSize($file, $storage = null)
	{
		if (is_string($file)) {
			$path = $file;
		} else {
			$path = $file->path;
			$storage = $file->filestorage;
		}

		$image = Image::getImagine()->read($storage->readStream($path));

		return $image->getSize();
	}

	/**
	 * Get <img> tag with resized image.
	 *
	 * @param FlyFile $file File to resize.
	 * @param string $size Resizing size.
	 * @param null $crop Crop options.
	 * @param string $resizeRoute Route to resize files.
	 * @param array $options Tag options.
	 *
	 * @return string
	 */
	public static function thumb($file, $size, $crop = null, $resizeRoute = '/file/resize', $options = [])
	{
		switch ($file->mime_type) {
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				if (isset($size)) {
					if (!isset($file->images[$size])) {
						return Html::img([
							$resizeRoute,
							'id' => $file->id,
							'size' => $size,
							'crop' => $crop
						], $options[$file->mime_type] ?? $options);
					}

					return Html::img($file->images[$size]->file->url, $options[$file->mime_type] ?? $options);
				}

				return Html::img($file->url, $options[$file->mime_type] ?? $options);
			break;
			default:
				return '';
			break;
		}
	}

	/**
	 * Auto fix exif orientation and clear exif orientations data for uploaded file.
	 *
	 * @param UploadedFile $uploadedFile
	 */
	public static function fixOrientation($uploadedFile)
	{
		try {
			$exif = exif_read_data($uploadedFile->tempName);

			if (!empty($exif['Orientation'])) {
				$image = Image::getImagine()->open($uploadedFile->tempName);

				Image::autorotate($image);

				$image->strip();

				$image->save(null, ['format' => $uploadedFile->extension]);
			}
		} catch (\Exception $e) {

		}
	}
}