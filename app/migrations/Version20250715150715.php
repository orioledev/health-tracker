<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250715150715 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE foods (id BIGSERIAL NOT NULL, external_id VARCHAR(64) NOT NULL, name VARCHAR(255) NOT NULL, calories SMALLINT NOT NULL, proteins NUMERIC(6, 2) NOT NULL, fats NUMERIC(6, 2) NOT NULL, carbohydrates NUMERIC(6, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX ux__foods__external_id ON foods (external_id)');
        $this->addSql('CREATE UNIQUE INDEX ux__foods__name ON foods (name)');

        $this->addSql('CREATE TABLE meals (id BIGSERIAL NOT NULL, user_id BIGINT NOT NULL, food_id BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, name VARCHAR(255) NOT NULL, weight SMALLINT NOT NULL, calories SMALLINT NOT NULL, proteins NUMERIC(6, 2) NOT NULL, fats NUMERIC(6, 2) NOT NULL, carbohydrates NUMERIC(6, 2) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_E229E6EAA76ED395 ON meals (user_id)');
        $this->addSql('CREATE INDEX IDX_E229E6EABA8E87C4 ON meals (food_id)');
        $this->addSql('CREATE INDEX ix__meals__calories ON meals (calories)');
        $this->addSql('CREATE INDEX ix__meals__proteins ON meals (proteins)');
        $this->addSql('CREATE INDEX ix__meals__fats ON meals (fats)');
        $this->addSql('CREATE INDEX ix__meals__carbohydrates ON meals (carbohydrates)');
        $this->addSql('CREATE INDEX ix__meals__created_at ON meals (created_at)');
        $this->addSql('COMMENT ON COLUMN meals.created_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('CREATE TABLE user_daily_norms (user_id BIGINT NOT NULL, calories SMALLINT NOT NULL, proteins NUMERIC(6, 2) NOT NULL, fats NUMERIC(6, 2) NOT NULL, carbohydrates NUMERIC(6, 2) NOT NULL, steps INT NOT NULL, PRIMARY KEY(user_id))');

        $this->addSql('CREATE TABLE user_indicators (user_id BIGINT NOT NULL, height SMALLINT DEFAULT NULL, initial_weight NUMERIC(5, 2) DEFAULT NULL, target_weight NUMERIC(5, 2) DEFAULT NULL, activity_level SMALLINT DEFAULT NULL, weight_target_type SMALLINT DEFAULT NULL, PRIMARY KEY(user_id))');

        $this->addSql('CREATE TABLE users (id BIGSERIAL NOT NULL, telegram_user_id BIGINT NOT NULL, telegram_username VARCHAR(255) DEFAULT NULL, gender SMALLINT DEFAULT NULL, birthdate DATE DEFAULT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX ux__users__telegram_user_id ON users (telegram_user_id)');
        $this->addSql('COMMENT ON COLUMN users.birthdate IS \'(DC2Type:date_immutable)\'');

        $this->addSql('CREATE TABLE walks (id BIGSERIAL NOT NULL, user_id BIGINT NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, steps INT NOT NULL, calories SMALLINT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_82FB0AA76ED395 ON walks (user_id)');
        $this->addSql('CREATE INDEX ix__walks__steps ON walks (steps)');
        $this->addSql('CREATE INDEX ix__walks__calories ON walks (calories)');
        $this->addSql('CREATE INDEX ix__walks__created_at ON walks (created_at)');
        $this->addSql('COMMENT ON COLUMN walks.created_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('CREATE TABLE weight_measurements (id BIGSERIAL NOT NULL, user_id BIGINT NOT NULL, weight NUMERIC(5, 2) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_67EB72B4A76ED395 ON weight_measurements (user_id)');
        $this->addSql('CREATE INDEX ix__weight_measurements__weight ON weight_measurements (weight)');
        $this->addSql('CREATE INDEX ix__weight_measurements__created_at ON weight_measurements (created_at)');
        $this->addSql('COMMENT ON COLUMN weight_measurements.created_at IS \'(DC2Type:datetime_immutable)\'');

        $this->addSql('ALTER TABLE meals ADD CONSTRAINT FK_E229E6EAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE meals ADD CONSTRAINT FK_E229E6EABA8E87C4 FOREIGN KEY (food_id) REFERENCES foods (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_daily_norms ADD CONSTRAINT FK_E475F877A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_indicators ADD CONSTRAINT FK_EED0DF4CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE walks ADD CONSTRAINT FK_82FB0AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE weight_measurements ADD CONSTRAINT FK_67EB72B4A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE meals DROP CONSTRAINT FK_E229E6EAA76ED395');
        $this->addSql('ALTER TABLE meals DROP CONSTRAINT FK_E229E6EABA8E87C4');
        $this->addSql('ALTER TABLE user_daily_norms DROP CONSTRAINT FK_E475F877A76ED395');
        $this->addSql('ALTER TABLE user_indicators DROP CONSTRAINT FK_EED0DF4CA76ED395');
        $this->addSql('ALTER TABLE walks DROP CONSTRAINT FK_82FB0AA76ED395');
        $this->addSql('ALTER TABLE weight_measurements DROP CONSTRAINT FK_67EB72B4A76ED395');
        $this->addSql('DROP TABLE foods');
        $this->addSql('DROP TABLE meals');
        $this->addSql('DROP TABLE user_daily_norms');
        $this->addSql('DROP TABLE user_indicators');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE walks');
        $this->addSql('DROP TABLE weight_measurements');
    }
}
