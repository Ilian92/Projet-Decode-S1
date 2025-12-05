<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251205111111 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE author (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, photo_image_url VARCHAR(255) DEFAULT NULL, bio TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE author_work (author_id INT NOT NULL, work_id INT NOT NULL, PRIMARY KEY(author_id, work_id))');
        $this->addSql('CREATE INDEX IDX_B7A1E05FF675F31B ON author_work (author_id)');
        $this->addSql('CREATE INDEX IDX_B7A1E05FBB3453DB ON author_work (work_id)');
        $this->addSql('CREATE TABLE book (isbn BIGINT NOT NULL, work_id INT NOT NULL, publisher_id INT DEFAULT NULL, publication_date DATE DEFAULT NULL, price_per_unit NUMERIC(10, 2) NOT NULL, amount_in_stock INT NOT NULL, cover_image_url VARCHAR(255) DEFAULT NULL, weight NUMERIC(10, 2) NOT NULL, PRIMARY KEY(isbn))');
        $this->addSql('CREATE INDEX IDX_CBE5A331BB3453DB ON book (work_id)');
        $this->addSql('CREATE INDEX IDX_CBE5A33140C86FCE ON book (publisher_id)');
        $this->addSql('CREATE TABLE customer (id SERIAL NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, password_hashed VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, phone VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN customer.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE "order" (id SERIAL NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('COMMENT ON COLUMN "order".created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE TABLE order_book (order_id INT NOT NULL, book_isbn BIGINT NOT NULL, PRIMARY KEY(order_id, book_isbn))');
        $this->addSql('CREATE INDEX IDX_861499268D9F6D38 ON order_book (order_id)');
        $this->addSql('CREATE INDEX IDX_86149926D581BFEE ON order_book (book_isbn)');
        $this->addSql('CREATE TABLE publisher (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE subscription (id SERIAL NOT NULL, owner_id INT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_A3C664D37E3C61F9 ON subscription (owner_id)');
        $this->addSql('COMMENT ON COLUMN subscription.start_date IS \'(DC2Type:date_immutable)\'');
        $this->addSql('CREATE TABLE work (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');
        $this->addSql('ALTER TABLE author_work ADD CONSTRAINT FK_B7A1E05FF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE author_work ADD CONSTRAINT FK_B7A1E05FBB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A33140C86FCE FOREIGN KEY (publisher_id) REFERENCES publisher (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_book ADD CONSTRAINT FK_861499268D9F6D38 FOREIGN KEY (order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_book ADD CONSTRAINT FK_86149926D581BFEE FOREIGN KEY (book_isbn) REFERENCES book (isbn) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D37E3C61F9 FOREIGN KEY (owner_id) REFERENCES customer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE author_work DROP CONSTRAINT FK_B7A1E05FF675F31B');
        $this->addSql('ALTER TABLE author_work DROP CONSTRAINT FK_B7A1E05FBB3453DB');
        $this->addSql('ALTER TABLE book DROP CONSTRAINT FK_CBE5A331BB3453DB');
        $this->addSql('ALTER TABLE book DROP CONSTRAINT FK_CBE5A33140C86FCE');
        $this->addSql('ALTER TABLE order_book DROP CONSTRAINT FK_861499268D9F6D38');
        $this->addSql('ALTER TABLE order_book DROP CONSTRAINT FK_86149926D581BFEE');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D37E3C61F9');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE author_work');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE customer');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_book');
        $this->addSql('DROP TABLE publisher');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE work');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
