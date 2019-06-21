<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190621183407 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX UNIQ_D592642CD1B862B8');
        $this->addSql('DROP INDEX IDX_D592642C31DBE174');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wallpaper AS SELECT id, subreddit_id, hash, rating, url FROM wallpaper');
        $this->addSql('DROP TABLE wallpaper');
        $this->addSql('CREATE TABLE wallpaper (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subreddit_id INTEGER DEFAULT NULL, hash VARCHAR(64) NOT NULL COLLATE BINARY, rating INTEGER NOT NULL, url VARCHAR(255) DEFAULT NULL COLLATE BINARY, title VARCHAR(255) DEFAULT NULL, CONSTRAINT FK_D592642C31DBE174 FOREIGN KEY (subreddit_id) REFERENCES sub_reddit (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO wallpaper (id, subreddit_id, hash, rating, url) SELECT id, subreddit_id, hash, rating, url FROM __temp__wallpaper');
        $this->addSql('DROP TABLE __temp__wallpaper');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D592642CD1B862B8 ON wallpaper (hash)');
        $this->addSql('CREATE INDEX IDX_D592642C31DBE174 ON wallpaper (subreddit_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'sqlite', 'Migration can only be executed safely on \'sqlite\'.');

        $this->addSql('DROP INDEX UNIQ_D592642CD1B862B8');
        $this->addSql('DROP INDEX IDX_D592642C31DBE174');
        $this->addSql('CREATE TEMPORARY TABLE __temp__wallpaper AS SELECT id, subreddit_id, hash, url, rating FROM wallpaper');
        $this->addSql('DROP TABLE wallpaper');
        $this->addSql('CREATE TABLE wallpaper (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, subreddit_id INTEGER DEFAULT NULL, hash VARCHAR(64) NOT NULL, url VARCHAR(255) DEFAULT NULL, rating INTEGER NOT NULL)');
        $this->addSql('INSERT INTO wallpaper (id, subreddit_id, hash, url, rating) SELECT id, subreddit_id, hash, url, rating FROM __temp__wallpaper');
        $this->addSql('DROP TABLE __temp__wallpaper');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_D592642CD1B862B8 ON wallpaper (hash)');
        $this->addSql('CREATE INDEX IDX_D592642C31DBE174 ON wallpaper (subreddit_id)');
    }
}
