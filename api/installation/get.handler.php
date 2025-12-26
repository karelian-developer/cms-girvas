<?php


/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @link        https://cms-girvas.ru Сайт продукта
 * 
 * @copyright   Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Entry as Entry;
use \core\PHPLibrary\EntryCategory as EntryCategory;
use \core\PHPLibrary\EntriesSample as EntriesSample;
use \core\PHPLibrary\SystemCore\Database\Connector as CMSDatabaseConnector;
use \core\PHPLibrary\SystemCore\Configurator as CMSConfigurator;
use \core\PHPLibrary\UserGroup as UserGroup;
use \DOMDocument as DOMDocument;
use \PDOException as PDOException;

if (!defined('IS_NOT_HACKED')) {
  http_response_code(503);
  die('An attempted hacker attack has been detected.');
}

if (!file_exists(CMS_ROOT_DIRECTORY . '/INSTALLED')) {
  $JSONDataTypeDMS = 'json';

  $installationStepIndex = $CMSCore->urlp->getParam('stepIndex');
  $installationStepIndex = $installationStepIndex ?? 1;
  $installationStepIndex = is_numeric($installationStepIndex) ? (int) $installationStepIndex : 1;

  if ($installationStepIndex === 1) {
    $document = new DOMDocument();
    
    $phpExtensionsLoaded = get_loaded_extensions();
    
    $tableData = [
      [$CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_VERSION_LABEL'), '>= 8.2.6', phpversion()],
      [sprintf('%s SimpleXML', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('SimpleXML', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s PDO', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('PDO', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s openssl', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('openssl', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s curl', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('curl', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s dom', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('dom', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s mbstring', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('mbstring', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s json', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('json', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s zip', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('zip', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      [sprintf('%s intl', $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_PHP_MODULE_LABEL')), (in_array('intl', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))]
    ];

    $tableCellsFontColor = [
      '',
      (in_array('SimpleXML', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('PDO', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('openssl', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('curl', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('dom', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('mbstring', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('json', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('zip', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('intl', $phpExtensionsLoaded) ? '#209A20' : '#9A2020')
    ];

    $tableElement = $document->createElement('table');
    $tableElement->setAttribute('class', 'table');
    
    $tableRowHeaderElement = $document->createElement('tr');

    $tableCellsHeadersElements = [];
    $tableCellsHeadersElements[] = $document->createElement('th');
    $tableCellsHeadersElements[] = $document->createElement('th');
    $tableCellsHeadersElements[] = $document->createElement('th');

    foreach ($tableCellsHeadersElements as $element) {
      $element->setAttribute('class', 'table__cell table__cell_header');
      $element->setAttribute('style', 'font-weight: 700;');
    }

    $tableCellsHeadersElements[0]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_NAME_LABEL');
    $tableCellsHeadersElements[1]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_EXPECTATION_LABEL');
    $tableCellsHeadersElements[2]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_FACT_LABEL');

    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[0]);
    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[1]);
    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[2]);

    $tableElement->appendChild($tableRowHeaderElement);

    foreach ($tableData as $dataIndex => $dataArray) {
      $tableRowElement = $document->createElement('tr');

      $tableCellElements = [];
      $tableCellElements[] = $document->createElement('td');
      $tableCellElements[] = $document->createElement('td');
      $tableCellElements[] = $document->createElement('td');

      foreach ($tableCellElements as $index => $element) {
        $element->setAttribute('class', 'table__cell');

        if ($index == 0) {
          $element->setAttribute('style', 'font-weight: 700;');
        }

        if ($index == 1 && $dataIndex > 0) {
          $element->setAttribute('colspan', 2);
        }

        if ($index == 1 && !empty($tableCellsFontColor[$dataIndex])) {
          $element->setAttribute('style', sprintf('color: %s;', $tableCellsFontColor[$dataIndex]));
        }
      }

      foreach ($dataArray as $arrayValueIndex => $arrayValue) {
        $tableCellElements[$arrayValueIndex]->nodeValue = $dataArray[$arrayValueIndex];
        $tableRowElement->appendChild($tableCellElements[$arrayValueIndex]);
      }

      $tableElement->appendChild($tableRowElement);
    }
    
    $document->appendChild($tableElement);

    $handlerOutputData['html'] = $document->saveHTML();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }

  if ($installationStepIndex === 2) {
    $document = new DOMDocument();
    
    $tableData = [
      ['./backups/', (file_exists(CMS_ROOT_DIRECTORY . '/backups')) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_FOUND_LABEL') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./modules/', (file_exists(CMS_ROOT_DIRECTORY . '/modules')) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_FOUND_LABEL') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./templates/', (file_exists(CMS_ROOT_DIRECTORY . '/templates')) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_FOUND_LABEL') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./uploads/', (file_exists(CMS_ROOT_DIRECTORY . '/uploads')) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_FOUND_LABEL') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
    ];

    $tableCellsFontColor = [
      (file_exists(CMS_ROOT_DIRECTORY . '/backups') ? '#209A20' : '#9A2020'),
      (file_exists(CMS_ROOT_DIRECTORY . '/modules') ? '#209A20' : '#9A2020'),
      (file_exists(CMS_ROOT_DIRECTORY . '/templates') ? '#209A20' : '#9A2020'),
      (file_exists(CMS_ROOT_DIRECTORY . '/uploads') ? '#209A20' : '#9A2020'),
    ];

    $tableElement = $document->createElement('table');
    $tableElement->setAttribute('class', 'table');
    
    $tableRowHeaderElement = $document->createElement('tr');

    $tableCellsHeadersElements = [];
    $tableCellsHeadersElements[] = $document->createElement('th');
    $tableCellsHeadersElements[] = $document->createElement('th');

    foreach ($tableCellsHeadersElements as $tableCellElement) {
      $tableCellElement->setAttribute('class', 'table__cell table__cell_header');
    }

    $tableCellsHeadersElements[0]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_NAME_LABEL');
    $tableCellsHeadersElements[1]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_FACT_LABEL');

    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[0]);
    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[1]);

    $tableElement->appendChild($tableRowHeaderElement);

    foreach ($tableData as $dataIndex => $dataArray) {
      $tableRowElement = $document->createElement('tr');

      $tableCellElements = [];
      $tableCellElements[] = $document->createElement('td');
      $tableCellElements[] = $document->createElement('td');

      foreach ($tableCellElements as $index => $element) {
        $element->setAttribute('class', 'table__cell');

        if ($index === 1 && !empty($tableCellsFontColor[$dataIndex])) {
          $element->setAttribute('style', sprintf('color: %s;', $tableCellsFontColor[$dataIndex]));
        }
      }

      foreach ($dataArray as $arrayValueIndex => $arrayValue) {
        $tableCellElements[$arrayValueIndex]->nodeValue = $dataArray[$arrayValueIndex];
        $tableRowElement->appendChild($tableCellElements[$arrayValueIndex]);
      }

      $tableElement->appendChild($tableRowElement);
    }
    
    $document->appendChild($tableElement);

    $handlerOutputData['html'] = $document->saveHTML();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }

  if ($installationStepIndex === 3) {
    $document = new DOMDocument();

    $tableData = [
      ['./core/', '755', file_exists(CMS_ROOT_DIRECTORY . '/core') ? substr(sprintf('%o', fileperms(CMS_ROOT_DIRECTORY . '/core')), -3) : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./backups/', '755', file_exists(CMS_ROOT_DIRECTORY . '/backups') ? substr(sprintf('%o', fileperms(CMS_ROOT_DIRECTORY . '/backups')), -3) : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./modules/', '755', file_exists(CMS_ROOT_DIRECTORY . '/modules') ? substr(sprintf('%o', fileperms(CMS_ROOT_DIRECTORY . '/modules')), -3) : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./templates/', '755', file_exists(CMS_ROOT_DIRECTORY . '/templates') ? substr(sprintf('%o', fileperms(CMS_ROOT_DIRECTORY . '/templates')), -3) : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
      ['./uploads/', '755', file_exists(CMS_ROOT_DIRECTORY . '/uploads') ? substr(sprintf('%o', fileperms(CMS_ROOT_DIRECTORY . '/uploads')), -3) : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DIRECTORY_NOT_FOUND_LABEL')],
    ];

    $tableCellsFontColor = [
      !file_exists(CMS_ROOT_DIRECTORY . '/core') ? '#9A2020' : (decoct(fileperms(CMS_ROOT_DIRECTORY . '/core') & 0777) >= 755 ? '#209A20' : '#9A2020'),
      !file_exists(CMS_ROOT_DIRECTORY . '/backups') ? '#9A2020' : (decoct(fileperms(CMS_ROOT_DIRECTORY . '/backups') & 0777) >= 755 ? '#209A20' : '#9A2020'),
      !file_exists(CMS_ROOT_DIRECTORY . '/modules') ? '#9A2020' : (decoct(fileperms(CMS_ROOT_DIRECTORY . '/modules') & 0777) >= 755 ? '#209A20' : '#9A2020'),
      !file_exists(CMS_ROOT_DIRECTORY . '/templates') ? '#9A2020' : (decoct(fileperms(CMS_ROOT_DIRECTORY . '/templates') & 0777) >= 755 ? '#209A20' : '#9A2020'),
      !file_exists(CMS_ROOT_DIRECTORY . '/uploads') ? '#9A2020' : (decoct(fileperms(CMS_ROOT_DIRECTORY . '/uploads') & 0777) >= 755 ? '#209A20' : '#9A2020'),
    ];

    $tableElement = $document->createElement('table');
    $tableElement->setAttribute('class', 'table');
    
    $tableRowHeaderElement = $document->createElement('tr');

    $tableCellsHeadersElements = [];
    $tableCellsHeadersElements[] = $document->createElement('th');
    $tableCellsHeadersElements[] = $document->createElement('th');
    $tableCellsHeadersElements[] = $document->createElement('th');

    foreach ($tableCellsHeadersElements as $element) {
      $element->setAttribute('class', 'table__cell table__cell_header');
    }

    $tableCellsHeadersElements[0]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_NAME_LABEL');
    $tableCellsHeadersElements[1]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_EXPECTATION_LABEL');
    $tableCellsHeadersElements[2]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_FACT_LABEL');

    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[0]);
    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[1]);
    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[2]);

    $tableElement->appendChild($tableRowHeaderElement);

    foreach ($tableData as $dataIndex => $dataArray) {
      $tableRowElement = $document->createElement('tr');

      $tableCellElements = [];
      $tableCellElements[] = $document->createElement('td');
      $tableCellElements[] = $document->createElement('td');
      $tableCellElements[] = $document->createElement('td');

      foreach ($tableCellElements as $index => $element) {
        $element->setAttribute('class', 'table__cell');

        if ($index === 2 && !empty($tableCellsFontColor[$dataIndex])) {
          $element->setAttribute('style', sprintf('color: %s;', $tableCellsFontColor[$dataIndex]));
        }
      }

      foreach ($dataArray as $arrayValueIndex => $arrayValue) {
        $tableCellElements[$arrayValueIndex]->nodeValue = $dataArray[$arrayValueIndex];
        $tableRowElement->appendChild($tableCellElements[$arrayValueIndex]);
      }

      $tableElement->appendChild($tableRowElement);
    }
    
    $document->appendChild($tableElement);

    $handlerOutputData['html'] = $document->saveHTML();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }

  if ($installationStepIndex === 4) {
    $document = new DOMDocument();
    
    $phpExtensionsLoaded = get_loaded_extensions();

    $tableData = [
      ['Cubrid', (in_array('pdo_cubrid', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['FreeTDS / Microsoft SQL Server / Sybase', (in_array('pdo_dblib', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['Firebird', (in_array('pdo_firebird', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['IBM DB2', (in_array('pdo_ibm', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['IBM Informix Dynamic Server', (in_array('pdo_informix', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['MySQL', (in_array('pdo_mysql', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['Oracle Call Interface', (in_array('pdo_ocl', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['ODBC v3 (IBM DB2, unixODBC, win32 ODBC)', (in_array('pdo_odbc', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['PostgreSQL', (in_array('pdo_pgsql', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['SQLite 3 и SQLite 2', (in_array('pdo_sqlite', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))],
      ['Microsoft SQL Server / SQL Azure', (in_array('pdo_sqlsrv', $phpExtensionsLoaded) ? $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_ENABLED') : $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DISABLED'))]
    ];

    $tableCellsFontColor = [
      (in_array('pdo_cubrid', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_dblib', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_firebird', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_ibm', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_informix', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_mysql', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_ocl', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_odbc', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_pgsql', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_sqlite', $phpExtensionsLoaded) ? '#209A20' : '#9A2020'),
      (in_array('pdo_sqlsrv', $phpExtensionsLoaded) ? '#209A20' : '#9A2020')
    ];

    $tableElement = $document->createElement('table');
    $tableElement->setAttribute('class', 'table');
    
    $tableRowHeaderElement = $document->createElement('tr');

    $tableCellsHeadersElements = [];
    $tableCellsHeadersElements[] = $document->createElement('th');
    $tableCellsHeadersElements[] = $document->createElement('th');

    foreach ($tableCellsHeadersElements as $element) {
      $element->setAttribute('class', 'table__cell table__cell_header');
    }

    $tableCellsHeadersElements[0]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DRIVER_PDO_LABEL');
    $tableCellsHeadersElements[1]->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_DRIVER_PDO_STATUS_LABEL');

    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[0]);
    $tableRowHeaderElement->appendChild($tableCellsHeadersElements[1]);

    $tableElement->appendChild($tableRowHeaderElement);

    foreach ($tableData as $dataIndex => $dataArray) {
      $tableRowElement = $document->createElement('tr');

      $tableCellElements = [];
      $tableCellElements[] = $document->createElement('td');
      $tableCellElements[] = $document->createElement('td');

      foreach ($tableCellElements as $index => $element) {
        $element->setAttribute('class', 'table__cell');

        if ($index === 1 && !empty($tableCellsFontColor[$dataIndex])) {
          $element->setAttribute('style', sprintf('color: %s; border-color: inherit;', $tableCellsFontColor[$dataIndex]));
        }
      }

      foreach ($dataArray as $arrayValueIndex => $arrayValue) {
        $tableCellElements[$arrayValueIndex]->nodeValue = $dataArray[$arrayValueIndex];
        $tableRowElement->appendChild($tableCellElements[$arrayValueIndex]);
      }

      $tableElement->appendChild($tableRowElement);
    }
    
    $document->appendChild($tableElement);

    $handlerOutputData['html'] = $document->saveHTML();

    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }

  if ($installationStepIndex === 5) {
    $document = new DOMDocument();
    $tipBlockElement = $document->createElement('div');

    $fileCMSConfigurationPath = CMS_ROOT_DIRECTORY . '/' . CMSConfigurator::FILE_PATH;
    if (file_exists($fileCMSConfigurationPath)) {
      unlink($fileCMSConfigurationPath);
    }

    if (!file_exists($fileCMSConfigurationPath)) {
      $domain = isset($_GET['domain']) ? idn_to_ascii(trim($_GET['domain'])) : '';

      $domainAliases = $_GET['domain_aliases'] ?? '';
      $domainAliases = trim($domainAliases);

      $domainAliasesExploaded = explode(',', $domainAliases);
      
      if (count($domainAliasesExploaded) > 0) {
        foreach ($domainAliasesExploaded as $index => $domainAliase) {
          $domainAliase = idn_to_ascii(trim($domainAliase));
          $domainAliasesExploaded[$index] = '\'' . $domainAliase . '\'';
        }
      }

      $domainAliases = implode(', ', $domainAliasesExploaded);
      
      $domainEmail = idn_to_ascii($_GET['domain_email'] ?? '') ?? '';
      $domainСookies = idn_to_ascii($_GET['domain_cookies'] ?? '') ?? '';
      $domainSSLStatus = isset($_GET['domain_ssl_status']) ? 'true' : 'false';
      $databaseDMSName = (isset($_GET['database_dms'])) ? addslashes($_GET['database_dms']) : '';
      $databasePrefix = (isset($_GET['database_prefix'])) ? addslashes($_GET['database_prefix']) : '';
      $databaseScheme = (isset($_GET['database_scheme'])) ? addslashes($_GET['database_scheme']) : '';
      $databaseHost = (isset($_GET['database_host'])) ? addslashes($_GET['database_host']) : '';
      $databaseUser = (isset($_GET['database_user'])) ? addslashes($_GET['database_user']) : '';
      $databasePassword = (isset($_GET['database_dms'])) ? addslashes($_GET['database_pass']) : '';
      $databaseName = (isset($_GET['database_name'])) ? addslashes($_GET['database_name']) : '';
      
      $CMSSalt = bin2hex(openssl_random_pseudo_bytes(10));

      $file = fopen($fileCMSConfigurationPath, 'w+');
      fwrite($file, '<?php' . PHP_EOL);
      fwrite($file, PHP_EOL);
      fwrite($file, 'use \core\PHPLibrary\Database\DatabaseManagementSystem as DMS;' . PHP_EOL);
      fwrite($file, PHP_EOL);
      fwrite($file, '$configuration = [' . PHP_EOL);
      fwrite($file, sprintf('  \'domain\' => \'%s\',', $domain) . PHP_EOL);
      fwrite($file, sprintf('  \'domainAliases\' => [%s],', $domainAliases) . PHP_EOL);
      fwrite($file, sprintf('  \'domainEmail\' => \'%s\',', $domainEmail) . PHP_EOL);
      fwrite($file, sprintf('  \'domainCookies\' => \'%s\',', $domainСookies) . PHP_EOL);
      fwrite($file, sprintf('  \'SSLIsEnabled\' => %s,', $domainSSLStatus) . PHP_EOL);
      fwrite($file, '  \'database\' => [' . PHP_EOL);
      fwrite($file, sprintf('    \'dms\' => %s,', $databaseDMSName) . PHP_EOL);
      fwrite($file, sprintf('    \'prefix\' => \'%s\',', $databasePrefix) . PHP_EOL);
      fwrite($file, sprintf('    \'scheme\' => \'%s\',', $databaseScheme) . PHP_EOL);
      fwrite($file, sprintf('    \'host\' => \'%s\',', $databaseHost) . PHP_EOL);
      fwrite($file, sprintf('    \'user\' => \'%s\',', $databaseUser) . PHP_EOL);
      fwrite($file, sprintf('    \'password\' => \'%s\',', $databasePassword) . PHP_EOL);
      fwrite($file, sprintf('    \'name\' => \'%s\',', $databaseName) . PHP_EOL);
      fwrite($file, '  ],' . PHP_EOL);
      fwrite($file, sprintf('  \'salt\' => \'%s\',', $CMSSalt) . PHP_EOL);
      fwrite($file, '  \'passwordHashingAlgorithm\' => PASSWORD_ARGON2ID,' . PHP_EOL);
      fwrite($file, '  \'sessionExpires\' => 86400,' . PHP_EOL);
      fwrite($file, '  \'sessionAdminExpires\' => 86400,' . PHP_EOL);
      fwrite($file, '  \'SSLCSP\' => [' . PHP_EOL);
      fwrite($file, '    \'default-src \\\'self\\\' *.cms-girvas.ru cms-girvas.ru *.xn--80afbjh2aegfdbd2a2a2a.xn--p1ai xn--80afbjh2aegfdbd2a2a2a.xn--p1ai\',' . PHP_EOL);
      fwrite($file, '    \'style-src \\\'unsafe-inline\\\' {DOMAIN} {DOMAIN_ALIASES} *.cms-girvas.ru cms-girvas.ru *.xn--80afbjh2aegfdbd2a2a2a.xn--p1ai xn--80afbjh2aegfdbd2a2a2a.xn--p1ai\',' . PHP_EOL);
      fwrite($file, '    \'script-src \\\'unsafe-inline\\\' \\\'unsafe-eval\\\' {DOMAIN} {DOMAIN_ALIASES} *.cms-girvas.ru cms-girvas.ru *.xn--80afbjh2aegfdbd2a2a2a.xn--p1ai xn--80afbjh2aegfdbd2a2a2a.xn--p1ai\',' . PHP_EOL);
      fwrite($file, '    \'script-src-elem \\\'unsafe-inline\\\' \\\'unsafe-eval\\\' {DOMAIN} {DOMAIN_ALIASES} *.cms-girvas.ru cms-girvas.ru *.xn--80afbjh2aegfdbd2a2a2a.xn--p1ai xn--80afbjh2aegfdbd2a2a2a.xn--p1ai\',' . PHP_EOL);
      fwrite($file, '    \'manifest-src \\\'self\\\' *.cms-girvas.ru cms-girvas.ru *.xn--80afbjh2aegfdbd2a2a2a.xn--p1ai xn--80afbjh2aegfdbd2a2a2a.xn--p1ai\'' . PHP_EOL);
      fwrite($file, '    \'img-src \\\'self\\\' data: *.cms-girvas.ru cms-girvas.ru *.xn--80afbjh2aegfdbd2a2a2a.xn--p1ai xn--80afbjh2aegfdbd2a2a2a.xn--p1ai\'' . PHP_EOL);
      fwrite($file, '  ],' . PHP_EOL);
      fwrite($file, '  \'SSLPermRedirect\' => false,' . PHP_EOL);
      fwrite($file, '  \'SSLHSTSMaxAge\' => 63072000,' . PHP_EOL);
      fwrite($file, '  \'SSLHSTSIncludeSubdomains\' => false,' . PHP_EOL);
      fwrite($file, '  \'SSLHSTSPreload\' => false,' . PHP_EOL);
      fwrite($file, '];' . PHP_EOL);
      fwrite($file, PHP_EOL);
      fclose($file);
      chmod($fileCMSConfigurationPath, 0664);
    }

    $JSONDataTypeDMS = match (strval($_GET['database_dms'])) {
      'DMS::PostgreSQL' => 'jsonb',
      default => 'json'
    };

    if (file_exists($fileCMSConfigurationPath)) {
      $tipBlockElement->setAttribute('class', 'tip tip_green');
      $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_CONFIGURATION_FILE_CREATED');
    } else {
      $tipBlockElement->setAttribute('class', 'tip tip_red');
      $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_CONFIGURATION_FILE_NOT_CREATED');
    }

    $document->appendChild($tipBlockElement);

    $handlerOutputData['html'] = $document->saveHTML();
    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }

  if ($installationStepIndex === 6) {
    $CMSDatabaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);
    $CMSConfigDatabase = $CMSCore->configurator->get('database');

    switch ($CMSConfigDatabase['dms']->getString()) {
      case 'PostgreSQL': $JSONDataTypeDMS = 'jsonb'; break;
      default: $JSONDataTypeDMS = 'json';
    }

    $document = new DOMDocument();
    $tipBlockElement = $document->createElement('div');

    $databasePrefix = $CMSConfigDatabase['prefix'];
    $databaseScheme = $CMSConfigDatabase['scheme'];
    $databasePrefix = ($databasePrefix !== '') ? $databasePrefix . '_' : '';
    $databaseScheme = ($databaseScheme !== '') ? $databaseScheme . '.' : '';
    
    try {
      // =======================
      // ТАБЛИЦА КОНФИГУРАЦИЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('configurations');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('value', 'text');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ЗАПИСЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('entries');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('categoryID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('authorID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА КАТЕГОРИЙ ЗАПИСЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('entries_categories');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('parentID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА КОММЕНТАРИЕВ ЗАПИСЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('entries_comments');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('entryID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('authorID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('content', 'text');
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ВЫБОРОК ЗАПИСЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('entries_samples');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ФОРМ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('forms');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('elements', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ДЛЯ ДАННЫХ С ФОРМ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('forms_data');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('formID', 'integer', 'NOT NULL');
      $queryBuilder->statement->addColumn('data', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА СТАТИЧЕСКИХ СТРАНИЦ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('pages_static');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('authorID', 'bigint');
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ОТЧЕТОВ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('reports');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('variables', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ПОЛЬЗОВАТЕЛЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('users');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('login', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('email', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('passwordHash', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('securityHash', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('emailIsSubmitted', 'boolean', 'NOT NULL DEFAULT false');
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ГРУПП ПОЛЬЗОВАТЕЛЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('users_groups');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('permissions', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('metadata', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ЗАЯВОК НА ПОДТВЕРЖДЕНИЕ РЕГИСТРАЦИИ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('users_registration_submits');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('userID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('submitToken', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('refusalToken', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА СЕССИЙ ПОЛЬЗОВАТЕЛЕЙ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('users_sessions');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('userID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('token', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('userIP', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('typeID', 'integer', 'NOT NULL DEFAULT 1');
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА ВЕБ-КАНАЛОВ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('web_channels');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('name', 'text', 'NOT NULL');
      $queryBuilder->statement->addColumn('entriesCategoryID', 'bigint', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('typeID', 'integer', 'NOT NULL DEFAULT 1');
      $queryBuilder->statement->addColumn('texts', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();

      // =======================
      // ТАБЛИЦА МЕТРИКИ
      // =======================

      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementCreateTable();
      $queryBuilder->statement->setCheckExists(true);
      $queryBuilder->statement->setTableName('metrics');
      $queryBuilder->statement->addColumn('id', 'serial', 'NOT NULL PRIMARY KEY');
      $queryBuilder->statement->addColumn('date', 'integer', 'NOT NULL');
      $queryBuilder->statement->addColumn('data', $JSONDataTypeDMS);
      $queryBuilder->statement->addColumn('createdUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->addColumn('updatedUnixTimestamp', 'integer', 'NOT NULL DEFAULT 0');
      $queryBuilder->statement->assembly();

      $databaseConnection = $CMSDatabaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);

      $execute = $databaseQuery->execute();
    } catch (PDOException $exception) {
      $tipBlockElement->setAttribute('class', 'tip tip_red');
      $tipBlockElement->nodeValue = $exception->getMessage();

      $document->appendChild($tipBlockElement);

      die(json_encode([
        'message' => $exception->getMessage(),
        'statusCode' => 0,
        'outputData' => [
          'html' => $document->saveHTML()
        ]
      // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
      ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    // =======================
    // ПЕРВИЧНОЕ НАПОЛНЕНИЕ БАЗЫ ДАННЫХ
    // =======================

    $CMSCore->databaseConnector = new CMSDatabaseConnector($CMSCore, $CMSCore->configurator);

    $firstEntriesCategoryTexts = [
      'en_US' => [
        'title' => 'News',
        'description' => 'All news'
      ],
      'ru_RU' => [
        'title' => 'Новости',
        'description' => 'Все новости'
      ]
    ];

    $secondEntriesCategoryTexts = [
      'en_US' => [
        'title' => 'Articles',
        'description' => 'All articles'
      ],
      'ru_RU' => [
        'title' => 'Статьи',
        'description' => 'Все статьи'
      ]
    ];

    $firstEntriesCategory = EntryCategory::create($CMSCore, 'news', 0, $firstEntriesCategoryTexts);
    $secondEntriesCategory = EntryCategory::create($CMSCore, 'articles', 0, $secondEntriesCategoryTexts);

    $firstEntriesCategory->update(['metadata' => ['isShowedOnIndexPage' => true]]);
    $secondEntriesCategory->update(['metadata' => ['isShowedOnIndexPage' => true]]);

    $firstEntryTexts = [
      'en_US' => [
        'title' => 'Hello, World!',
        'description' => 'Welcome to the GIRVAS Content Management System!',
        'content' => "Welcome to the GIRVAS Content Management System! This is a simple example of an entry on your website that does not contain anything important, but we would like to tell you a little about the system and the developer company. In the future, you can delete or change this entry, or just keep it as a keepsake!\r\n\r\n##Briefly about the system\r\nContent management system \"GIRVAS\" is a technically complex software, but easy to use, through which you can manage content on a website, as well as change its appearance using templates or expand functionality using a modular system. On our YouTube channel we have collected several examples demonstrating the functionality of the CMS \"GIRVAS\".\r\n\r\n##First release of the system\r\nThe control system is currently undergoing the first stage of its post-release review, so we need to collect feedback on its performance. If something does not work or does not work as it should, then feel free to report it via one of the contacts: https://карельский-разработчик.рф/page/contacts.",
        'keywords' => ['cms girvas', 'Content Management System GIRVAS']
      ],
      'ru_RU' => [
        'title' => 'Привет, Мир!',
        'description' => 'Добро пожаловать в Систему управления содержимым «ГИРВАС»!',
        'content' => "Добро пожаловать в Систему управления содержимым «ГИРВАС»! Это простой пример записи на Вашем сайте, которая не несет в себе ничего важного, однако хотели бы немного рассказать о системе и компании-разработчике. В дальнейшем Вы сможете удалить или изменить эту запись, или же просто оставить себе на память!\r\n\r\n##Кратко о системе\r\nСистема управления содержимым «ГИРВАС» представляет собой сложное в техническом плане программное обеспечение, но легкое в плане использования, через которое Вы можете управлять содержимым на веб-сайте, а также изменять его внешний вид при помощи шаблонов или расширять функционал при помощи модульной системы. На нашем YouTube-канале мы собрали несколько примеров с демонстрацией функционала СУС «ГИРВАС»\r\n\r\n##Первый выпуск системы\r\nСейчас система управления проходит первый этап своей проверки после выпуска, поэтому нам необходимо собирать обратную связь по ее работе. Если что-то будет не работать или работать не так как надо, то смело сообщайте по одному из контактов: https://карельский-разработчик.рф/page/contacts.",
        'keywords' => ['CMS «ГИРВАС»', 'Система управления содержимым «ГИРВАС»']
      ]
    ];

    $firstEntry = Entry::create($CMSCore, 'hello-world', 1, 1, $firstEntryTexts);
    $firstEntry->update(['metadata' => ['isPublished' => true]]);
    $firstEntry->update(['metadata' => ['previewURL' => '/uploads/media/example.webp']]);

    $firstEntriesSampleTexts = [
      'en_US' => [
        'title' => 'Last news',
        'description' => 'Last news on site'
      ],
      'ru_RU' => [
        'title' => 'Последние новости',
        'description' => 'Последние новости на сайте'
      ]
    ];

    $secondEntriesSampleTexts = [
      'en_US' => [
        'title' => 'Index entries',
        'description' => 'Last entries for index page'
      ],
      'ru_RU' => [
        'title' => 'Записи стартовой страницы',
        'description' => 'Последние записи для стартовой страницы'
      ]
    ];

    $firstEntriesSample = EntriesSample::create($CMSCore, 'last-news', $firstEntriesSampleTexts, [
      'limitCount' => 6,
      'sortTypeID' => 2,
      'categoriesIDs' => [1]
    ]);

    $secondEntriesSample = EntriesSample::create($CMSCore, 'index-entries', $secondEntriesSampleTexts, [
      'limitCount' => 8,
      'sortTypeID' => 2,
      'categoriesIDs' => [1, 2]
    ]);

    $firstUsersGroupTexts = [
      'en_US' => ['title' => 'Administrator'],
      'ru_RU' => ['title' => 'Администратор']
    ];

    $secondUsersGroupTexts = [
      'en_US' => ['title' => 'Moderator'],
      'ru_RU' => ['title' => 'Модератор']
    ];

    $thirtyUsersGroupTexts = [
      'en_US' => ['title' => 'Editor'],
      'ru_RU' => ['title' => 'Редактор']
    ];

    $fourtyUsersGroupTexts = [
      'en_US' => ['title' => 'User'],
      'ru_RU' => ['title' => 'Пользователь']
    ];

    $firstUsersGroup = UserGroup::create($CMSCore, 'admin', $firstUsersGroupTexts, 262143);
    $secondUsersGroup = UserGroup::create($CMSCore, 'moder', $secondUsersGroupTexts, 115585);
    $thirtyUsersGroup = UserGroup::create($CMSCore, 'editor', $thirtyUsersGroupTexts, 130049);
    $fourtyUsersGroup = UserGroup::create($CMSCore, 'user', $fourtyUsersGroupTexts, 114688);
    
    $CMSCore->configurator->insertDatabaseEntryValue('base_template', 'default');
    $CMSCore->configurator->insertDatabaseEntryValue('base_site_title', 'CMS «ГИРВАС»');
    $CMSCore->configurator->insertDatabaseEntryValue('base_engineering_works_status', 'off');
    $CMSCore->configurator->insertDatabaseEntryValue('base_engineering_works_text', '');
    $CMSCore->configurator->insertDatabaseEntryValue('seo_site_description', 'CMS GIRVAS - a multidisciplinary free contents control system from the Karelian Developer company.');
    $CMSCore->configurator->insertDatabaseEntryValue('seo_site_keywords', '["CMS GIRVAS","Free Content Management System","Free CMS","Karelian Developer"]');
    $CMSCore->configurator->insertDatabaseEntryValue('base_site_title', 'CMS GIRVAS');

    $tipBlockElement->setAttribute('class', 'tip tip_green');
    $tipBlockElement->nodeValue = $CMSCore->locale->getSingleValueByKey('API_INSTALLATION_TABLES_GENERATED');

    $document->appendChild($tipBlockElement);

    $handlerOutputData['html'] = $document->saveHTML();
    $handlerMessage = $handlerMessage ?? $CMSCore->locale->getSingleValueByKey('API_GET_DATA_SUCCESS');
    $handlerStatusCode = $handlerStatusCode ?? 1;
  }
}