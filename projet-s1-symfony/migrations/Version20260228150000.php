<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Change Work.id and Book.id to Open Library keys (string OLIDs).
 */
final class Version20260228150000 extends AbstractMigration
{
    public function isTransactional(): bool
    {
        return false;
    }

    public function getDescription(): string
    {
        return 'Work and Book primary keys become Open Library OLIDs (string)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE order_line DROP CONSTRAINT IF EXISTS FK_9CE58EE116A2B381');
        $this->addSql('ALTER TABLE book DROP CONSTRAINT IF EXISTS FK_CBE5A331BB3453DB');
        $this->addSql('ALTER TABLE work_genre DROP CONSTRAINT IF EXISTS FK_ABAB48D7BB3453DB');
        $this->addSql('ALTER TABLE work_author DROP CONSTRAINT IF EXISTS FK_16561EEABB3453DB');

        $this->addSql('ALTER TABLE work ADD COLUMN id_olid VARCHAR(255)');
        $this->addSql("UPDATE work SET id_olid = 'legacy_work_' || id::text");
        $this->addSql('ALTER TABLE work ALTER COLUMN id_olid SET NOT NULL');

        $this->addSql('ALTER TABLE work_genre ADD COLUMN work_id_olid VARCHAR(255)');
        $this->addSql('UPDATE work_genre wg SET work_id_olid = w.id_olid FROM work w WHERE w.id = wg.work_id');
        $this->addSql('ALTER TABLE work_genre DROP COLUMN work_id');
        $this->addSql('ALTER TABLE work_genre RENAME COLUMN work_id_olid TO work_id');

        $this->addSql('ALTER TABLE work_author ADD COLUMN work_id_olid VARCHAR(255)');
        $this->addSql('UPDATE work_author wa SET work_id_olid = w.id_olid FROM work w WHERE w.id = wa.work_id');
        $this->addSql('ALTER TABLE work_author DROP COLUMN work_id');
        $this->addSql('ALTER TABLE work_author RENAME COLUMN work_id_olid TO work_id');

        $this->addSql('ALTER TABLE book ADD COLUMN work_id_olid VARCHAR(255)');
        $this->addSql('UPDATE book b SET work_id_olid = w.id_olid FROM work w WHERE w.id = b.work_id');
        $this->addSql('ALTER TABLE book ADD COLUMN id_olid VARCHAR(255)');
        $this->addSql("UPDATE book SET id_olid = CASE WHEN open_library_edition_key IS NOT NULL AND open_library_edition_key != '' THEN SUBSTRING(open_library_edition_key FROM '[^/]+$') ELSE 'legacy_book_' || id::text END");
        $this->addSql('ALTER TABLE book ALTER COLUMN id_olid SET NOT NULL');

        $this->addSql('ALTER TABLE order_line ADD COLUMN book_id_olid VARCHAR(255)');
        $this->addSql('UPDATE order_line ol SET book_id_olid = b.id_olid FROM book b WHERE b.id = ol.book_id');
        $this->addSql('ALTER TABLE order_line DROP COLUMN book_id');
        $this->addSql('ALTER TABLE order_line RENAME COLUMN book_id_olid TO book_id');

        $this->addSql('ALTER TABLE work DROP CONSTRAINT work_pkey');
        $this->addSql('ALTER TABLE work DROP COLUMN id');
        $this->addSql('ALTER TABLE work RENAME COLUMN id_olid TO id');
        $this->addSql('ALTER TABLE work ADD PRIMARY KEY (id)');

        $this->addSql('ALTER TABLE book DROP COLUMN work_id');
        $this->addSql('ALTER TABLE book RENAME COLUMN work_id_olid TO work_id');
        $this->addSql('ALTER TABLE book DROP CONSTRAINT book_pkey');
        $this->addSql('ALTER TABLE book DROP COLUMN id');
        $this->addSql('ALTER TABLE book RENAME COLUMN id_olid TO id');
        $this->addSql('ALTER TABLE book ADD PRIMARY KEY (id)');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE116A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_genre ADD CONSTRAINT FK_ABAB48D7BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_author ADD CONSTRAINT FK_16561EEABB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('DROP INDEX IF EXISTS UNIQ_CBE5A33160207815');
        $this->addSql('ALTER TABLE book DROP COLUMN IF EXISTS open_library_edition_key');
    }

    public function down(Schema $schema): void
    {
        $this->throwIrreversibleMigrationException('Cannot revert Work/Book OLID primary keys to integer.');
    }
}
