<?php

require 'vendor/autoload.php';
require 'helpers.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


if (!isset($argv)) { $argv = []; }
if (count($argv) < 2) {
	echo "Usage: php to-excel.php [files]\n";
	exit();
}

for ($a = 1; $a < count($argv); $a++) {
	$inputFile = $argv[$a];

	$fileInfo = pathinfo($inputFile);
	echo "Reading file: " . $inputFile . "\n";

	$lines = preg_split('/\n##~~Do not edit this line\.([0-9.]+)~~##\n/', file_get_contents($inputFile), -1, PREG_SPLIT_DELIM_CAPTURE);

	// Remove first comment
	array_shift($lines);


	$out = [];
	$x = 0;
	for ($i = 0; $i < count($lines); $i += 2) {
		$line = $lines[$i];
		$content = splitHTML($lines[$i + 1]);

		for ($j = 0; $j < count($content); $j++) {
			$c = $content[$j];
			$data = [
				'line' => $line,
				'internal' => $x++,
				'pre' => '',
				'post' => '',
				'master' => '',
				'translation' => '',
			];

			if (substr($c, 0, 1) == '<') {
				$data['pre'] = $c;
				$j++;
				if ($j >= count($content)) {
					break;
				}
				$c = $content[$j];
			}

			$data['master'] = html_entity_decode($c, ENT_COMPAT | ENT_HTML401, "UTF-8");

			if (count($content)>$j+1 && substr($content[$j+1], 0, 1) == '<') {
				$data['post'] = $content[$j+1];
				$j++;
			}
			$out[] = $data;
		}

	}

	// Write excel

	$spreadsheet = new Spreadsheet();
	$sheet = $spreadsheet->getActiveSheet();
	$line = 2;

	$sheet->setCellValue('A1', 'Line');
	$sheet->setCellValue('B1', 'Intern rad');
	$sheet->setCellValue('C1', 'Pre-html');
	$sheet->setCellValue('D1', 'Post-html');
	$sheet->setCellValue('E1', 'Master');
	$sheet->setCellValue('F1', 'Översättning');

	foreach ($out as $o) {
		$sheet->setCellValueExplicit('A' . $line, $o['line'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->setCellValueExplicit('B' . $line, $o['internal'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->setCellValueExplicit('C' . $line, $o['pre'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->setCellValueExplicit('D' . $line, $o['post'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->setCellValueExplicit('E' . $line, $o['master'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$sheet->setCellValueExplicit('F' . $line, $o['translation'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
		$line++;
	} 

	$sheet->setAutoFilter('A1:F' . ($line - 1));

	$writer = new Xlsx($spreadsheet);


	echo "Writing file: " . $fileInfo['dirname'] . "/" . $fileInfo['filename'] . ".xlsx\n";
	$writer->save($fileInfo['dirname'] . "/" . $fileInfo['filename'] . '.xlsx');

}

