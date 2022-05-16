<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220516153405 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36A76ED395');
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36126F525E');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36126F525E FOREIGN KEY (item_id) REFERENCES item (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EA76ED395');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36A76ED395');
        $this->addSql('ALTER TABLE inventory DROP FOREIGN KEY FK_B12D4A36126F525E');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE inventory ADD CONSTRAINT FK_B12D4A36126F525E FOREIGN KEY (item_id) REFERENCES item (id)');
        $this->addSql('ALTER TABLE item DROP FOREIGN KEY FK_1F1B251EA76ED395');
        $this->addSql('ALTER TABLE item ADD CONSTRAINT FK_1F1B251EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX fk_User_Id_Token_Type ON token');
        $this->addSql('CREATE INDEX fk_User_Id_Token_Type ON token (user_id, token, token_type(255))');
    }
}
