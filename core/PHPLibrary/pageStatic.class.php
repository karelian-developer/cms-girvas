<?php

/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2021 - 2025, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

namespace core\PHPLibrary {
  use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
  use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
  use \PDOException as PDOException;

  #[\AllowDynamicProperties]
  class PageStatic implements EntityTypeContent  {
    private readonly SystemCore $CMSCore;
    private int $id;
    private int $categoryID;
    private int $viewsCount = 0;
    private string $name;
    
    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @return void
     */
    public function __construct(SystemCore $CMSCore, int $id) {
      $this->CMSCore = $CMSCore;
      $this->set_id($id);
    }
    
    /**
     * Инициализация данных из БД
     *
     * @param  mixed $columns
     * @return void
     */
    public function init_data(array $columns = ['*']) : void {
      $columnsData = $this->get_database_columns_data($columns);
      foreach ($columnsData as $name => $data) {
        $this->{$name} = $data;
      }
    }
    
    /**
     * Назначить ID страницы
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить ID страницы
     *
     * @param  mixed $value
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }

    /**
     * Установить количество просмотров
     * 
     * @param int $views
     * 
     * @return void
     */
    public function set_views_count(int $value) : void {
      $this->viewsCount = $value;
    }

    /**
     * Получить количество просмотров
     * 
     * @param int $value
     * 
     * @return int
     */
    public function get_views_count() : int {
      return $this->viewsCount;
    }

    /**
     * Получить временную отметку создания страницы в UNIX-формате
     * 
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp : 0;
    }

    /**
     * Получить временную отметку обновления страницы в UNIX-формате
     * 
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : 0;
    }

    /**
     * Получить ID автора страницы
     * 
     * @return int
     */
    public function get_author_id() : int|string {
      return (property_exists($this, 'authorID')) ? $this->authorID : 0;
    }
    
    /**
     * Получить заголовок страницы
     *
     * @param  mixed $localeName Наименование локализации
     * @return string
     */
    public function get_title($localeName = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts = json_decode($this->texts, true);
        if (isset($texts[$localeName]['title'])) {
          return $texts[$localeName]['title'];
        }
      }

      return '';
    }

    /**
     * Получить объект автора страницы
     * 
     * @param array $initData
     * 
     * @return User
     */
    public function get_author(array $initData = ['*']) : User|null {
      $authorID = $this->get_author_id();

      if (User::exists_by_id($this->CMSCore, $authorID)) {
        $author = new User($this->CMSCore, $authorID);
        $author->init_data($initData);

        return $author;
      }

      return null;
    }

    /**
     * Получить описание страницы
     *
     * @param  mixed $localeName Наименование локализации
     * @return string
     */
    public function get_description($localeName = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts = json_decode($this->texts, true);
        if (isset($texts[$localeName]['description'])) {
          return $texts[$localeName]['description'];
        }
      }

