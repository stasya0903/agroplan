<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416161043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE spending ADD work_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spending ADD CONSTRAINT FK_E44ECDDBB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E44ECDDBB3453DB ON spending (work_id)');
        $this->addSql('ALTER TABLE worker_shift ADD work_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE worker_shift ADD CONSTRAINT FK_B9AC9F91BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_B9AC9F91BB3453DB ON worker_shift (work_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE spending DROP CONSTRAINT FK_E44ECDDBB3453DB');
        $this->addSql('DROP INDEX UNIQ_E44ECDDBB3453DB');
        $this->addSql('ALTER TABLE spending DROP work_id');
        $this->addSql('ALTER TABLE worker_shift DROP CONSTRAINT FK_B9AC9F91BB3453DB');
        $this->addSql('DROP INDEX IDX_B9AC9F91BB3453DB');
        $this->addSql('ALTER TABLE worker_shift DROP work_id');
    }
}
