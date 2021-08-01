<?php

global $CFG;

// Get real path for our folder
$rootPath = realpath(__DIR__ . '/../mod/customcert/files');

$idCurso = isset($_GET['idCurso']) ?? null;

// Initialize archive object
$zip = new ZipArchive();
$archive_file_name = 'file.zip';
$zip->open($archive_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($rootPath),
	RecursiveIteratorIterator::LEAVES_ONLY
);

function get_string_between($string, $start, $end){
	$string = ' ' . $string;
	$ini = strpos($string, $start);
	if ($ini == 0) return '';
	$ini += strlen($start);
	$len = strpos($string, $end, $ini) - $ini;
	return substr($string, $ini, $len);
}

$cont = 0;
$contfiles = 0;

foreach ($files as $name => $file)
{
	if(!empty($idCurso)) {
		// Skip directories (they would be added automatically)
		$idGet = get_string_between($name,
			$rootPath."/",
			'_-_');

		if($idGet != $idCurso || $idGet == false) {
			$cont++;
			continue;
		}
	}

	// Skip directories (they would be added automatically)
	if (!$file->isDir()) {
		$contfiles++;
		// Get real and relative path for current file
		$filePath = $file->getRealPath();
		$relativePath = substr($filePath, strlen($rootPath) + 1);

		// Add current file to archive
		$zip->addFile($filePath, $relativePath);
	}
}

if($contfiles == 0) {
	echo 'Este curso no tiene certificados';
	echo '<br><a href="/my">Regresar a la página anterior</a>';
	exit;
}

// Zip archive will be created only after closing object
$zip->close();

header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$archive_file_name");
header("Content-length: " . filesize($archive_file_name));
header("Pragma: no-cache");
header("Expires: 0");
readfile("$archive_file_name");