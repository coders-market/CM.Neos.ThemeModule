<?php
namespace Neos\Flow\Persistence\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170412171204 extends AbstractMigration
{

    /**
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function up(Schema $schema)
    {

        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');

        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings ADD customfont VARCHAR(255) DEFAULT NULL, ADD graybase VARCHAR(255) DEFAULT NULL, ADD bgcolor VARCHAR(255) DEFAULT NULL, ADD primarycolor VARCHAR(255) DEFAULT NULL, ADD successcolor VARCHAR(255) DEFAULT NULL, ADD infocolor VARCHAR(255) DEFAULT NULL, ADD warningcolor VARCHAR(255) DEFAULT NULL, ADD dangercolor VARCHAR(255) DEFAULT NULL, ADD fontsizebase VARCHAR(255) DEFAULT NULL, ADD btndefaultbgcolor VARCHAR(255) DEFAULT NULL, ADD btndefaultborder VARCHAR(255) DEFAULT NULL, ADD btndefaultcolor VARCHAR(255) DEFAULT NULL, ADD btnprimarycolor VARCHAR(255) DEFAULT NULL, ADD btnsuccesscolor VARCHAR(255) DEFAULT NULL, ADD btninfocolor VARCHAR(255) DEFAULT NULL, ADD btnwarningcolor VARCHAR(255) DEFAULT NULL, ADD btndangercolor VARCHAR(255) DEFAULT NULL, CHANGE customscss customscss LONGTEXT DEFAULT NULL, CHANGE customcss customcss LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     * @return void
     */
    public function down(Schema $schema)
    {

        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on "mysql".');
        
        $this->addSql('ALTER TABLE cm_neos_thememodule_domain_model_settings DROP customfont, DROP graybase, DROP bgcolor, DROP primarycolor, DROP successcolor, DROP infocolor, DROP warningcolor, DROP dangercolor, DROP fontsizebase, DROP btndefaultbgcolor, DROP btndefaultborder, DROP btndefaultcolor, DROP btnprimarycolor, DROP btnsuccesscolor, DROP btninfocolor, DROP btnwarningcolor, DROP btndangercolor, CHANGE customscss customscss LONGTEXT NOT NULL COLLATE utf8_unicode_ci, CHANGE customcss customcss LONGTEXT NOT NULL COLLATE utf8_unicode_ci');

    }
}