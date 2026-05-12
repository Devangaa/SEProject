<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportWilayah extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:export-wilayah';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export data wilayah (provinces, cities, kecamatans) ke file SQL untuk backup seeder';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tables = ['provinces', 'cities', 'kecamatans'];
        $outputFile = database_path('seeders/sql/wilayah_indonesia.sql');

        if (! File::exists(database_path('seeders/sql'))) {
            File::makeDirectory(database_path('seeders/sql'), 0755, true);
        }

        $sql = "-- Backup Data Wilayah Indonesia\n";
        $sql .= '-- Generated: '.now()."\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            $this->info("Exporting table: $table...");
            $rows = DB::table($table)->get();

            $sql .= "TRUNCATE TABLE `$table`;\n";

            if ($rows->count() > 0) {
                $columns = array_keys((array) $rows->first());
                $columnList = implode('`, `', $columns);

                $sql .= "INSERT INTO `$table` (`$columnList`) VALUES \n";

                $values = [];
                foreach ($rows as $row) {
                    $rowValues = [];
                    foreach ($columns as $col) {
                        $val = $row->$col;
                        if (is_null($val)) {
                            $rowValues[] = 'NULL';
                        } elseif (is_numeric($val) && (string) (int) $val === (string) $val) {
                            $rowValues[] = $val;
                        } else {
                            $rowValues[] = "'".str_replace("'", "''", $val)."'";
                        }
                    }
                    $values[] = '('.implode(', ', $rowValues).')';

                    // Chunk inserts to avoid massive single statements
                    if (count($values) >= 500) {
                        $sql .= implode(",\n", $values).";\n";
                        $sql .= "INSERT INTO `$table` (`$columnList`) VALUES \n";
                        $values = [];
                    }
                }

                if (count($values) > 0) {
                    $sql .= implode(",\n", $values).";\n\n";
                } else {
                    // Fix trailing INSERT if the last chunk was exactly 500
                    $sql = rtrim($sql, "INSERT INTO `$table` (`$columnList`) VALUES \n")."\n";
                }
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        File::put($outputFile, $sql);

        $this->info("✓ Berhasil mengekspor data wilayah ke: $outputFile");
    }
}
