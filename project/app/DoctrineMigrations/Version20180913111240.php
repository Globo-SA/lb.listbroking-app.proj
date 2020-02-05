<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180913111240 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE slow_log');
        $this->addSql('ALTER TABLE owner ADD notification_email_address VARCHAR(255) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE slow_log (start_time DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, user_host MEDIUMTEXT NOT NULL COLLATE utf8_general_ci, query_time TIME NOT NULL, lock_time TIME NOT NULL, rows_sent INT NOT NULL, rows_examined INT NOT NULL, db VARCHAR(512) NOT NULL COLLATE utf8_general_ci, last_insert_id INT NOT NULL, insert_id INT NOT NULL, server_id INT UNSIGNED NOT NULL, sql_text MEDIUMTEXT NOT NULL COLLATE utf8_general_ci, thread_id BIGINT UNSIGNED NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE owner DROP notification_email_address');
    }
}
