<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

namespace justcoded\yii2\filestorage\db;

use yii\base\Behavior;

/**
 * Class ActiveQueryMorphBehavior. Used by system in MorphRelationsTrait.
 *
 * @package justcoded\yii2\filestorage\db
 * @author Aleksey Fedorenko <alfedorenko@justcoded.co>
 * @since 0.7.1.0
 */
class ActiveQueryMorphBehavior extends Behavior
{
	/**
	 * @var string $morphName Name of morph relation.
	 */
	public $morphName;
	/**
	 * @var array Additional conditions for link.
	 */
	public $extraCondition;
}