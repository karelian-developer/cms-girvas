<?php

/**
 * CMS «ГИРВАС»
 * 
 * Cron: ротация отчётов (152-ФЗ)
 * 
 * Запуск (пример crontab):
 *   0 3 * * * /usr/bin/php /path/to/cms/cron/rotateReports.php >> /path/to/cms/logs/cron-rotateReports.log 2>&1
 */

require_once __DIR__ . '/bootstrap.php';

use \core\PHPLibrary\SystemCore\Reports\Rotator as ReportsRotator;
use \core\PHPLibrary\SystemCore\Report as CMSReport;

try {
  $CMSCore = cmsCronBootstrap();
} catch (\Exception $e) {
  fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] Bootstrap failed: ' . $e->getMessage() . PHP_EOL);
  exit(1);
}

// Проверка статуса ротации
$rotationStatus = 'off';

if ($CMSCore->configurator->existsDatabaseEntryValue('security_reports_rotation_status')) {
  $rotationStatus = $CMSCore->configurator->getDatabaseEntryValue('security_reports_rotation_status');
}

if ($rotationStatus !== 'on') {
  echo '[' . date('Y-m-d H:i:s') . '] Rotation is disabled (status = ' . $rotationStatus . '). Exit.' . PHP_EOL;
  exit(0);
}

// Срок хранения
$retentionDays = 365;

if ($CMSCore->configurator->existsDatabaseEntryValue('security_reports_retention_days')) {
  $value = (int)$CMSCore->configurator->getDatabaseEntryValue('security_reports_retention_days');

  if ($value > 0) {
    $retentionDays = $value;
  }
}

// Запуск
try {
  $result = ReportsRotator::rotate($CMSCore, $retentionDays, 1000);
} catch (\Exception $e) {
  fwrite(STDERR, '[' . date('Y-m-d H:i:s') . '] Rotation failed: ' . $e->getMessage() . PHP_EOL);
  exit(1);
}

$rotatedCount = $result['rotated'] ?? 0;

// Логирование в reports
CMSReport::create(
  $CMSCore,
  CMSReport::REPORT_TYPE_ID_AP_REPORTS_ROTATED,
  [
    'rotatedCount' => $rotatedCount,
    'threshold' => $result['threshold'] ?? 0,
    'days' => $retentionDays,
    'initiatedByID' => 0,
    'initiatedByLogin' => 'cron',
    'ip' => '0.0.0.0'
  ]
);

echo '[' . date('Y-m-d H:i:s') . '] Rotation completed. Archived: ' . $rotatedCount . ' records (older than ' . $retentionDays . ' days).' . PHP_EOL;

exit(0);