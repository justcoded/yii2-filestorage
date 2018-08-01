<?php

namespace justcoded\yii2\filestorage\models;

use Yii;

/**
 * This is the model class for table "fly_file_image".
 *
 * @property int $id
 * @property int $file_id
 * @property string $size_key
 * @property string $crop
 * @property int $width
 * @property int $height
 *
 * @property FlyFile $parent
 * @property FlyFile $file
 */
class FlyFileImage extends \yii\db\ActiveRecord
{
	public const CROP_TOP = 'top';
	public const CROP_RIGHT = 'right';
	public const CROP_BOTTOM = 'bottom';
	public const CROP_LEFT = 'left';

	public function fields()
	{
		return [
			'id',
			'parent_id',
			'file',
			'size_key',
			'crop',
			'width',
			'height'
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'fly_file_image';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[
				[
					'parent_id',
					'file_id',
					'size_key',
					'width',
					'height'
				],
				'required'
			],
			[
				[
					'parent_id',
					'file_id',
					'width',
					'height'
				],
				'integer'
			],
			[
				[
					'size_key',
					'crop'
				],
				'string',
				'max' => 255
			],
			[
				['parent_id'],
				'exist',
				'skipOnError' => true,
				'targetClass' => FlyFile::className(),
				'targetAttribute' => ['parent_id' => 'id']
			],
			[
				['file_id'],
				'exist',
				'skipOnError' => true,
				'targetClass' => FlyFile::className(),
				'targetAttribute' => ['file_id' => 'id']
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'ID'),
			'parent_id' => Yii::t('app', 'Parent ID'),
			'file_id' => Yii::t('app', 'File ID'),
			'size_key' => Yii::t('app', 'Size Key'),
			'crop' => Yii::t('app', 'Crop'),
			'width' => Yii::t('app', 'Width'),
			'height' => Yii::t('app', 'Height'),
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getParent()
	{
		return $this->hasOne(FlyFile::className(), ['id' => 'parent_id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getFile()
	{
		return $this->hasOne(FlyFile::className(), ['id' => 'file_id']);
	}
}
