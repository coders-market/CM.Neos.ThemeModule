<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adjust schema to code cleanup
 */
class Version20171017162444 extends AbstractMigration
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
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customscss DROP DEFAULT');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customscss SET NOT NULL');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customcss DROP DEFAULT');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customcss SET NOT NULL');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customsettings DROP DEFAULT');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customsettings SET NOT NULL');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'postgresql', 'Migration can only be executed safely on "postgresql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customscss SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customscss DROP NOT NULL');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customcss SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customcss DROP NOT NULL');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customsettings SET DEFAULT \'\'');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ALTER customsettings DROP NOT NULL');
    }
}
