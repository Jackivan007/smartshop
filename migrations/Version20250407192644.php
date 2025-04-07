<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250407192644 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE categoria (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE grupo (id INT AUTO_INCREMENT NOT NULL, creado_por_id INT DEFAULT NULL, nombre VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_8C0E9BD3FE35D8C4 (creado_por_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE lista (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, grupo_id INT NOT NULL, nombre VARCHAR(100) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_FB9FEEEDDB38439E (usuario_id), INDEX IDX_FB9FEEED9C833003 (grupo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE notificacion (id INT AUTO_INCREMENT NOT NULL, usuario_id INT NOT NULL, mensaje VARCHAR(255) NOT NULL, leida TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_729A19ECDB38439E (usuario_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE producto (id INT AUTO_INCREMENT NOT NULL, lista_id INT NOT NULL, categoria_id INT NOT NULL, nombre VARCHAR(100) NOT NULL, cantidad INT NOT NULL, comprado TINYINT(1) NOT NULL, nota LONGTEXT DEFAULT NULL, INDEX IDX_A7BB06156736D68F (lista_id), INDEX IDX_A7BB06153397707A (categoria_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE usuario (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, apellidos VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, username VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE grupo ADD CONSTRAINT FK_8C0E9BD3FE35D8C4 FOREIGN KEY (creado_por_id) REFERENCES usuario (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lista ADD CONSTRAINT FK_FB9FEEEDDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lista ADD CONSTRAINT FK_FB9FEEED9C833003 FOREIGN KEY (grupo_id) REFERENCES grupo (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notificacion ADD CONSTRAINT FK_729A19ECDB38439E FOREIGN KEY (usuario_id) REFERENCES usuario (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE producto ADD CONSTRAINT FK_A7BB06156736D68F FOREIGN KEY (lista_id) REFERENCES lista (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE producto ADD CONSTRAINT FK_A7BB06153397707A FOREIGN KEY (categoria_id) REFERENCES categoria (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE grupo DROP FOREIGN KEY FK_8C0E9BD3FE35D8C4
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lista DROP FOREIGN KEY FK_FB9FEEEDDB38439E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE lista DROP FOREIGN KEY FK_FB9FEEED9C833003
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE notificacion DROP FOREIGN KEY FK_729A19ECDB38439E
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06156736D68F
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE producto DROP FOREIGN KEY FK_A7BB06153397707A
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE categoria
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE grupo
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE lista
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE notificacion
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE producto
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE usuario
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
