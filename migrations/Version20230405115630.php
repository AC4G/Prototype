<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405115630 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organisation (id INT AUTO_INCREMENT NOT NULL, organisation_name VARCHAR(255) NOT NULL, organisation_email VARCHAR(255) NOT NULL, organisation_logo LONGTEXT DEFAULT NULL, support_email VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX organisation_name_UNIQUE (organisation_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE project ADD organisation_id INT DEFAULT NULL, DROP organisation_name, DROP organisation_email, DROP organisation_logo, DROP support_email');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE9E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_2FB3D0EE9E6B1585 ON project (organisation_id)');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE9E6B1585');
        $this->addSql('DROP TABLE organisation');
        $this->addSql('DROP INDEX IDX_2FB3D0EE9E6B1585 ON project');
        $this->addSql('ALTER TABLE project ADD organisation_name VARCHAR(255) NOT NULL, ADD organisation_email VARCHAR(255) NOT NULL, ADD organisation_logo LONGTEXT DEFAULT NULL, ADD support_email VARCHAR(255) NOT NULL, DROP organisation_id');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type(255))');
    }
}
