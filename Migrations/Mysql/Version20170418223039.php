<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Set up initial table structure
 */
class Version20170418223039 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Set up initial table structure';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('CREATE TABLE cm_neos_thememodule_domain_model_settings (persistence_object_identifier VARCHAR(40) NOT NULL, customscss LONGTEXT DEFAULT NULL, customcss LONGTEXT DEFAULT NULL, customsettings LONGTEXT DEFAULT NULL, PRIMARY KEY(persistence_object_identifier)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('DROP TABLE cm_neos_thememodule_domain_model_settings');
    }
}
