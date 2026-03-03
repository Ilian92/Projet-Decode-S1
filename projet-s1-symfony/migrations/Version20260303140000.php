<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove status column from subscription; status is now computed from start_date and expected_end_date';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription DROP status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE subscription ADD status VARCHAR(255) NOT NULL DEFAULT \'active\'');
    }
}