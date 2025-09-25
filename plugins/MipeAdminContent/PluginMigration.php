<?php
class PluginMigration
{
    public static function install()
    {
        $db = App()->db;

        // Create programs
        $db->createCommand("
            CREATE TABLE IF NOT EXISTS programs (
                id INT AUTO_INCREMENT PRIMARY KEY,
                group_id INT NULL,
                name VARCHAR(255) NOT NULL,
                startDate DATETIME NULL,
                endDate DATETIME NULL,
                description TEXT NULL,
                expectedResults TEXT NULL,
                notes TEXT NULL,
                budget VARCHAR(255) NULL,
                committedBudgetPercentage SMALLINT NULL,
                objective TEXT NULL,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                modified DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ")->execute();

        $db->createCommand("
            ALTER TABLE programs ADD CONSTRAINT fk_programs_survey_groups FOREIGN KEY (group_id) REFERENCES surveys_groups(gsid);
        ")->execute();

        // Create editais
        $db->createCommand("
            CREATE TABLE IF NOT EXISTS editais (
                id INT AUTO_INCREMENT PRIMARY KEY,
                group_id INT NULL,
                program_id INT NULL,
                name VARCHAR(255) NOT NULL,
                startDate DATETIME NULL,
                endDate DATETIME NULL,
                description TEXT NULL,
                expectedResults TEXT NULL,
                notes TEXT NULL,
                created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                modified DATETIME NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ")->execute();

        $db->createCommand("
            ALTER TABLE editais ADD CONSTRAINT fk_editais_programs_groups FOREIGN KEY (program_id) REFERENCES programs(id);

            ALTER TABLE editais ADD CONSTRAINT fk_editais_survey_groups FOREIGN KEY (group_id) REFERENCES surveys_groups(gsid);
        ")->execute();
    }

    public static function uninstall()
    {
        $db = App()->db;
        $db->createCommand("DROP TABLE IF EXISTS editais")->execute();
        $db->createCommand("DROP TABLE IF EXISTS programs")->execute();
    }
}