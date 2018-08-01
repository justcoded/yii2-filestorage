<?php

namespace justcoded\yii2\filestorage\models;

use justcoded\yii2\filestorage\db\MorphRelationsTrait;
use justcoded\yii2\filestorage\storage\Filestorage;
use League\Flysystem\Filesystem;
use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "file".
 *
 * @property int $id
 * @property Filesystem $storage
 * @property string $url
 * @property string $path
 * @property string $name
 * @property string $description
 * @property string $mime_type
 * @property int $size
 * @property array $meta
 * @property int $created_by
 * @property int $created_at
 *
 * @property Filestorage $filestorage
 * @property FlyFileImage[] $images
 * @property FlyFileImage $imageData
 * @property FlyFileImage[] $imageSizes indexed by size_key
 */
class FlyFile extends \yii\db\ActiveRecord
{
	/**
	 * @var int $id
	 * @var Filesystem $storage
	 * @var string $url
	 * @var string $path
	 * @var string $name
	 * @var string $description
	 * @var string $mime_type
	 * @var int $size
	 * @var array $meta
	 * @var int $created_by
	 * @var int $created_at
	 *
	 * @var Filestorage $filestorage
	 * @var FlyFileImage[] $images
	 * @var FlyFileImage $imageData
	 * @var FlyFileImage[] $imageSizes indexed by size_key
	 */

	use MorphRelationsTrait;

	/**
	 * @return array
	 */
	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::class,
				'updatedAtAttribute' => null,
			],
			[
				'class' => BlameableBehavior::class,
				'updatedByAttribute' => null
			],
		];
	}

	/**
	 * @return array
	 */
	public function fields()
	{
		return [
			'id',
			'storage',
			'url',
			'name',
			'description',
			'mime_type',
			'size',
			'image_data' => 'imageData',
			'images'
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return '{{%fly_file}}';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[
				[
					'storage',
					'name',
					'size'
				],
				'required'
			],
			[
				[
					'size',
					'created_by',
					'created_at'
				],
				'integer'
			],
			[
				[
					'url',
					'path',
					'description'
				],
				'string'
			],
			[
				['meta'],
				'safe'
			],
			[
				[
					'storage',
					'name',
					'mime_type'
				],
				'string',
				'max' => 255
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
			'storage' => Yii::t('app', 'Storage'),
			'url' => Yii::t('app', 'Url'),
			'path' => Yii::t('app', 'Path'),
			'name' => Yii::t('app', 'Name'),
			'description' => Yii::t('app', 'Description'),
			'mime_type' => Yii::t('app', 'Mime Type'),
			'size' => Yii::t('app', 'Size'),
			'meta' => Yii::t('app', 'Meta'),
			'created_by' => Yii::t('app', 'Created By'),
			'created_at' => Yii::t('app', 'Created At'),
		];
	}

	/**
	 * @return null|object
	 * @throws InvalidConfigException
	 */
	public function getFilestorage()
	{
		if (($storage = Yii::$app->get($this->storage)) === null) {
			throw new InvalidConfigException("App have not storage \"$this->storage\"");
		}

		return $storage;
	}

	/**
	 * @return array
	 */
	public function getFileables()
	{
		return $this->morphToMany('fileable', 'fly_file_relation', 'file_id');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getImages()
	{
		return $this->hasMany(FlyFileImage::class, ['parent_id' => 'id'])->indexBy('size_key');
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getImageData()
	{
		return $this->hasOne(FlyFileImage::class, ['file_id' => 'id'])->asArray();
	}

}
