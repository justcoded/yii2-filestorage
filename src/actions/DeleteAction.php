<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\actions;

use app\models\ActiveRecord;
use justcoded\yii2\filestorage\models\FlyFile;
use Yii;
use yii\base\Action;
use yii\web\NotFoundHttpException;

/**
 * Class DeleteAction. Action for delete files.
 *
 * @package justcoded\yii2\filestorage\actions
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
class DeleteAction extends Action
{
	/**
	 * @var string $fileAttribute Attribute which used to identify file.
	 */
	public $fileAttribute = 'id';

	/**
	 * {@inheritdoc}
	 */
	public function run()
	{
		/** @var ActiveRecord $fileable */
		$files = $this->findFile(Yii::$app->request->get($this->fileAttribute));

		if (!empty($files)) {
			if (is_array($files)) {
				foreach ($files as $file) {
					$this->deleteFile($file);
				}
			} else {
				$this->deleteFile($files);
			}
		}

		Yii::$app->response->setStatusCode(204);
	}

	/**
	 * Delete file by model.
	 *
	 * @param FlyFile $file File model.
	 *
	 * @throws \yii\db\Exception
	 */
	protected function deleteFile($file)
	{
		$transaction = Yii::$app->db->beginTransaction();

		if ($file->delete()) {
			if ($file->filestorage->delete($file->path)) {
				$transaction->commit();
			}
		}
	}

	/**
	 * Find file by id.
	 *
	 * @param integer $id Id for finding file.
	 *
	 * @return FlyFile|null
	 * @throws NotFoundHttpException
	 */
	protected function findFile($id)
	{
		if (($model = FlyFile::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException('The requested page does not exist.');
	}
}
