<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230405150150 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE organisation_member (id INT AUTO_INCREMENT NOT NULL, organisation_id INT DEFAULT NULL, user_id INT DEFAULT NULL, join_date DATETIME NOT NULL, INDEX IDX_F2FF20659E6B1585 (organisation_id), INDEX IDX_F2FF2065A76ED395 (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX organisation_user_UNIQUE (organisation_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_team_member (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, user_id INT DEFAULT NULL, join_date DATETIME NOT NULL, INDEX IDX_CE304A5A166D1F9C (project_id), INDEX IDX_CE304A5AA76ED395 (user_id), UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX project_user_UNIQUE (project_id, user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE total_storage_usage (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, total_usage INT NOT NULL, UNIQUE INDEX id_UNIQUE (id), UNIQUE INDEX project_UNIQUE (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE organisation_member ADD CONSTRAINT FK_F2FF20659E6B1585 FOREIGN KEY (organisation_id) REFERENCES organisation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE organisation_member ADD CONSTRAINT FK_F2FF2065A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_team_member ADD CONSTRAINT FK_CE304A5A166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('ALTER TABLE project_team_member ADD CONSTRAINT FK_CE304A5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE total_storage_usage ADD CONSTRAINT FK_1E90BE97166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project ADD with_invitation TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE storage ADD length INT NOT NULL');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE organisation_member DROP FOREIGN KEY FK_F2FF20659E6B1585');
        $this->addSql('ALTER TABLE organisation_member DROP FOREIGN KEY FK_F2FF2065A76ED395');
        $this->addSql('ALTER TABLE project_team_member DROP FOREIGN KEY FK_CE304A5A166D1F9C');
        $this->addSql('ALTER TABLE project_team_member DROP FOREIGN KEY FK_CE304A5AA76ED395');
        $this->addSql('ALTER TABLE total_storage_usage DROP FOREIGN KEY FK_1E90BE97166D1F9C');
        $this->addSql('DROP TABLE organisation_member');
        $this->addSql('DROP TABLE project_team_member');
        $this->addSql('DROP TABLE total_storage_usage');
        $this->addSql('ALTER TABLE storage DROP length');
        $this->addSql('ALTER TABLE project DROP with_invitation');
        $this->addSql('DROP INDEX fk_User_Id_User_Token_Type ON user_token');
        $this->addSql('CREATE INDEX fk_User_Id_User_Token_Type ON user_token (user_id, token, token_type(255))');
    }
}
