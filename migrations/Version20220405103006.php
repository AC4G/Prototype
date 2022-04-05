<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220405103006 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE access_token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, access_token VARCHAR(255) NOT NULL, expire_date DATETIME NOT NULL, INDEX fk_Access_Token_Project1_idx (project_id), INDEX fk_Access_Token_User1_idx (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX access_token_UNIQUE (access_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE auth_token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, auth_token VARCHAR(255) NOT NULL, expire_date DATETIME NOT NULL, INDEX fk_Token_Project1_idx (project_id), INDEX fk_Token_User1_idx (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX auth_token_UNIQUE (auth_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_message (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, room_id INT DEFAULT NULL, message LONGTEXT DEFAULT NULL, data_path LONGTEXT DEFAULT NULL, send_date DATETIME NOT NULL, INDEX fk_chat_message_chat_room1_idx (room_id), INDEX fk_chat_message_user1_idx (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_room (id INT AUTO_INCREMENT NOT NULL, image_path LONGTEXT DEFAULT NULL,  settings LONGTEXT DEFAULT NULL, UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_room_member (id INT AUTO_INCREMENT NOT NULL, room_type_id INT DEFAULT NULL, chat_room_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX fk_chat_room_member_chat_room_type1_idx (room_type_id), INDEX fk_chat_room_user1_idx (user_id), INDEX fk_chat_room_member_chat_room1_idx (chat_room_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX room_id_user_id_UNIQUE (user_id, chat_room_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE chat_room_type (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE developer (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, second_name VARCHAR(255) NOT NULL, UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX User_id_UNIQUE (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE inventory (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, item_id INT DEFAULT NULL, amount INT NOT NULL, parameter LONGTEXT DEFAULT NULL, INDEX fk_User_has_Item_Item1_idx (item_id), INDEX fk_User_has_Item_User1_idx (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX user_id_item_id_UNIQUE (user_id, item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE item (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, game_name VARCHAR(255) DEFAULT NULL, parameter LONGTEXT NOT NULL, path LONGTEXT NOT NULL, creation_date DATETIME NOT NULL, INDEX fk_Item_User_idx (user_id), UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE oauth_client (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, client_id VARCHAR(255) NOT NULL, client_secret VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, INDEX fk_OAuth_Client_Project1_idx (project_id), UNIQUE INDEX client_secret_UNIQUE (client_secret), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX client_id_UNIQUE (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project (id INT AUTO_INCREMENT NOT NULL, developer_id INT DEFAULT NULL, project_name VARCHAR(255) NOT NULL, organisation_name VARCHAR(255) NOT NULL, organisation_email VARCHAR(255) NOT NULL, organisation_logo LONGTEXT DEFAULT NULL, support_email VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, INDEX fk_Project_Developer1_idx (developer_id), UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE refresh_token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, project_id INT DEFAULT NULL, refresh_token VARCHAR(255) NOT NULL, expire_date DATETIME NOT NULL, INDEX fk_Refresh_Token_Project1_idx (project_id), INDEX fk_Refresh_Token_User1_idx (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX refresh_token_UNIQUE (refresh_token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE role_ident (id INT AUTO_INCREMENT NOT NULL, role_name VARCHAR(255) NOT NULL, UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE scope (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, scope LONGTEXT NOT NULL, INDEX fk_Scope_Project1_idx (project_id), UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE token (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, token VARCHAR(255) NOT NULL, token_type TINYTEXT NOT NULL, expire_date DATETIME NOT NULL, INDEX fk_User_Id_Token_Type (user_id, token, token_type), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX user_id_UNIQUE (user_id), UNIQUE INDEX token_UNIQUE (token), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, nickname VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, password LONGTEXT NOT NULL, is_private TINYINT(1) NOT NULL, email_verified DATETIME DEFAULT NULL, creation_date DATETIME NOT NULL, UNIQUE INDEX email_UNIQUE (email), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX nickname_UNIQUE (nickname), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_public_key (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, public_key VARCHAR(45) DEFAULT NULL, UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX user_id_UNIQUE (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_roles (id INT AUTO_INCREMENT NOT NULL, role_ident_id INT DEFAULT NULL, user_id INT DEFAULT NULL, INDEX IDX_54FCD59FA76ED395 (user_id), INDEX fk_user_roles_role_ident1_idx (role_ident_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX user_id_role_ident_Id_UNIQUE (user_id, role_ident_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE web_app (id INT AUTO_INCREMENT NOT NULL, oauth_client_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, redirect_url LONGTEXT DEFAULT NULL, INDEX fk_Web_App_OAuth_Client1_idx (oauth_client_id), UNIQUE INDEX id_UNIQUE (id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD68A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE access_token ADD CONSTRAINT FK_B6A2DD68166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE auth_token ADD CONSTRAINT FK_9315F04E166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC16A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_message ADD CONSTRAINT FK_FAB3FC1654177093 FOREIGN KEY (room_id) REFERENCES chat_room (id)');
        $this->addSql('ALTER TABLE chat_room_member ADD CONSTRAINT FK_ED8CB21296E3073 FOREIGN KEY (room_type_id) REFERENCES chat_room_type (id)');
        $this->addSql('ALTER TABLE chat_room_member ADD CONSTRAINT FK_ED8CB211819BCFA FOREIGN KEY (chat_room_id) REFERENCES chat_room (id)');
        $this->addSql('ALTER TABLE chat_room_member ADD CONSTRAINT FK_ED8CB21A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE developer ADD CONSTRAINT FK_65FB8B9AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE oauth_client ADD CONSTRAINT FK_AD73274D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project ADD CONSTRAINT FK_2FB3D0EE64DD9267 FOREIGN KEY (developer_id) REFERENCES developer (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE refresh_token ADD CONSTRAINT FK_C74F2195166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE scope ADD CONSTRAINT FK_AF55D3166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE token ADD CONSTRAINT FK_5F37A13BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_public_key ADD CONSTRAINT FK_C19E128FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FFCCA9612 FOREIGN KEY (role_ident_id) REFERENCES role_ident (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_roles ADD CONSTRAINT FK_54FCD59FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE web_app ADD CONSTRAINT FK_AA293D36DCA49ED FOREIGN KEY (oauth_client_id) REFERENCES oauth_client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC1654177093');
        $this->addSql('ALTER TABLE chat_room_member DROP FOREIGN KEY FK_ED8CB211819BCFA');
        $this->addSql('ALTER TABLE chat_room_member DROP FOREIGN KEY FK_ED8CB21296E3073');
        $this->addSql('ALTER TABLE project DROP FOREIGN KEY FK_2FB3D0EE64DD9267');
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36126F525E');
        $this->addSql('ALTER TABLE web_app DROP FOREIGN KEY FK_AA293D36DCA49ED');
        $this->addSql('ALTER TABLE access_token DROP FOREIGN KEY FK_B6A2DD68166D1F9C');
        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY FK_9315F04E166D1F9C');
        $this->addSql('ALTER TABLE oauth_client DROP FOREIGN KEY FK_AD73274D166D1F9C');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195166D1F9C');
        $this->addSql('ALTER TABLE scope DROP FOREIGN KEY FK_AF55D3166D1F9C');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FFCCA9612');
        $this->addSql('ALTER TABLE access_token DROP FOREIGN KEY FK_B6A2DD68A76ED395');
        $this->addSql('ALTER TABLE auth_token DROP FOREIGN KEY FK_9315F04EA76ED395');
        $this->addSql('ALTER TABLE chat_message DROP FOREIGN KEY FK_FAB3FC16A76ED395');
        $this->addSql('ALTER TABLE chat_room_member DROP FOREIGN KEY FK_ED8CB21A76ED395');
        $this->addSql('ALTER TABLE developer DROP FOREIGN KEY FK_65FB8B9AA76ED395');
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36A76ED395');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EA76ED395');
        $this->addSql('ALTER TABLE refresh_token DROP FOREIGN KEY FK_C74F2195A76ED395');
        $this->addSql('ALTER TABLE token DROP FOREIGN KEY FK_5F37A13BA76ED395');
        $this->addSql('ALTER TABLE user_public_key DROP FOREIGN KEY FK_C19E128FA76ED395');
        $this->addSql('ALTER TABLE user_roles DROP FOREIGN KEY FK_54FCD59FA76ED395');
        $this->addSql('DROP TABLE access_token');
        $this->addSql('DROP TABLE auth_token');
        $this->addSql('DROP TABLE chat_message');
        $this->addSql('DROP TABLE chat_room');
        $this->addSql('DROP TABLE chat_room_member');
        $this->addSql('DROP TABLE chat_room_type');
        $this->addSql('DROP TABLE developer');
        $this->addSql('DROP TABLE inventory');
        $this->addSql('DROP TABLE item');
        $this->addSql('DROP TABLE oauth_client');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE refresh_token');
        $this->addSql('DROP TABLE role_ident');
        $this->addSql('DROP TABLE scope');
        $this->addSql('DROP TABLE token');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_public_key');
        $this->addSql('DROP TABLE user_roles');
        $this->addSql('DROP TABLE web_app');
    }
}
