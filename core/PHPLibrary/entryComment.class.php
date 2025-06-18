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
  class EntryComment implements EntityTypeContent {
    private readonly SystemCore $CMSCore;
    private int $id;
    
    /**
     * __construct
     *
     * @param  SystemCore $CMSCore
     * @param  int $id
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
     * Назначить идентификатор комментарию
     *
     * @param  mixed $value
     * @return void
     */
    private function set_id(int $value) : void {
      $this->id = $value;
    }
    
    /**
     * Получить идентификатор комментария
     *
     * @return int
     */
    public function get_id() : int {
      return $this->id;
    }
    
    /**
     * Получить идентификатор записи, к которой написан комментарий
     *
     * @return int
     */
    public function get_entry_id() : int {
      return (property_exists($this, 'entryID')) ? $this->entryID : 0;
    }
    
    /**
     * Получить объект записи, к которой написан комментарий
     *
     * @return Entry|null
     */
    public function get_entry() : Entry|null {
      return (property_exists($this, 'entryID')) ? new User($this->CMSCore, $this->entryID) : null;
    }
    
    /**
     * Получить идентификатор автора комментария
     *
     * @return int
     */
    public function get_author_id() : int {
      return (property_exists($this, 'authorID')) ? $this->authorID : 0;
    }
    
    /**
     * Получить объект автора комментария
     *
     * @return User|null
     */
    public function get_author() : User|null {
      return (property_exists($this, 'authorID')) ? new User($this->CMSCore, $this->authorID) : null;
    }
    
    /**
     * Получить идентификатор записи, к которой написан комментарий
     *
     * @return string
     */
    public function get_content() : string {
      return (property_exists($this, 'content')) ? $this->content : '';
    }
    
    /**
     * Получить дату создания в UNIX-формате
     *
     * @return int
     */
    public function get_created_unix_timestamp() : int {
      return (property_exists($this, 'createdUnixTimestamp')) ? $this->createdUnixTimestamp :  0;
    }
    
    /**
     * Получить дату обновления в UNIX-формате
     *
     * @return int
     */
    public function get_updated_unix_timestamp() : int {
      return (property_exists($this, 'updatedUnixTimestamp')) ? $this->updatedUnixTimestamp : 0;
    }

    /**
     * Получить статус отображения
     *
     * @return bool
     */
    public function is_hidden() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['isHidden'])) {
          return (bool)$metadata['isHidden'];
        }
      }

      return false;
    }

    /**
     * Получить причину скрытия комментария
     *
     * @return string
     */
    public function get_hidden_reason() : string {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['hiddenReason'])) {
          return $metadata['hiddenReason'];
        }
      }

      return '';
    }

    /**
     * Получить количество ответов к комментарию
     * 
     * @return int
     */
    public function get_answers_count() : int {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['count(id)']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_comments');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('(metadata::jsonb->\'parentID\')::int = :parentID::int');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':parentID', $this->id, \PDO::PARAM_INT);
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
      return ($result) ? $result['count'] : 0;
    }

    /**
     * Получить массив объектов ответов к комментарию
     * 
     * @return array
     */
    public function get_answers() : array {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['id']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_comments');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('(metadata::jsonb->\'parentID\')::int = :parentID::int');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':parentID', $this->id, \PDO::PARAM_INT);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        // Убираем экранирующие слеши из ответа, а также преобразовываем UNICODE в текст
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }

      $objects = [];
      $results = $databaseQuery->fetchAll(\PDO::FETCH_ASSOC);
      if ($results) {
        foreach ($results as $data) {
          array_push($objects, new EntryComment($this->CMSCore, $data['id']));
        }
      }

      return $objects;
    }

    /**
     * Получить ID комментария-родителя
     *
     * @return string
     */
    public function get_parent_id() : int {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['parentID'])) {
          return (int)$metadata['parentID'];
        }
      }

      return 0;
    }

    /**
     * Получить объект комментария-родителя
     *
     * @return string
     */
    public function get_parent() : EntryComment|null {
      if (property_exists($this, 'metadata')) {
        $parent_id = $this->get_parent_id();
        if ($parent_id > 0) {
          return new EntryComment($this->CMSCore, $parent_id);
        }
      }

      return null;
    }

    /**
     * Получить рейтинг комментария
     *
     * @return int
     */
    public function get_rating() : int {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['rating'])) {
          return $metadata['rating'];
        }
      }

      return 0;
    }

    /**
     * Получить массив ID голосовавших пользователей за рейтинг комментария
     *
     * @return string
     */
    public function get_rating_voters() : array {
      if (property_exists($this, 'metadata')) {
        $metadata = json_decode($this->metadata, true);
        if (isset($metadata['ratingVoters'])) {
          return $metadata['ratingVoters'];
        }
      }

      return [];
    }

    /**
     * Проверить наличие голоса от конкретного пользователя по его ID
     *
     * @return bool
     */
    public function user_is_voted(int $user_id) : bool {
      if (property_exists($this, 'metadata')) {
        $voters = $this->get_rating_voters();
        return in_array($user_id, $voters);
      }

      return false;
    }
    
    /**
     * Получить данные колонок комментария в базе данных
     *
     * @param  mixed $columns
     * @return void
     */
    private function get_database_columns_data(array $columns = ['*']) : array|null {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections($columns);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_comments');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition(': = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();
      
      /** @var int $entryID Идентификационный номер записи */
      $entryID = $this->get_id();

      try {
        $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $entryID, \PDO::PARAM_INT);
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
      return ($result) ? $result : null;
    }

    /**
     * Проверка наличия комментария по идентификационному номеру
     *
     * @param  SystemCore $CMSCore
     * @param  int $commentID
     * @return bool
     */
    public static function exists_by_id(SystemCore $CMSCore, int $commentID) : bool {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_select();
      $queryBuilder->statement->add_selections(['1']);
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_comments');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->set_clause_where();
      $queryBuilder->statement->clauseWhere->add_condition('"id" = :id');
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->set_clause_limit(1);
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':id', $commentID, \PDO::PARAM_INT);
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
     * Удаление существующего комментария
     *
     * @return bool
     */
    public function delete() : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_delete();
      $queryBuilder->statement->set_clause_from();
      $queryBuilder->statement->clauseFrom->add_table('entries_comments');
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

      return ($execute) ? true : false;
    }
        
    /**
     * Создание нового комментария
     *
     * @param  SystemCore $CMSCore
     * @param  int $entryID
     * @param  int $authorID
     * @param  string $content
     * @return EntryComment|null
     */
    public static function create(SystemCore $CMSCore, int $entryID, int $authorID, string $content) : EntryComment|null {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore);
      $queryBuilder->set_statement_insert();
      $queryBuilder->statement->set_table('entries_comments');
      $queryBuilder->statement->add_column('authorID');
      $queryBuilder->statement->add_column('entryID');
      $queryBuilder->statement->add_column('content');
      $queryBuilder->statement->add_column('createdUnixTimestamp');
      $queryBuilder->statement->add_column('updatedUnixTimestamp');
      $queryBuilder->statement->add_column('metadata');
      $queryBuilder->statement->set_clause_returning();
      $queryBuilder->statement->clauseReturning->add_column('id');
      $queryBuilder->statement->assembly();

      $metadata = [];
      $metadata['rating'] = 0;
      $metadata['ratingVoters'] = json_decode('{}');
      $metadata['isHidden'] = false;
      $metadata['hiddenReason'] = false;
      $metadata = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      $createdUnixTimestamp = time();
      $updatedUnixTimestamp = $createdUnixTimestamp;

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->bindParam(':authorID', $authorID, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':entryID', $entryID, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':content', $content, \PDO::PARAM_STR);
        $databaseQuery->bindParam(':createdUnixTimestamp', $createdUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':updatedUnixTimestamp', $updatedUnixTimestamp, \PDO::PARAM_INT);
        $databaseQuery->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
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
        return ($result) ? new EntryComment($CMSCore, $result['id']) : null;
      }

      return null;
    }

    /**
     * Обновление существующего комментария
     *
     * @param  array $data Массив данных
     * @return bool
     */
    public function update(array $data) : bool {
      $queryBuilder = new DatabaseQueryBuilder($this->CMSCore);
      $queryBuilder->set_statement_update();
      $queryBuilder->statement->set_table('entries_comments');
      $queryBuilder->statement->set_clause_set();

      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
          $queryBuilder->statement->clauseSet->add_column($name);
        }
      }

      if (array_key_exists('metadata', $data)) {
        if (!empty($data['metadata'])) {
          $metadataAssignments = [];
          
          foreach ($data['metadata'] as $metadataName => $metadataValue) {
            if ($metadataName == 'rating_vote' && $metadataValue['vote'] == 'up') {
              $commentRatingVoters = $this->get_rating_voters();

              array_push($metadataAssignments, sprintf('jsonb_set(metadata::jsonb, \'{ratingVoters}\', (metadata::jsonb->>\'ratingVoters\')::jsonb || \'{"%d": "%s"}\')', $metadataValue['voter_id'], $metadataValue['vote']));

              if (!isset($commentRatingVoters[$metadataValue['voter_id']])) {
                array_push($metadataAssignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int + 1)');
              } else {
                if ($commentRatingVoters[$metadataValue['voter_id']] !== $metadataValue['vote']) {
                  array_push($metadataAssignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int + 2)');
                }
              }
            } else if ($metadataName === 'ratingVote' && $metadataValue['vote'] === 'down') {
              $commentRatingVoters = $this->get_rating_voters();

              array_push($metadataAssignments, sprintf('jsonb_set(metadata::jsonb, \'{ratingVoters}\', (metadata::jsonb->>\'ratingVoters\')::jsonb || \'{"%d": "%s"}\')', $metadataValue['voter_id'], $metadataValue['vote']));
              if (!isset($commentRatingVoters[$metadataValue['voter_id']])) {
                array_push($metadataAssignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int - 1)');
              } else {
                if ($commentRatingVoters[$metadataValue['voter_id']] !== $metadataValue['vote']) {
                  array_push($metadataAssignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int - 2)');
                }
              }
            } else if ($metadataName === 'isHidden') {
              array_push($metadataAssignments, sprintf('jsonb_build_object(\'isHidden\', %d::int::bool)', $metadataValue));
            } else if ($metadataName === 'hiddenReason') {
              array_push($metadataAssignments, sprintf('jsonb_build_object(\'hiddenReason\', \'%s\'::text)', $metadataValue));
            } else if ($metadataName === 'parentID') {
              array_push($metadataAssignments, sprintf('jsonb_build_object(\'parentID\', %d::int)', $metadataValue));
            }
          }

          if (!empty($metadataAssignments)) {
            $queryBuilder->statement->clauseSet->add_column('metadata', sprintf('metadata::jsonb || %s', implode(' || ', $metadataAssignments)));
          }
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
          if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
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