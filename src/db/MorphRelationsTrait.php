<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\db;

use app\models\ActiveRecord;
use yii\db\ActiveQuery;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Trait MorphRelationsTrait. Trait for using morph relations.
 *
 * Note: MO- Morph to Object. OM- Object to morph.
 *
 * @package justcoded\yii2\filestorage\db
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
trait MorphRelationsTrait
{
	/*
	 * $relation[0] -> type
	 * $relation[1] -> id
	 */

	/**
	 * [MO] Set one-to-one relation from morph object to object.
	 *
	 * @param ActiveRecord|string $class Class name of model for make relation.
	 * @param string|array $morphName Array like ['able_type','able_id'] or string 'able'.
	 *
	 * @return ActiveQuery the relational query object.
	 */
	public function morphToOne($class, $morphName)
	{
		$morphName = $this->normalizeMorphFields($morphName);

		return $this->hasOne($class, ['id' => $morphName[1]])->onCondition([$morphName[0] => $class]);
	}

	/**
	 * [MO] Get list of relations.
	 *
	 * @param string|array $morphName Array like ['able_type','able_id'] or string 'able'.
	 * @param string|null $viaTable Name of junction table for many-to-many relation.
	 * @param string|null $viaLink Name of foreign key to morph table.
	 * @param array $extraCondition Extra condition for filtering.
	 *
	 * @return array of ActiveQuery elements like ['entity1'=>hasMany(),'entity2'=>hasMany()].
	 */
	public function morphToMany($morphName, $viaTable, $viaLink, $extraCondition = [])
	{
		$morphName = $this->normalizeMorphFields($morphName);

		$query = new Query();

		$types = $query->select($morphName[0])
			->from($viaTable)
			->where([$viaLink => $this->id])
			->groupBy($morphName[0])
			->all();

		return ArrayHelper::map($types, $morphName[0],
			function ($model) use ($viaTable, $viaLink, $morphName, $extraCondition) {
				$class = $model[$morphName[0]];

				return $this->hasMany($class, ['id' => $morphName[1]])
					->viaTable($viaTable, [$viaLink => 'id'],
						function ($query) use ($morphName, $class, $extraCondition) {
							$query->onCondition(ArrayHelper::merge([$morphName[0] => $class], $extraCondition));
						});
			});
	}

	/**
	 * [OM] Set one-to-one relation from object to morph object.
	 *
	 * @param ActiveRecord|string $class Class name of model for make relation.
	 * @param string|array $morphName Array like ['able_type','able_id'] or string 'able'.
	 * @param array $extraCondition Extra condition for filtering.
	 * @param string|null $viaTable Name of junction table for many-to-many relation.
	 * @param string|null $viaLink Name of foreign key to morph table.
	 * @param callable $viaCallable a PHP callback for customizing the relation associated with the junction table.
	 *
	 * @return ActiveQuery the relational query object.
	 */
	public function morphOne(
		$class,
		$morphName,
		$extraCondition = [],
		$viaTable = null,
		$viaLink = null,
		callable $viaCallable = null
	) {
		return $this->createMorphRelation($class, $morphName, $extraCondition, $viaTable, $viaLink, $viaCallable,
			false);
	}

	/**
	 * [OM] Set one-to-many relation from object to morph object.
	 *
	 * @param ActiveRecord|string $class Class name of model for make relation.
	 * @param string|array $morphName Array like ['able_type','able_id'] or string 'able'.
	 * @param array $extraCondition Extra condition for filtering.
	 * @param string|null $viaTable Name of junction table for many-to-many relation.
	 * @param string|null $viaLink Name of foreign key to morph table.
	 * @param callable $viaCallable a PHP callback for customizing the relation associated with the junction table.
	 *
	 * @return ActiveQuery the relational query object.
	 */
	public function morphMany(
		$class,
		$morphName,
		$extraCondition = [],
		$viaTable = null,
		$viaLink = null,
		callable $viaCallable = null
	) {
		return $this->createMorphRelation($class, $morphName, $extraCondition, $viaTable, $viaLink, $viaCallable, true);
	}

	/**
	 * [OM] Set relation from object to morph object.
	 *
	 * @param ActiveRecord|string $class Class name of model for make relation.
	 * @param string|array $morphName Array like ['able_type','able_id'] or string 'able'.
	 * @param array $extraCondition Extra condition for filtering.
	 * @param string|null $viaTable Name of junction table for many-to-many relation.
	 * @param string|null $viaLink Name of foreign key to morph table.
	 * @param callable $viaCallable a PHP callback for customizing the relation associated with the junction table.
	 * @param boolean $multiple hasMany() or hasOne().
	 *
	 * @return ActiveQuery the relational query object.
	 */
	private function createMorphRelation(
		$class,
		$morphName,
		$extraCondition,
		$viaTable,
		$viaLink,
		$viaCallable,
		$multiple
	) {
		$morphName = $this->normalizeMorphFields($morphName);

		$query = null;

		if ($viaTable !== null) {
			$query = $multiple ? $this->hasMany($class, ['id' => $viaLink]) : $this->hasOne($class, ['id' => $viaLink]);
			$query = $query->viaTable($viaTable, [$morphName[1] => 'id'],
				$viaCallable ?? function ($query) use ($morphName, $class, $extraCondition, $viaCallable) {
					$query->onCondition(ArrayHelper::merge([
						$morphName[0] => self::class,
					], $extraCondition));

					if ($viaCallable !== null) {
						call_user_func($viaCallable, $query);
					}
				});
		} else {
			$query = $multiple ? $this->hasMany($class, [$morphName[1] => 'id']) : $this->hasOne($class,
				[$morphName[1] => 'id']);
			$query = $query->where([$morphName[0] => self::class])->andWhere($extraCondition);
		}

		$query->attachBehavior('morph', [
			'class' => ActiveQueryMorphBehavior::class,
			'morphName' => $morphName,
			'extraCondition' => $extraCondition
		]);

		return $query;
	}

	public function link($name, $model, $extraColumns = [])
	{
		$relation = parent::getRelation($name);

		if ($morph = $relation->getBehavior('morph')) {
			/* @var $morph ActiveQueryMorphBehavior */

			$extraColumns[$morph->morphName[0]] = self::class;

			$extraColumns = ArrayHelper::merge($extraColumns, $morph->extraCondition);
		}

		parent::link($name, $model, $extraColumns);
	}

	/**
	 * Normalize relation.
	 * If $relation is string, converts that to array like ['able_type','able_id']
	 *
	 * @param string|array $morphName
	 *
	 * @return array
	 */
	private function normalizeMorphFields($morphName)
	{
		if (is_string($morphName)) {
			$morphName = [
				$morphName . '_type',
				$morphName . '_id'
			];
		}

		return $morphName;
	}
}