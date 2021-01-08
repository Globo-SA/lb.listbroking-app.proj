<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20210225160732 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE consent_revalidation (id INT AUTO_INCREMENT NOT NULL, contact_id BIGINT NOT NULL, type VARCHAR(255) NOT NULL, status VARCHAR(255) DEFAULT NULL, data LONGTEXT DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_431BA092E7A1254A (contact_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE brand (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, ivr_audio_url VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_1C52F9585E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE consent_revalidation ADD CONSTRAINT FK_431BA092E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE source ADD brand_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE source ADD CONSTRAINT FK_5F8A7F7344F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_5F8A7F7344F5D008 ON source (brand_id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE source DROP FOREIGN KEY FK_5F8A7F7344F5D008');
        $this->addSql('DROP TABLE consent_revalidation');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP INDEX IDX_5F8A7F7344F5D008 ON source');
        $this->addSql('ALTER TABLE source DROP brand_id');
    }
}
