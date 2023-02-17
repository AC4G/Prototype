<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230217150402 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE public_key (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, `key` LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, INDEX fk_Public_Key_User1_idx (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX key_UNIQUE (`key`), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE public_key ADD CONSTRAINT FK_66F9D463A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE public_key DROP FOREIGN KEY FK_66F9D463A76ED395');
        $this->addSql('DROP TABLE public_key');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type(255))');
    }
}
