<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260303123227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uniq_cbe5a3314d1b94bc');
        $this->addSql('ALTER TABLE book DROP edition_olid');
        $this->addSql('ALTER TABLE book ALTER work_id SET NOT NULL');
        $this->addSql('ALTER TABLE subscription ADD stripe_subscription_id VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE work_genre ALTER work_id SET NOT NULL');
        $this->addSql('ALTER TABLE work_genre ADD PRIMARY KEY (work_id, genre_id)');
        $this->addSql('ALTER TABLE work_author ALTER work_id SET NOT NULL');
        $this->addSql('ALTER TABLE work_author ADD PRIMARY KEY (work_id, author_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE subscription DROP stripe_subscription_id');
        $this->addSql('ALTER TABLE work_author DROP CONSTRAINT work_author_pkey');
        $this->addSql('ALTER TABLE work_author ALTER work_id DROP NOT NULL');
        $this->addSql('ALTER TABLE work_genre DROP CONSTRAINT work_genre_pkey');
        $this->addSql('ALTER TABLE work_genre ALTER work_id DROP NOT NULL');
        $this->addSql('ALTER TABLE book ADD edition_olid VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE book ALTER work_id DROP NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uniq_cbe5a3314d1b94bc ON book (edition_olid)');
    }
}
