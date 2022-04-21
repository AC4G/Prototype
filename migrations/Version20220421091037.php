<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220421091037 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_room ADD room_type_id INT DEFAULT NULL, CHANGE settings  settings LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_room ADD CONSTRAINT FK_D403CCDA296E3073 FOREIGN KEY (room_type_id) REFERENCES chat_room_type (id)');
        $this->addSql('CREATE INDEX fk_chat_room_member_chat_room_type1_idx ON chat_room (room_type_id)');
        $this->addSql('ALTER TABLE chat_room_member DROP FOREIGN KEY FK_ED8CB21296E3073');
        $this->addSql('DROP INDEX fk_chat_room_member_chat_room_type1_idx ON chat_room_member');
        $this->addSql('ALTER TABLE chat_room_member DROP room_type_id');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_room DROP FOREIGN KEY FK_D403CCDA296E3073');
        $this->addSql('DROP INDEX fk_chat_room_member_chat_room_type1_idx ON chat_room');
        $this->addSql('ALTER TABLE chat_room DROP room_type_id, CHANGE  settings settings LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_room_member ADD room_type_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE chat_room_member ADD CONSTRAINT FK_ED8CB21296E3073 FOREIGN KEY (room_type_id) REFERENCES chat_room_type (id)');
        $this->addSql('CREATE INDEX fk_chat_room_member_chat_room_type1_idx ON chat_room_member (room_type_id)');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type(255))');
    }
}
