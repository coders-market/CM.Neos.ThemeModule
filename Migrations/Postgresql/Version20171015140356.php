<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Initial DB structure
 */
class Version20171015140356 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Initial DB structure';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('CREATE TABLE cm_neos_thememodule_domain_model_settings (persistence_object_identifier VARCHAR(40) NOT NULL, customscss TEXT DEFAULT \'\', customcss TEXT DEFAULT \'\', customsettings TEXT DEFAULT \'\', PRIMARY KEY(persistence_object_identifier))');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('DROP TABLE cm_neos_thememodule_domain_model_settings');
    }
}
