<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
<<<<<<<< HEAD:projet-s1-symfony/migrations/Version20260109154437.php
final class Version20260109154437 extends AbstractMigration
========
final class Version20260203103130 extends AbstractMigration
>>>>>>>> feature/tailwind-thomas:projet-s1-symfony/migrations/Version20260203103130.php
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE address (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, recipient_name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, address_details VARCHAR(255) NOT NULL, postal_code VARCHAR(255) NOT NULL, city VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE author (id SERIAL NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, biography TEXT DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE book (id SERIAL NOT NULL, work_id INT NOT NULL, book_publisher_id INT DEFAULT NULL, publication_date DATE NOT NULL, current_unit_price INT DEFAULT NULL, available_stock INT DEFAULT NULL, cover_image_url VARCHAR(255) DEFAULT NULL, weight_grams INT DEFAULT NULL, release_date DATE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CBE5A331BB3453DB ON book (work_id)');
        $this->addSql('CREATE INDEX IDX_CBE5A3311929A596 ON book (book_publisher_id)');
        $this->addSql('CREATE TABLE book_publisher (id SERIAL NOT NULL, publisher_name VARCHAR(255) NOT NULL, contact_email VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE "customer" (id SERIAL NOT NULL, address_id INT DEFAULT NULL, last_name VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password_hash VARCHAR(255) NOT NULL, phone VARCHAR(20) NOT NULL, registration_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, last_login TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_81398E09F5B7AF75 ON "customer" (address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON "customer" (email)');
        $this->addSql('CREATE TABLE genre (id SERIAL NOT NULL, label VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE monthly_box (id SERIAL NOT NULL, subscription_id INT DEFAULT NULL, reference_month VARCHAR(255) NOT NULL, creation_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, shipping_status VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_3BBB31B39A1887DC ON monthly_box (subscription_id)');
        $this->addSql('CREATE TABLE "order" (id SERIAL NOT NULL, address_id INT DEFAULT NULL, monthly_box_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, order_date TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, status VARCHAR(255) NOT NULL, total_amount INT NOT NULL, shipping_cost INT NOT NULL, tracking_number VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F5299398F5B7AF75 ON "order" (address_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52993983BB7E8F4 ON "order" (monthly_box_id)');
        $this->addSql('CREATE INDEX IDX_F52993989395C3F3 ON "order" (customer_id)');
        $this->addSql('CREATE TABLE order_line (id SERIAL NOT NULL, book_id INT DEFAULT NULL, table_order_id INT DEFAULT NULL, quantity INT NOT NULL, unit_price_snapshot INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_9CE58EE116A2B381 ON order_line (book_id)');
        $this->addSql('CREATE INDEX IDX_9CE58EE11D2243E8 ON order_line (table_order_id)');
        $this->addSql('CREATE TABLE subscription (id SERIAL NOT NULL, address_id INT DEFAULT NULL, subscription_offer_id INT DEFAULT NULL, customer_id INT DEFAULT NULL, start_date DATE NOT NULL, expected_end_date DATE NOT NULL, status VARCHAR(255) NOT NULL, next_payment_date DATE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_A3C664D3F5B7AF75 ON subscription (address_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D334682BC2 ON subscription (subscription_offer_id)');
        $this->addSql('CREATE INDEX IDX_A3C664D39395C3F3 ON subscription (customer_id)');
        $this->addSql('CREATE TABLE subscription_offer (id SERIAL NOT NULL, offer_name VARCHAR(255) NOT NULL, description TEXT NOT NULL, monthly_price INT NOT NULL, included_books_count INT NOT NULL, commitment_months INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE work (id SERIAL NOT NULL, title VARCHAR(255) NOT NULL, summary TEXT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE TABLE work_genre (work_id INT NOT NULL, genre_id INT NOT NULL, PRIMARY KEY(work_id, genre_id))');
        $this->addSql('CREATE INDEX IDX_ABAB48D7BB3453DB ON work_genre (work_id)');
        $this->addSql('CREATE INDEX IDX_ABAB48D74296D31F ON work_genre (genre_id)');
        $this->addSql('CREATE TABLE work_author (work_id INT NOT NULL, author_id INT NOT NULL, PRIMARY KEY(work_id, author_id))');
        $this->addSql('CREATE INDEX IDX_16561EEABB3453DB ON work_author (work_id)');
        $this->addSql('CREATE INDEX IDX_16561EEAF675F31B ON work_author (author_id)');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
<<<<<<<< HEAD:projet-s1-symfony/migrations/Version20260109154437.php
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
========
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
>>>>>>>> feature/tailwind-thomas:projet-s1-symfony/migrations/Version20260203103130.php
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
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A331BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE book ADD CONSTRAINT FK_CBE5A3311929A596 FOREIGN KEY (book_publisher_id) REFERENCES book_publisher (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "customer" ADD CONSTRAINT FK_81398E09F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE monthly_box ADD CONSTRAINT FK_3BBB31B39A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F5299398F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993983BB7E8F4 FOREIGN KEY (monthly_box_id) REFERENCES monthly_box (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE "order" ADD CONSTRAINT FK_F52993989395C3F3 FOREIGN KEY (customer_id) REFERENCES "customer" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE116A2B381 FOREIGN KEY (book_id) REFERENCES book (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE order_line ADD CONSTRAINT FK_9CE58EE11D2243E8 FOREIGN KEY (table_order_id) REFERENCES "order" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D3F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D334682BC2 FOREIGN KEY (subscription_offer_id) REFERENCES subscription_offer (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39395C3F3 FOREIGN KEY (customer_id) REFERENCES "customer" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_genre ADD CONSTRAINT FK_ABAB48D7BB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_genre ADD CONSTRAINT FK_ABAB48D74296D31F FOREIGN KEY (genre_id) REFERENCES genre (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_author ADD CONSTRAINT FK_16561EEABB3453DB FOREIGN KEY (work_id) REFERENCES work (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE work_author ADD CONSTRAINT FK_16561EEAF675F31B FOREIGN KEY (author_id) REFERENCES author (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE book DROP CONSTRAINT FK_CBE5A331BB3453DB');
        $this->addSql('ALTER TABLE book DROP CONSTRAINT FK_CBE5A3311929A596');
        $this->addSql('ALTER TABLE "customer" DROP CONSTRAINT FK_81398E09F5B7AF75');
        $this->addSql('ALTER TABLE monthly_box DROP CONSTRAINT FK_3BBB31B39A1887DC');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F5299398F5B7AF75');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993983BB7E8F4');
        $this->addSql('ALTER TABLE "order" DROP CONSTRAINT FK_F52993989395C3F3');
        $this->addSql('ALTER TABLE order_line DROP CONSTRAINT FK_9CE58EE116A2B381');
        $this->addSql('ALTER TABLE order_line DROP CONSTRAINT FK_9CE58EE11D2243E8');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D3F5B7AF75');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D334682BC2');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D39395C3F3');
        $this->addSql('ALTER TABLE work_genre DROP CONSTRAINT FK_ABAB48D7BB3453DB');
        $this->addSql('ALTER TABLE work_genre DROP CONSTRAINT FK_ABAB48D74296D31F');
        $this->addSql('ALTER TABLE work_author DROP CONSTRAINT FK_16561EEABB3453DB');
        $this->addSql('ALTER TABLE work_author DROP CONSTRAINT FK_16561EEAF675F31B');
        $this->addSql('DROP TABLE address');
        $this->addSql('DROP TABLE author');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE book_publisher');
        $this->addSql('DROP TABLE "customer"');
        $this->addSql('DROP TABLE genre');
        $this->addSql('DROP TABLE monthly_box');
        $this->addSql('DROP TABLE "order"');
        $this->addSql('DROP TABLE order_line');
        $this->addSql('DROP TABLE subscription');
        $this->addSql('DROP TABLE subscription_offer');
        $this->addSql('DROP TABLE work');
        $this->addSql('DROP TABLE work_genre');
        $this->addSql('DROP TABLE work_author');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