      return '';
    }

    /**
     * Получить содержимое страницы
     *
     * @param  mixed $localeName Наименование локализации
     * @return string
     */
    public function get_content($localeName = 'en_US') : string {
      if (property_exists($this, 'texts')) {
        $texts = json_decode($this->texts, true);
        if (isset($texts[$localeName]['content'])) {
          return $texts[$localeName]['content'];
        }
      }

      return '';
    }
    
    /**
     * Получить ключевые слова
     *
     * @param  mixed $localeName Наименование локализации
     * @return array
     */
    public function get_keywords($localeName = 'en_US') : array {
      if (property_exists($this, 'texts')) {
        $texts = json_decode($this->texts, true);
        if (isset($texts[$localeName]['keywords'])) {
          return $texts[$localeName]['keywords'];
        }
      }

      return [];
    }

    /**
     * Получить URL изображения предпросмотра
     *
     * @return string
     */
    public function get_preview_url() : string {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['preview_url'])) {
          return $metadata['preview_url'];
        }
      }

      return '';
    }

    /**
     * Получить статус публикации страницы
     *
     * @return bool
     */
    public function is_published() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['is_published'])) {
          return (bool)$metadata['is_published'];
        }
      }

      return false;
    }

    /**
     * Получить временную отметку публикации страницы в UNIX-формате
     * 
     * @return int
     */
    public function get_published_unix_timestamp() : int {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['publishedUnixTimestamp'])) {
          return $metadata['publishedUnixTimestamp'];
        }
      }

      return 0;
    }

    /**
     * Получить данные по дополнительному полю
     * 
     * @param string $fieldName
     * 
     * @return mixed
     */
    public function get_additional_field_data(string $fieldName) : mixed {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['additionalFields'])) {
          return (isset($metadata['additionalFields'][$fieldName])) ? $metadata['additionalFields'][$fieldName] : null;
        }
      }

      return null;
    }

    /**
     * Получить данные по дополнительным полям
     * 
     * @return array
     */
    public function get_additional_fields_data() : array {
      if (property_exists($this, 'metadata')) {
        /** @var array Массив метаданных */
        $metadata = json_decode($this->metadata, true);
        
        return (isset($metadata['additionalFields'])) ? $metadata['additionalFields'] : [];
      }

      return [];
    }

    /**
     * Получить путь до персонального шаблона
     * 
     * @return string
     */
    public function get_personal_template_path() : string {
      if (property_exists($this, 'metadata')) {
        /** @var array Метаданные в виде массива */
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['personalTemplatePath'])) {
          return (!empty($metadata['personalTemplatePath'])) ? $metadata['personalTemplatePath'] : 'templates/page/static.tpl';
        }
      }

      return 'templates/page/static.tpl';
    }

    /**
     * Проверить наличие файла персонального шаблона
     * 
     * @return bool
     */
    public function exists_personal_template_file() : bool {
      if (property_exists($this, 'metadata')) {
        /** @var string Путь до персонального шаблона */
        $themePath = $this->CMSCore->theme->get_path() . '/templates/' . $this->get_personal_template_path();
        return file_exists($themePath);
      }

      return false;
    }

    /**
     * Получить URL дефолтной заставки
     * 
     * @param SystemCore $CMSCore
     * @param int $size
     * 
     * @return string
     */
    public static function get_preview_default_url(SystemCore $CMSCore, int $size) : string {
      return '/' . $CMSCore->theme->get_url() . '/images/pageStatic/default_' . (string) $size . '.png';
    }
    
    /**
     * Получить имя страницы
     *
     * @return void
     */
    public function get_name() : string {
      return (property_exists($this, 'name')) ? $this->name : '';
    }
    
    /**
     * Получить URL до страницы
     *
     * @return void
     */
    public function get_url() {
      return '/page/' . $this->get_name();
    }
    
    /**
     * Получить данные колонок страницы в базе данных
     *
     * @param  mixed $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('pages_static');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $pageStaticID Идентификационный номер страницы */
      $pageStaticID = $this->get_id();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $pageStaticID, \PDO::PARAM_INT);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? $result : null;
    }
    
    /**
     * Получить объект страницы по его наименованию
     *
     * @param  mixed $CMSCore
     * @param  string $pageStaticName
     * @return PageStatic
     */
    public static function get_by_name(SystemCore $CMSCore, string $pageStaticName) : PageStatic|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('pages_static');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"name" = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $pageStaticName, \PDO::PARAM_STR);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? new PageStatic($CMSCore, (int)$result['id']) : null;
    }

    /**
     * Проверка наличия страницы
     *
     * @param  mixed $CMSCore
     * @param  string $pageStaticName
     * @return bool
     */
    public static function exists_by_name(SystemCore $CMSCore, string $pageStaticName) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('pages_static');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"name" = :name');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':name', $pageStaticName, \PDO::PARAM_STR);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($databaseQuery->fetchColumn()) ? true : false;
    }

    /**
     * Проверка наличия страницы по идентификационному номеру
     *
     * @param  mixed $CMSCore
     * @param  int $pageStaticID
     * @return bool
     */
    public static function exists_by_id(SystemCore $CMSCore, int $pageStaticID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('pages_static');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $pageStaticID, \PDO::PARAM_INT);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($databaseQuery->fetchColumn()) ? true : false;
    }
    
    /**
     * Удаление существующей страницы
     *
     * @return bool
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('pages_static');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
        $execute = $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return $execute ? true : false;
    }
        
    /**
     * Создание новой страницы
     *
     * @param  mixed $CMSCore
     * @param  mixed $name
     * @param  mixed $authorID
     * @param  mixed $categoryID
     * @param  mixed $texts
     * @return PageStatic
     */
    public static function create(SystemCore $CMSCore, string $name, int $authorID, array $texts, array $metadata = []) : PageStatic|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('pages_static');
      $queryBuilder->statement->add_column('authorID');
      $queryBuilder->statement->add_column('name');
      $queryBuilder->statement->add_column('texts');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $createdUnixTimestamp = time();
      $updatedUnixTimestamp = $createdUnixTimestamp;

      $texts = (!empty($texts)) ? json_encode($texts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';
      $metadata = (!empty($metadata)) ? json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) : '{}';

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':authorID', $authorID, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':name', $name, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':texts', $texts, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
        $execute = $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      if ($execute) {
        $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new PageStatic($CMSCore, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующей страницы
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('pages_static');
      $queryBuilder->statement->set_clause_set();

      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }

      if (array_key_exists('texts', $data)) {
        $json_fields = [];

        foreach ($data['texts'] as $name => $value) {
          array_push($json_fields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['texts'])) {
          $queryBuilder->statement->clauseSet->add_column('texts', 'texts::jsonb || ' . implode(' || ', $json_fields));
        }
      }

      if (array_key_exists('metadata', $data)) {
        $json_fields = [];

        foreach ($data['metadata'] as $name => $value) {
          array_push($json_fields, sprintf('\'{"%s": %s}\'::jsonb', $name, json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)));
        }

        if (!empty($data['metadata'])) {
          $queryBuilder->statement->clauseSet->add_column('metadata', 'metadata::jsonb || ' . implode(' || ', $json_fields));
        }
      }

      $queryBuilder->statement->clauseSet->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->clauseSet->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      /** @var int $updatedUnixTimestamp Текущее время в UNIX-формате */
      $updatedUnixTimestamp = time();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        error_log($queryBuilder->statement->assembled);
        foreach ($data as $name => $value) {
          if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'texts', 'metadata'])) {
            switch (gettype($value)) {
              case 'boolean': $valueType = \PDO::PARAM_BOOL; break;
              case 'integer': $valueType = \PDO::PARAM_INT; break;
              case 'string': $valueType = \PDO::PARAM_STR; break;
              case 'null': $valueType = \PDO::PARAM_NULL; break;
            }

            $databaseQuery->bindParam(':' . $name, $data[$name], $valueType);
          }
        }

        $databaseQuery->bindParam(':id', $this->id, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
        $execute = $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      return ($execute) ? true : false;
    }

  }

}

?>