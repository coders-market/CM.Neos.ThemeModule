<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add packageKey to Settings
 */
class Version20171019001547 extends AbstractMigration
{
    /**
     * @return string
     */
    public function getDescription()
    {
        return 'Add packageKey to Settings';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ADD packagekey VARCHAR(255) DEFAULT \'\' NOT NULL, DROP persistence_object_identifier');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ADD PRIMARY KEY (packagekey)');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings DROP PRIMARY KEY');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ADD persistence_object_identifier VARCHAR(40) NOT NULL COLLATE utf8_unicode_ci, DROP packagekey');
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ADD PRIMARY KEY (persistence_object_identifier)');
    }
}
