<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\helpers;

use Imagine\Image\AbstractImage;
use Imagine\Image\Point;
use justcoded\yii2\filestorage\models\FlyFile;
use justcoded\yii2\filestorage\storage\Filestorage;
use yii\helpers\Html;
use yii\imagine\Image;

/**
 * Class FileHelper. Helper for uploaded files.
 *
 * @package justcoded\yii2\filestorage\helpers
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
class FileHelper
{
	/**
	 * Add suffix to file name string. Example: file.suffix.png.
	 *
	 * @param string $fileName
	 * @param string $suffix
	 *
	 * @return string
	 */
	public static function addSuffix($fileName, $suffix)
	{
		$fileNameArray = explode('.', $fileName);

		$extension = array_pop($fileNameArray);

		return implode('.', [
			implode('.', $fileNameArray),
			$suffix,
			$extension
		]);
	}
}