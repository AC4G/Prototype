<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221117223220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX name ON item');
        $this->addSql('CREATE FULLTEXT INDEX project_name_fulltext ON project (project_name)');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX project_name_fulltext ON project');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type(255))');
        $this->addSql('CREATE FULLTEXT INDEX name ON item (name, parameter)');
    }
}
