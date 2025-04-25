<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250424164425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        //this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE spending_group_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE spending_group (id INT NOT NULL, work_id INT DEFAULT NULL, type INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, amount_in_cents INT NOT NULL, note VARCHAR(255) DEFAULT NULL, is_shared BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BA946F16BB3453DB ON spending_group (work_id)');
        $this->addSql('COMMENT ON COLUMN spending_group.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE spending_group ADD CONSTRAINT FK_BA946F16BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE spending DROP CONSTRAINT fk_e44ecddbb3453db');
        $this->addSql('DROP INDEX uniq_e44ecddbb3453db');
        $this->addSql('ALTER TABLE spending DROP work_id');
        $this->addSql('ALTER TABLE spending DROP date');
        $this->addSql('ALTER TABLE spending DROP note');
        $this->addSql('ALTER TABLE spending RENAME COLUMN type TO spending_group_id');
        $this->addSql('ALTER TABLE spending ADD CONSTRAINT FK_E44ECDD6BED3A7D FOREIGN KEY (spending_group_id) REFERENCES spending_group (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_E44ECDD6BED3A7D ON spending (spending_group_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE spending DROP CONSTRAINT FK_E44ECDD6BED3A7D');
        $this->addSql('DROP SEQUENCE spending_group_id_seq CASCADE');
        $this->addSql('ALTER TABLE spending_group DROP CONSTRAINT FK_BA946F16BB3453DB');
        $this->addSql('DROP TABLE spending_group');
        $this->addSql('DROP INDEX IDX_E44ECDD6BED3A7D');
        $this->addSql('ALTER TABLE spending ADD work_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE spending ADD date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE spending ADD note VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE spending RENAME COLUMN spending_group_id TO type');
        $this->addSql('COMMENT ON COLUMN spending.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE spending ADD CONSTRAINT fk_e44ecddbb3453db FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX uniq_e44ecddbb3453db ON spending (work_id)');
    }
}
