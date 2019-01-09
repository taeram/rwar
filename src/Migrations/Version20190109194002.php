<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190109194002 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_D592642C31DBE174');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wallpaper AS SELECT id, subreddit_id, hash FROM wallpaper');
        $this->addSql('DROP TABLE wallpaper');
        $this->addSql('CREATE TABLE wallpaper (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subreddit_id INTEGER DEFAULT NULL, hash VARCHAR(64) NOT NULL COLLATE BINARY, CONSTRAINT FK_D592642C31DBE174 FOREIGN KEY (subreddit_id) REFERENCES sub_reddit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO wallpaper (id, subreddit_id, hash) SELECT id, subreddit_id, hash FROM __temp__wallpaper');
        $this->addSql('DROP TABLE __temp__wallpaper');
        $this->addSql('CREATE INDEX IDX_D592642C31DBE174 ON wallpaper (subreddit_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX IDX_D592642C31DBE174');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wallpaper AS SELECT id, subreddit_id, hash FROM wallpaper');
        $this->addSql('DROP TABLE wallpaper');
        $this->addSql('CREATE TABLE wallpaper (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subreddit_id INTEGER DEFAULT NULL, hash VARCHAR(64) NOT NULL, url VARCHAR(2048) NOT NULL COLLATE BINARY, path VARCHAR(255) NOT NULL COLLATE BINARY)');
        $this->addSql('INSERT INTO wallpaper (id, subreddit_id, hash) SELECT id, subreddit_id, hash FROM __temp__wallpaper');
        $this->addSql('DROP TABLE __temp__wallpaper');
        $this->addSql('CREATE INDEX IDX_D592642C31DBE174 ON wallpaper (subreddit_id)');
    }
}
