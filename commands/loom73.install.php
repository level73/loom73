<?php
namespace Loom73\Shuttle;
use Loom73\Woodframe\Config;
use Loom73\Woodframe\Errata;
use Loom73\Shuttle\CLI;
use PDO;

class Loom73Install  {

    protected PDO $pdo;

    protected array $paths = [
        ROOT_DIR . '/storage',
        ROOT_DIR . '/storage/uploads',
        ROOT_DIR . '/storage/logs',
        ROOT_DIR . '/storage/cache',
    ];

    public function __construct(?array $args) {


        $cli = new CLI();

        $confirm = $cli->confirm("Are you sure you want to install Loom73? This will delete and reinstall all user tables.");
        if(!$confirm):
            echo "Ok. ".PHP_EOL;
            if (ob_get_level() > 0) {
                ob_flush();
            }
            exit(1);
        endif;
        echo 'Installing the database...' . PHP_EOL;
        $DNS = $_SERVER['DBTYPE'] . ':dbname=' . $_SERVER['DBNAME'] . ';host=' . $_SERVER['DBHOST'] . ';port=' . $_SERVER['DBPORT'] . ';charset=utf8mb4';

        $this->pdo = new PDO(
            $DNS,
            $_SERVER['DBUSER'],
            $_SERVER['DBPASS']
        );

        try {

                echo "Disabling Key Checks... ";
                $this->pdo->exec('SET foreign_key_checks = 0;');
                echo $cli->cout_color(" OK", 'green').PHP_EOL;

                $sql = [];
                //Queries for basic tables
                $sql['drop_user_table'] = "DROP TABLE IF EXISTS `auth_user`";
                $sql['user'] = "CREATE TABLE `auth_user` (
                                  `idauth_user` bigint NOT NULL AUTO_INCREMENT,
                                  `username` varchar(255) NOT NULL,
                                  `email` varchar(255) NOT NULL,
                                  `password` varchar(255) NOT NULL,
                                  `salt` varchar(48) NOT NULL,
                                  `role` tinyint NOT NULL,
                                  `status` tinyint NOT NULL DEFAULT '1',
                                  `recovery` varchar(255) DEFAULT NULL,
                                  `recovery_created_at` datetime DEFAULT NULL,
                                  `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                  PRIMARY KEY (`idauth_user`),
                                  UNIQUE KEY `email_UNIQUE` (`email`),
                                  KEY `username` (`username`),
                                  KEY `fk_user_role_idx` (`role`),
                                  CONSTRAINT `fk_user_role` FOREIGN KEY (`role`) REFERENCES `auth_role` (`idauth_role`)
                                ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $sql['drop_roles_table'] = "DROP TABLE IF EXISTS `auth_role`";
                $sql['roles'] = "CREATE TABLE `auth_role` (
                                 `idauth_role` tinyint NOT NULL AUTO_INCREMENT,
                                  `role` varchar(45) NOT NULL,
                                  PRIMARY KEY (`idauth_role`)
                                 ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $sql['drop_session_table'] = "DROP TABLE IF EXISTS `auth_session`";
                $sql['sessions'] = "CREATE TABLE `auth_session` (
                                      `idauth_session` bigint NOT NULL AUTO_INCREMENT,
                                      `session` varchar(255) NOT NULL,
                                      `auth_user` bigint NOT NULL,
                                      `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                      `modified_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                      PRIMARY KEY (`idauth_session`),
                                      KEY `fk_session_user_idx` (`auth_user`),
                                      CONSTRAINT `fk_session_user` FOREIGN KEY (`auth_user`) REFERENCES `auth_user` (`idauth_user`) ON DELETE CASCADE
                                    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

                $sql['add_roles'] = "INSERT INTO `auth_role` VALUES (1,'admin'),(2,'editor'),(3,'user')";


                /*** Create Asset Tables **/

                /** - This holds the semantic file type (i.e. Avatar, Progress report, Contract, etc.) */
                $sql['drop_asset_types'] = "DROP TABLE IF EXISTS `asset_types`";
                $sql['add_asset_types'] = 'CREATE TABLE `asset_types` (
                                     `idasset_type` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                
                                    `slug` VARCHAR(100) NOT NULL,
                                    `label` VARCHAR(150) NOT NULL,
                                    `description` TEXT DEFAULT NULL,
                                
                                    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
                                
                                    `modified_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                                    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                
                                    PRIMARY KEY (`idasset_type`),
                                    UNIQUE KEY `slug_UNIQUE` (`slug`),
                                    KEY `is_active` (`is_active`)
                                ) ENGINE=InnoDB
                                  DEFAULT CHARSET=utf8mb4
                                  COLLATE=utf8mb4_unicode_ci';

                $sql['insert_asset_types'] = "INSERT INTO `asset_types` (
                                                `slug`,
                                                `label`,
                                                `description`,
                                                `is_active`
                                            ) VALUES (
                                                'image_avatar',
                                                'Avatar image',
                                                'Square image used as user profile avatar.',
                                                1
                                            )
                                            ON DUPLICATE KEY UPDATE
                                                `label` = VALUES(`label`),
                                                `description` = VALUES(`description`),
                                                `is_active` = VALUES(`is_active`)";

