<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250416013709 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE spending_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE work_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE SEQUENCE worker_shift_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE spending (id INT NOT NULL, plantation_id INT NOT NULL, type INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, amount_in_cents INT NOT NULL, note VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E44ECDD19E5826C ON spending (plantation_id)');
        $this->addSql('COMMENT ON COLUMN spending.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE work (id INT NOT NULL, work_type_id INT NOT NULL, plantation_id INT NOT NULL, note VARCHAR(255) NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_534E6880108734B1 ON work (work_type_id)');
        $this->addSql('CREATE INDEX IDX_534E688019E5826C ON work (plantation_id)');
        $this->addSql('COMMENT ON COLUMN work.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE work_worker (work_entity_id INT NOT NULL, worker_entity_id INT NOT NULL, PRIMARY KEY(work_entity_id, worker_entity_id))');
        $this->addSql('CREATE INDEX IDX_344B7940E32DD508 ON work_worker (work_entity_id)');
        $this->addSql('CREATE INDEX IDX_344B7940325D04E ON work_worker (worker_entity_id)');
        $this->addSql('CREATE TABLE worker_shift (id INT NOT NULL, worker_id INT NOT NULL, plantation_id INT NOT NULL, date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, payment_in_cents INT NOT NULL, paid BOOLEAN NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_B9AC9F916B20BA36 ON worker_shift (worker_id)');
        $this->addSql('CREATE INDEX IDX_B9AC9F9119E5826C ON worker_shift (plantation_id)');
        $this->addSql('COMMENT ON COLUMN worker_shift.date IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE spending ADD CONSTRAINT FK_E44ECDD19E5826C FOREIGN KEY (plantation_id) REFERENCES plantations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work ADD CONSTRAINT FK_534E6880108734B1 FOREIGN KEY (work_type_id) REFERENCES work_types (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work ADD CONSTRAINT FK_534E688019E5826C FOREIGN KEY (plantation_id) REFERENCES plantations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_worker ADD CONSTRAINT FK_344B7940E32DD508 FOREIGN KEY (work_entity_id) REFERENCES work (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_worker ADD CONSTRAINT FK_344B7940325D04E FOREIGN KEY (worker_entity_id) REFERENCES workers (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE worker_shift ADD CONSTRAINT FK_B9AC9F916B20BA36 FOREIGN KEY (worker_id) REFERENCES workers (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE worker_shift ADD CONSTRAINT FK_B9AC9F9119E5826C FOREIGN KEY (plantation_id) REFERENCES plantations (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE spending_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE work_id_seq CASCADE');
        $this->addSql('DROP SEQUENCE worker_shift_id_seq CASCADE');
        $this->addSql('ALTER TABLE spending DROP CONSTRAINT FK_E44ECDD19E5826C');
        $this->addSql('ALTER TABLE work DROP CONSTRAINT FK_534E6880108734B1');
        $this->addSql('ALTER TABLE work DROP CONSTRAINT FK_534E688019E5826C');
        $this->addSql('ALTER TABLE work_worker DROP CONSTRAINT FK_344B7940E32DD508');
        $this->addSql('ALTER TABLE work_worker DROP CONSTRAINT FK_344B7940325D04E');
        $this->addSql('ALTER TABLE worker_shift DROP CONSTRAINT FK_B9AC9F916B20BA36');
        $this->addSql('ALTER TABLE worker_shift DROP CONSTRAINT FK_B9AC9F9119E5826C');
        $this->addSql('DROP TABLE spending');
        $this->addSql('DROP TABLE work');
        $this->addSql('DROP TABLE work_worker');
        $this->addSql('DROP TABLE worker_shift');
    }
}
