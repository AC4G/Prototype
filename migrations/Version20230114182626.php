<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230114182626 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, token_type TINYTEXT NOT NULL, creation_date DATETIME DEFAULT NULL, expire_date DATETIME DEFAULT NULL, INDEX fk_User_Id_User_Token_Type (user_id, token, token_type), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX user_id_UNIQUE (user_id), UNIQUE INDEX token_UNIQUE (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_token ADD CONSTRAINT FK_BDF55A63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
        $this->addSql('DROP TABLE token');
        $this->addSql('ALTER TABLE access_token ADD creation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE auth_token ADD creation_date DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE refresh_token ADD creation_date DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, token_type TINYTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, expire_date DATETIME NOT NULL, UNIQUE INDEX token_UNIQUE (token), UNIQUE INDEX user_id_UNIQUE (user_id), INDEX fk_User_Id_Token_Type (user_id, token, token_type(255)), UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_token DROP FOREIGN KEY FK_BDF55A63A76ED395');
        $this->addSql('DROP TABLE user_token');
        $this->addSql('ALTER TABLE auth_token DROP creation_date');
        $this->addSql('ALTER TABLE refresh_token DROP creation_date');
        $this->addSql('ALTER TABLE access_token DROP creation_date');
    }
}
