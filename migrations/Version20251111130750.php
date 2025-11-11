<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251111130750 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE jeu_participants (jeu_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_1A7A0E078C9E392E (jeu_id), INDEX IDX_1A7A0E07A76ED395 (user_id), PRIMARY KEY(jeu_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE jeu_participants ADD CONSTRAINT FK_1A7A0E078C9E392E FOREIGN KEY (jeu_id) REFERENCES jeu (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE jeu_participants ADD CONSTRAINT FK_1A7A0E07A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE jeu_participants DROP FOREIGN KEY FK_1A7A0E078C9E392E');
        $this->addSql('ALTER TABLE jeu_participants DROP FOREIGN KEY FK_1A7A0E07A76ED395');
        $this->addSql('DROP TABLE jeu_participants');
    }
}
