<?php
/**
 * @link https://justcoded.com/
 * @copyright © COPYRIGHT JUSTCODED 2018
 * @license https://justcoded.com/privacy/
 */

use app\modules\base\db\Migration;

/**
 * Handles the creation of table `file`.
 */
class m180508_115755_create_fly_file_relation_table extends Migration
{

	private $tableName = '{{%fly_file_relation}}';

	/**
	 * @inheritdoc
	 */
	public function up()
	{
		$this->createTable($this->tableName, [
			'file_id' => $this->integer()->notNull(),
			'fileable_type' => $this->string()->notNull(),
			'fileable_id' => $this->integer()->notNull(),
			'attribute' => $this->string()->notNull(),
		], $this->tableOptions());

		// add foreign key for table `fly_file`
		$this->addForeignKey('fk-fly_file_relation-file_id', 'fly_file_relation', 'file_id', 'fly_file', 'id',
			'CASCADE');
	}

	/**
	 * @inheritdoc
	 */
	public function down()
	{
		$this->dropTable($this->tableName);
	}
}
