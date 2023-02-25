<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230225201519 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP INDEX project_name_fulltext, ADD UNIQUE INDEX project_name_UNIQUE (project_name)');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project DROP INDEX project_name_UNIQUE, ADD INDEX project_name_fulltext (project_name)');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type(255))');
    }
}
