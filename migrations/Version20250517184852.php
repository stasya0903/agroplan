<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250517184852 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE recipes_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE recipes (id INT NOT NULL, chemical_id INT NOT NULL, problem_id INT NOT NULL, work_id INT DEFAULT NULL, dosis_in_ml INT NOT NULL, note VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A369E2B5E1770A76 ON recipes (chemical_id)');
        $this->addSql('CREATE INDEX IDX_A369E2B5A0DCED86 ON recipes (problem_id)');
        $this->addSql('CREATE INDEX IDX_A369E2B5BB3453DB ON recipes (work_id)');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B5E1770A76 FOREIGN KEY (chemical_id) REFERENCES chemicals (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B5A0DCED86 FOREIGN KEY (problem_id) REFERENCES problem_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B5BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE recipes_id_seq CASCADE');
        $this->addSql('ALTER TABLE recipes DROP CONSTRAINT FK_A369E2B5E1770A76');
        $this->addSql('ALTER TABLE recipes DROP CONSTRAINT FK_A369E2B5A0DCED86');
        $this->addSql('ALTER TABLE recipes DROP CONSTRAINT FK_A369E2B5BB3453DB');
        $this->addSql('DROP TABLE recipes');
    }
}
