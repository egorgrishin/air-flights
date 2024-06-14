<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240614082126 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE airports (
          code VARCHAR(15) NOT NULL,
          city_code VARCHAR(15) DEFAULT NULL,
          title VARCHAR(127) NOT NULL,
          PRIMARY KEY(code)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE airport_company (
          airport_code VARCHAR(15) NOT NULL,
          company_id INT NOT NULL,
          INDEX IDX_32753767EF395399 (airport_code),
          INDEX IDX_32753767979B1AD6 (company_id),
          PRIMARY KEY(airport_code, company_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE companies (
          id INT AUTO_INCREMENT NOT NULL,
          title VARCHAR(63) NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          airport_company
        ADD
          CONSTRAINT FK_32753767EF395399 FOREIGN KEY (airport_code) REFERENCES airports (code)');
        $this->addSql('ALTER TABLE
          airport_company
        ADD
          CONSTRAINT FK_32753767979B1AD6 FOREIGN KEY (company_id) REFERENCES companies (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE airport_company DROP FOREIGN KEY FK_32753767EF395399');
        $this->addSql('ALTER TABLE airport_company DROP FOREIGN KEY FK_32753767979B1AD6');
        $this->addSql('DROP TABLE airports');
        $this->addSql('DROP TABLE airport_company');
        $this->addSql('DROP TABLE companies');
    }
}
