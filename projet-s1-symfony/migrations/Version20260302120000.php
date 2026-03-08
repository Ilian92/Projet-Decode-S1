<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260302120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove shipping_status from monthly_box (use Order status instead)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monthly_box DROP COLUMN IF EXISTS shipping_status');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE monthly_box ADD shipping_status VARCHAR(255)');
    }
}
