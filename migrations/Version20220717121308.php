<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220717121308 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EA76ED395');
        $this->addSql('ALTER TABLE item ADD project_id INT DEFAULT NULL, DROP game_name');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251E166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX fk_Item_Project1_idx ON item (project_id)');
        $this->addSql('DROP INDEX fk_item_user_idx ON item');
        $this->addSql('CREATE INDEX IDX_1F1B251EA76ED395 ON item (user_id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251E166D1F9C');
        $this->addSql('DROP INDEX fk_Item_Project1_idx ON item');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EA76ED395');
        $this->addSql('ALTER TABLE item ADD game_name VARCHAR(255) DEFAULT NULL, DROP project_id');
        $this->addSql('DROP INDEX idx_1f1b251ea76ed395 ON item');
        $this->addSql('CREATE INDEX fk_Item_User_idx ON item (user_id)');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type(255))');
    }
}
