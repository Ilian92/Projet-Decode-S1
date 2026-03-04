<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260303150000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add stripe_payment_intent_id to order for refunds';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" ADD stripe_payment_intent_id VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "order" DROP stripe_payment_intent_id');
    }
}
