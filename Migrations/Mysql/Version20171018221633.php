<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adjust schema to code cleanup
 */
class Version20171018221633 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Adjust schema to code cleanup';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings CHANGE customscss customscss LONGTEXT NOT NULL, CHANGE customcss customcss LONGTEXT NOT NULL, CHANGE customsettings customsettings LONGTEXT NOT NULL');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings CHANGE customscss customscss LONGTEXT DEFAULT NULL, CHANGE customcss customcss LONGTEXT DEFAULT NULL, CHANGE customsettings customsettings LONGTEXT DEFAULT NULL');
    }
}
