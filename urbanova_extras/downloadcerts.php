<?php

global $CFG;

// Get real path for our folder
$rootPath = realpath(__DIR__ . '/../mod/customcert/files');


$idCurso = isset($_GET['idCurso']) ?? null;

// Initialize archive object
// Initialize archive object
$zip = new ZipArchive();
$fileName = 'myZip.zip';
$zip->open($fileName, ZipArchive::CREATE | ZipArchive::OVERWRITE);

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

foreach ($files as $name => $file) {

	if(!empty($idCurso)) {
		// Skip directories (they would be added automatically)
		$idGet = get_string_between($name,
			$rootPath."/",
			'_-_');

		if($idGet != $idCurso) {
			$cont++;
			continue;
		}
	}

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

echo 'Archivo creado!';
//ob_end_clean();
//ob_end_flush();
//header('Content-disposition: attachment; filename=Certificados.zip');
//header('Content-type: application/zip');
//readfile($fileName);
//// remove zip file is exists in temp path
//unlink($fileName);

ob_end_clean();
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=". pathinfo($fileName , PATHINFO_BASENAME));
header("Content-Length: " . filesize($fileName ));
readfile($fileName);