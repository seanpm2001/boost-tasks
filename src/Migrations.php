<?php

class Migrations {
    static $versions = array(
        'GitHubEventQueue::migration_AddType',
        'Migrations::migration_DropVersionTable',
    );

    static function migrate() {
        while(R::transaction('Migrations::single_migration')) {}
    }

    static function single_migration() {
        $num_versions = count(self::$versions);
        $version = R::getCell('PRAGMA user_version');
        if ($version < $num_versions) {
            Log::info("Call migration {$version}: ".self::$versions[$version]);
            call_user_func(self::$versions[$version]);
            ++$version;
            Log::info("Migration success, now at version {$version}");
            R::exec("PRAGMA user_version = {$version}");
        }
        return $version < $num_versions;
    }

    static function newColumn($table, $column, $initial_value) {
        if (!array_key_exists($column, R::getColumns($table))) {
            $x = R::findOne($table);
            $x->{$column} = $initial_value;
            R::store($x);
        }
        R::exec("UPDATE {$table} SET {$column} = ? WHERE {$column} IS NULL",
            array($initial_value));
    }

    static function migration_DropVersionTable() {
        R::exec("DROP TABLE version");
    }
}