                /** - The Asset table - All data that refers to an individual resource uploaded to the application **/
                $sql['drop_asset'] = "DROP TABLE IF EXISTS `assets`";
                $sql['add_asset'] =  "CREATE TABLE `assets` (
                                        `idasset` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `uuid` CHAR(36) NOT NULL,
                                    
                                        /*
                                         * Owner = logical object this asset belongs to.
                                         * Example:
                                         *   owner_type = user
                                         *   owner_id = 12
                                         *   owner_slot = avatar
                                         *
                                         * owner_type is intentionally NOT a foreign key.
                                         * It is validated at application level through OwnerRegistry.
                                         */
                                        `owner_type` VARCHAR(100) DEFAULT NULL,
                                        `owner_id` VARCHAR(100) DEFAULT NULL,
                                        `owner_slot` VARCHAR(100) DEFAULT NULL,
                                    
                                        /*
                                         * Semantic asset type.
                                         * Example:
                                         *   image_avatar
                                         *   project_report
                                         *   informed_consent
                                         */
                                        `asset_type` INT UNSIGNED DEFAULT NULL,
                                    
                                        `original_name` VARCHAR(255) NOT NULL,
                                        `stored_name` VARCHAR(255) NOT NULL,
                                        `disk_path` VARCHAR(500) NOT NULL,
                                        `mime_type` VARCHAR(150) NOT NULL,
                                        `extension` VARCHAR(20) NOT NULL,
                                        `size_bytes` BIGINT UNSIGNED NOT NULL,
                                        `checksum_sha256` CHAR(64) DEFAULT NULL,
                                    
                                        `visibility` ENUM('private', 'restricted', 'public') NOT NULL DEFAULT 'private',
                                        `status` TINYINT NOT NULL DEFAULT 2,
                                    
                                        /*
                                         * User who uploaded the asset.
                                         * This is different from the owner.
                                         */
                                        `uploaded_by` BIGINT DEFAULT NULL,
                                    
                                        `modified_at` DATETIME DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                                        `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                                    
                                        PRIMARY KEY (`idasset`),
                                    
                                        UNIQUE KEY `uuid_UNIQUE` (`uuid`),
                                    
                                        KEY `assets_owner_slot_status` (`owner_type`, `owner_id`, `owner_slot`, `status`, `created_at`),
                                        KEY `asset_type` (`asset_type`),
                                        KEY `visibility` (`visibility`),
                                        KEY `uploaded_by` (`uploaded_by`),
                                        KEY `checksum_sha256` (`checksum_sha256`),
                                        KEY `created_at` (`created_at`),
                                    
                                        CONSTRAINT `fk_assets_asset_type`
                                            FOREIGN KEY (`asset_type`)
                                            REFERENCES `asset_types` (`idasset_type`)
                                            ON UPDATE CASCADE
                                            ON DELETE SET NULL,
                                    
                                        CONSTRAINT `fk_assets_uploaded_by`
                                            FOREIGN KEY (`uploaded_by`)
                                            REFERENCES `auth_user` (`idauth_user`)
                                            ON UPDATE CASCADE
                                            ON DELETE SET NULL
                                    ) ENGINE=InnoDB
                                      DEFAULT CHARSET=utf8mb4
                                      COLLATE=utf8mb4_unicode_ci";


                foreach($sql as $k => $sql_operation) :
                    echo "Running " . $cli->cout_color($k, 'yellow') . " operation on database..." . PHP_EOL;
                    $q = $this->pdo->prepare($sql_operation)->execute();
                    echo (!$q) ? $cli->cout_color("ERROR", 'red') . PHP_EOL : $cli->cout_color("Successful!", 'green').PHP_EOL;
                endforeach;

                echo "Enabling Key Checks... ";
                $this->pdo->exec('SET foreign_key_checks = 1;');
                echo $cli->cout_color(" OK", 'green').PHP_EOL;

        }
        catch (Errata $e) {
            echo $cli->cout_color($e->errorMessage(), 'red') . PHP_EOL;
        }


        echo "Creating storage directories...".PHP_EOL;
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                $dir = mkdir($path, 02775, true);
                if($dir): echo "Storage path "; echo $cli->cout_color($dir, 'yellow') . " created" . PHP_EOL;
                else: echo $cli->cout_color("Storage path {$path} not created", 'red') . PHP_EOL;
                endif;
            }

            chmod($path, 02775);
            if (!is_writable($path)) {
                echo $cli->cout_color("Warning: directory is not writable: {$path}\n", 'red') . PHP_EOL;
            }
        }

    }
}