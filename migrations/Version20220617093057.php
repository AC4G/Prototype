<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220617093057 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE web_app DROP FOREIGN KEY FK_AA293D36DCA49ED');
        $this->addSql('CREATE TABLE client (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, client_id VARCHAR(255) NOT NULL, client_secret VARCHAR(255) NOT NULL, creation_date DATETIME NOT NULL, INDEX fk_OAuth_Client_Project1_idx (project_id), UNIQUE INDEX client_secret_UNIQUE (client_secret), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX client_id_UNIQUE (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE client ADD CONSTRAINT FK_C7440455166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('DROP TABLE oauth_client');
        $this->addSql('ALTER TABLE access_token ADD scopes LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\'');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type)');
        $this->addSql('ALTER TABLE web_app DROP FOREIGN KEY FK_AA293D36DCA49ED');
        $this->addSql('ALTER TABLE web_app ADD CONSTRAINT FK_AA293D36DCA49ED FOREIGN KEY (oauth_client_id) REFERENCES client (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE web_app DROP FOREIGN KEY FK_AA293D36DCA49ED');
        $this->addSql('CREATE TABLE oauth_client (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, client_id VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, client_secret VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, creation_date DATETIME NOT NULL, INDEX fk_OAuth_Client_Project1_idx (project_id), UNIQUE INDEX client_secret_UNIQUE (client_secret), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX client_id_UNIQUE (client_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE oauth_client ADD CONSTRAINT FK_AD73274D166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('DROP TABLE client');
        $this->addSql('ALTER TABLE access_token DROP scopes');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type(255))');
        $this->addSql('ALTER TABLE web_app DROP FOREIGN KEY FK_AA293D36DCA49ED');
        $this->addSql('ALTER TABLE web_app ADD CONSTRAINT FK_AA293D36DCA49ED FOREIGN KEY (oauth_client_id) REFERENCES oauth_client (id)');
    }
}
