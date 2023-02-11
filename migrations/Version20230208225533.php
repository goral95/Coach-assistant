<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230208225533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE training_unit (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, topic VARCHAR(255) NOT NULL, duration INT NOT NULL, date DATETIME NOT NULL, warm_part LONGTEXT NOT NULL, first_main_part LONGTEXT NOT NULL, second_main_part LONGTEXT DEFAULT NULL, end_part LONGTEXT NOT NULL, INDEX IDX_D3214B10A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE training_unit_player (training_unit_id INT NOT NULL, player_id INT NOT NULL, INDEX IDX_AB2BEB442583E003 (training_unit_id), INDEX IDX_AB2BEB4499E6F5DF (player_id), PRIMARY KEY(training_unit_id, player_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE training_unit ADD CONSTRAINT FK_D3214B10A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE training_unit_player ADD CONSTRAINT FK_AB2BEB442583E003 FOREIGN KEY (training_unit_id) REFERENCES training_unit (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE training_unit_player ADD CONSTRAINT FK_AB2BEB4499E6F5DF FOREIGN KEY (player_id) REFERENCES player (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE training_unit DROP FOREIGN KEY FK_D3214B10A76ED395');
        $this->addSql('ALTER TABLE training_unit_player DROP FOREIGN KEY FK_AB2BEB442583E003');
        $this->addSql('ALTER TABLE training_unit_player DROP FOREIGN KEY FK_AB2BEB4499E6F5DF');
        $this->addSql('DROP TABLE training_unit');
        $this->addSql('DROP TABLE training_unit_player');
    }
}
