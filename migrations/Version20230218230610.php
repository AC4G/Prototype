<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230218230610 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type(255))');
    }
}
