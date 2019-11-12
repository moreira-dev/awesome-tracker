<?php

if (!class_exists('AwesomeTrackerExport')):
    class AwesomeTrackerExport {

        /**
         * Limit of records to retrieve from the DB in one go
         */
        const PERFORMANCE_LIMIT = 20000;

        static private $exporting = false;

        static private function echo_csv($filename, $output) {

            if (!self::$exporting) {
                header("Pragma: public");
                header("Expires: 0");
                header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
                header("Content-Type: application/force-download");
                header("Content-Type: application/octet-stream");
                header("Content-Type: application/download");
                header("Content-Disposition: attachment;filename=" . $filename . ".csv");

                self::$exporting = true;
            }


            echo mb_convert_encoding($output, 'UTF-16LE', 'UTF-8');

        }


        static public function csv_pageRecords_table() {
            global $wpdb;

            if (!current_user_can('manage_options'))
                return;

            $filename = sanitize_file_name('Awesome_Tracker_Records');


            $tableLog = new AwesomeTrackerLogTable();
            $tableLog->buildSql();


            $count = $wpdb->get_var($tableLog->sqlCount);

            $output = "Type of Record;Description;User;IP;Date" . PHP_EOL;

            for ($offset = 0; $offset < $count; $offset += self::PERFORMANCE_LIMIT) {
                $sql = $tableLog->sqlNoFilters
                        . $tableLog->sqlWhere
                        . " ORDER BY " . $tableLog->orderby . ' ' . $tableLog->orderway
                        . " LIMIT $offset, ".self::PERFORMANCE_LIMIT;

                $records = $wpdb->get_results($sql);

                if ($records)
                    foreach ($records as $record) {
                        $labelAndDescription = AT_Record::get_label_and_description($record);

                        $output .= $labelAndDescription['label'] . ";";
                        $output .= "\"" . $labelAndDescription['description'] . "\"" . ";";
                        $output .= $record->username . ";";
                        $output .= $record->ip . ";";
                        $output .= $record->visited;

                        $output .= PHP_EOL;
                    }

                self::echo_csv(date('Y_m_d') . '_' . $filename, $output);

                $output = "";
            }

            exit;
        }

    }
endif;
