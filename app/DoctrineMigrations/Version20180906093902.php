<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20180906093902 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE client_notification (id INT AUTO_INCREMENT NOT NULL, client_id BIGINT NOT NULL, lead_id BIGINT NOT NULL, type VARCHAR(255) NOT NULL, campaigns VARCHAR(255) NOT NULL, createdAt DATETIME DEFAULT NULL, INDEX IDX_E5ED4CB19EB6921 (client_id), INDEX IDX_E5ED4CB55458D (lead_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client_notification ADD CONSTRAINT FK_E5ED4CB19EB6921 FOREIGN KEY (client_id) REFERENCES client (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE client_notification ADD CONSTRAINT FK_E5ED4CB55458D FOREIGN KEY (lead_id) REFERENCES lead (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE staging_contact_dqp RENAME INDEX email TO email_index');
        $this->addSql('ALTER TABLE staging_contact_dqp RENAME INDEX phone TO phone_index');
        $this->addSql('ALTER TABLE contact CHANGE postalcode1 postalcode1 VARCHAR(255) DEFAULT NULL, CHANGE postalcode2 postalcode2 VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE contact RENAME INDEX email TO email_index');
        $this->addSql('ALTER TABLE contact RENAME INDEX external_id TO external_id_index');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE client_notification');
        $this->addSql('ALTER TABLE contact CHANGE postalcode1 postalcode1 INT DEFAULT NULL, CHANGE postalcode2 postalcode2 INT DEFAULT NULL');
        $this->addSql('ALTER TABLE contact RENAME INDEX email_index TO email');
        $this->addSql('ALTER TABLE contact RENAME INDEX external_id_index TO external_id');
        $this->addSql('ALTER TABLE staging_contact_dqp RENAME INDEX email_index TO email');
        $this->addSql('ALTER TABLE staging_contact_dqp RENAME INDEX phone_index TO phone');
    }
}
