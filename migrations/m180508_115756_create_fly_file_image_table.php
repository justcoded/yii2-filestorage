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
class m180508_115756_create_fly_file_image_table extends Migration
{

	private $tableName = '{{%fly_file_image}}';

	/**
	 * @inheritdoc
	 */
	public function up()
	{
		$this->createTable($this->tableName, [
			'id' => $this->primaryKey(),
			'parent_id' => $this->integer()->notNull(),
			'file_id' => $this->integer()->notNull(),
			'size_key' => $this->string()->notNull(),
			'crop' => $this->string()->notNull(),
			'width' => $this->integer()->notNull(),
			'height' => $this->integer()->notNull(),
		], $this->tableOptions());

		// add foreign key for table `fly_file`
		$this->addForeignKey('fk-fly_file_image-file_id', 'fly_file_image', 'file_id', 'fly_file', 'id', 'CASCADE');
		$this->addForeignKey('fk-fly_file_image-parent_id', 'fly_file_image', 'parent_id', 'fly_file', 'id', 'CASCADE');
	}

	/**
	 * @inheritdoc
	 */
	public function down()
	{
		$this->dropTable($this->tableName);
	}
}
