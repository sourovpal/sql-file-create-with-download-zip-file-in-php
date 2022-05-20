<?php
function exportDatabase()
{
        $host = "localhost";
        $username = "root";
        $password = "";
        $database_name = "test";
		// Get connection object and set the charset
		$conn = mysqli_connect($host, $username, $password, $database_name);
		$conn->set_charset("utf8");


		// Get All Table Names From the Database
		$tables = array();
		$sql = "SHOW TABLES";
		$result = mysqli_query($conn, $sql);

		while ($row = mysqli_fetch_row($result)) {
			$tables[] = $row[0];
		}

		$sqlScript = "SET foreign_key_checks = 0;";

		foreach ($tables as $table) {
			// Prepare SQLscript for creating table structure
			$query = "SHOW CREATE TABLE $table";
			$result = mysqli_query($conn, $query);
			$row = mysqli_fetch_row($result);

			$sqlScript .= "\n\n" . $row[1] . ";\n\n";


			$query = "SELECT * FROM $table";
			$result = mysqli_query($conn, $query);

			$columnCount = mysqli_num_fields($result);

			// Prepare SQLscript for dumping data for each table
			for ($i = 0; $i < $columnCount; $i ++) {
				while ($row = mysqli_fetch_row($result)) {
					$sqlScript .= "INSERT INTO $table VALUES(";
					for ($j = 0; $j < $columnCount; $j ++) {
						if (isset($row[$j])) {
							$sqlScript .= "'" . addslashes($row[$j]) . "'";
						} else {
							$sqlScript .= "''";
						}
						if ($j < ($columnCount - 1)) {
							$sqlScript .= ',';
						}
					}
					$sqlScript .= ");\n";
				}
			}

			$sqlScript .= "\n";
		}
        $sqlScript .= "SET foreign_key_checks = 1;";

		if(!empty($sqlScript))
		{
			// Save the SQL script to a backup file
			$backup_file_name = $database_name . '_backup_' . time();
            $zip_file_name = $backup_file_name . '.zip';
            touch($zip_file_name);
            $sql_file_name = $backup_file_name . '.sql';
			$fileHandler = fopen($sql_file_name, 'w+');
			$number_of_lines = fwrite($fileHandler, $sqlScript);
			fclose($fileHandler);
            // zip file create
			$zip = new ZipArchive();
			$zip->open($zip_file_name, ZipArchive::CREATE);
			$zip->addFile($sql_file_name, $sql_file_name);
			$zip->close();

			// Download the SQL backup file to the browser
			// header('Content-Description: File Transfer');
			// header('Content-Type: application/octet-stream');
			// header('Content-Disposition: attachment; filename=' . basename($backup_file_name));
			// header('Content-Transfer-Encoding: binary');
			// header('Expires: 0');
			// header('Cache-Control: must-revalidate');
			// header('Pragma: public');
			// header('Content-Length: ' . filesize($backup_file_name));
			// ob_clean();
			// flush();
			// readfile($backup_file_name);
			// exec('rm ' . $backup_file_name);
            
            
            //zip file download
            
            header("Content-type: application/zip"); 
            header("Content-Disposition: attachment; filename=$zip_file_name"); 
            header("Pragma: no-cache"); 
            header("Expires: 0"); 
            ob_clean();
			flush();
            readfile("$zip_file_name");
            if(file_exists($zip_file_name) && file_exists($sql_file_name)){
                unlink($zip_file_name);
                unlink($sql_file_name);
            }
		}
	}

    exportDatabase();