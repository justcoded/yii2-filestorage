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
class m180508_115754_create_fly_file_table extends Migration
{

	private $tableName = '{{%fly_file}}';

	/**
	 * @inheritdoc
	 */
	public function up()
	{
		$this->createTable($this->tableName, [
			'id' => $this->primaryKey(),
			'storage' => $this->string()->notNull(),
			'url' => $this->text(),
			'path' => $this->text(),
			'name' => $this->string(255)->notNull(),
			'description' => $this->text(),
			'mime_type' => $this->string(255)->notNull(),
			'size' => $this->integer()->notNull(),
			'meta' => $this->json(),
			'created_by' => $this->integer(),
			'created_at' => $this->integer(),
		], $this->tableOptions());
	}

	/**
	 * @inheritdoc
	 */
	public function down()
	{
		$this->dropTable($this->tableName);
	}
}
