<?php

use Phinx\Migration\AbstractMigration;

class SeedUsersTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $this->execute("CREATE TABLE `users` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `name` varchar(255) DEFAULT NULL,
              `email` varchar(255) NOT NULL DEFAULT '',
              `password` varchar(255) NOT NULL DEFAULT '',
              `created_at` timestamp NULL DEFAULT NULL,
              `updated_at` timestamp NULL DEFAULT NULL,
              `active` tinyint(1) DEFAULT NULL,
              `active_hash` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=latin1;"
        );
    }
}
