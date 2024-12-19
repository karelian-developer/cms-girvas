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

  #[\AllowDynamicProperties]
  class EntryComment {
    private readonly SystemCore $system_core;
    private int $id;
    
    /**
     * __construct
     *
     * @param  SystemCore $system_core
     * @param  int $id
     * @return void
     */
    public function __construct(SystemCore $system_core, int $id) {
      $this->system_core = $system_core;
      $this->set_id($id);
    }

    /**
     * Инициализация данных из БД
     *
     * @param  mixed $columns
     * @return void
     */
    public function init_data(array $columns = ['*']) {
      $columns_data = $this->get_database_columns_data($columns);
      foreach ($columns_data as $column_name => $column_data) {
        $this->{$column_name} = $column_data;
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
      return (property_exists($this, 'entry_id')) ? $this->entry_id : '{ERROR:ENTRY_COMMENT_DATA_IS_NOT_EXISTS=entry_id}';
    }
    
    /**
     * Получить объект записи, к которой написан комментарий
     *
     * @return Entry|null
     */
    public function get_entry() : Entry|null {
      return (property_exists($this, 'entry_id')) ? new User($this->system_core, $this->entry_id) : null;
    }
    
    /**
     * Получить идентификатор автора комментария
     *
     * @return int
     */
    public function get_author_id() : int {
      return (property_exists($this, 'author_id')) ? $this->author_id : '{ERROR:ENTRY_COMMENT_DATA_IS_NOT_EXISTS=author_id}';
    }
    
    /**
     * Получить объект автора комментария
     *
     * @return User|null
     */
    public function get_author() : User|null {
      return (property_exists($this, 'author_id')) ? new User($this->system_core, $this->author_id) : null;
    }
    
    /**
     * Получить идентификатор записи, к которой написан комментарий
     *
     * @return string
     */
    public function get_content() : string {
      return (property_exists($this, 'content')) ? $this->content : '{ERROR:ENTRY_COMMENT_DATA_IS_NOT_EXISTS=content}';
    }
    
    /**
     * Получить дату создания в UNIX-формате
     *
     * @return int
     */
    public function get_created_unix_timestamp() : int|string {
      return (property_exists($this, 'created_unix_timestamp')) ? $this->created_unix_timestamp : '{ERROR:ENTRY_COMMENT_DATA_IS_NOT_EXISTS=created_unix_timestamp}';
    }
    
    /**
     * Получить дату обновления в UNIX-формате
     *
     * @return int
     */
    public function get_updated_unix_timestamp() : int|string {
      return (property_exists($this, 'updated_unix_timestamp')) ? $this->updated_unix_timestamp : '{ERROR:ENTRY_COMMENT_DATA_IS_NOT_EXISTS=updated_unix_timestamp}';
    }

    /**
     * Получить статус отображения
     *
     * @return bool
     */
    public function is_hidden() : bool {
      if (property_exists($this, 'metadata')) {
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['isHidden'])) {
          return (bool)$metadata_array['isHidden'];
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
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['hiddenReason'])) {
          return $metadata_array['hiddenReason'];
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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['count(id)']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_comments');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('(metadata::jsonb->\'parentID\')::int = :parentID::int');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':parentID', $this->id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result['count'] : 0;
    }

    /**
     * Получить массив объектов ответов к комментарию
     * 
     * @return array
     */
    public function get_answers() : array {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['id']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_comments');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('(metadata::jsonb->\'parentID\')::int = :parentID::int');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':parentID', $this->id, \PDO::PARAM_INT);
			$database_query->execute();

      $objects = [];
      $results = $database_query->fetchAll(\PDO::FETCH_ASSOC);
      if ($results) {
        foreach ($results as $data) {
          array_push($objects, new EntryComment($this->system_core, $data['id']));
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
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['parentID'])) {
          return (int)$metadata_array['parentID'];
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
          return new EntryComment($this->system_core, $parent_id);
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
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['rating'])) {
          return $metadata_array['rating'];
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
        $metadata_array = json_decode($this->metadata, true);
        if (isset($metadata_array['ratingVoters'])) {
          return $metadata_array['ratingVoters'];
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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections($columns);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_comments');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();
      
      /** @var int $entry_id Идентификационный номер записи */
      $entry_id = $this->get_id();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $entry_id, \PDO::PARAM_INT);
			$database_query->execute();

      $result = $database_query->fetch(\PDO::FETCH_ASSOC);
      return ($result) ? $result : null;
    }

    /**
     * Проверка наличия комментария по идентификационному номеру
     *
     * @param  SystemCore $system_core
     * @param  int $comment_id
     * @return bool
     */
    public static function exists_by_id(SystemCore $system_core, int $comment_id) : bool {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_select();
      $query_builder->statement->add_selections(['1']);
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_comments');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->set_clause_limit(1);
      $query_builder->statement->assembly();

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $comment_id, \PDO::PARAM_INT);
			$database_query->execute();

      return ($database_query->fetchColumn()) ? true : false;
    }
    
    /**
     * Удаление существующего комментария
     *
     * @return bool
     */
    public function delete() : bool {
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_delete();
      $query_builder->statement->set_clause_from();
      $query_builder->statement->clause_from->add_table('entries_comments');
      $query_builder->statement->clause_from->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':id', $this->id, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }
        
    /**
     * Создание нового комментария
     *
     * @param  SystemCore $system_core
     * @param  int $entry_id
     * @param  int $author_id
     * @param  string $content
     * @return EntryComment|null
     */
    public static function create(SystemCore $system_core, int $entry_id, int $author_id, string $content) : EntryComment|null {
      $query_builder = new DatabaseQueryBuilder($system_core);
      $query_builder->set_statement_insert();
      $query_builder->statement->set_table('entries_comments');
      $query_builder->statement->add_column('author_id');
      $query_builder->statement->add_column('entry_id');
      $query_builder->statement->add_column('content');
      $query_builder->statement->add_column('created_unix_timestamp');
      $query_builder->statement->add_column('updated_unix_timestamp');
      $query_builder->statement->add_column('metadata');
      $query_builder->statement->set_clause_returning();
      $query_builder->statement->clause_returning->add_column('id');
      $query_builder->statement->assembly();

      $metadata = [];
      $metadata['rating'] = 0;
      $metadata['ratingVoters'] = json_decode('{}');
      $metadata['isHidden'] = false;
      $metadata['hiddenReason'] = false;
      $metadata = json_encode($metadata, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

      $entry_comment_created_unix_timestamp = time();
      $entry_comment_updated_unix_timestamp = $entry_comment_created_unix_timestamp;

      $database_connection = $system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      $database_query->bindParam(':author_id', $author_id, \PDO::PARAM_INT);
      $database_query->bindParam(':entry_id', $entry_id, \PDO::PARAM_INT);
      $database_query->bindParam(':content', $content, \PDO::PARAM_STR);
      $database_query->bindParam(':created_unix_timestamp', $entry_comment_created_unix_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $entry_comment_updated_unix_timestamp, \PDO::PARAM_INT);
      $database_query->bindParam(':metadata', $metadata, \PDO::PARAM_STR);
      $execute = $database_query->execute();

      if ($execute) {
        $result = $database_query->fetch(\PDO::FETCH_ASSOC);
        return ($result) ? new EntryComment($system_core, $result['id']) : null;
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
      $query_builder = new DatabaseQueryBuilder($this->system_core);
      $query_builder->set_statement_update();
      $query_builder->statement->set_table('entries_comments');
      $query_builder->statement->set_clause_set();

      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata'])) {
          $query_builder->statement->clause_set->add_column($data_name);
        }
      }

      if (array_key_exists('metadata', $data)) {
        if (!empty($data['metadata'])) {
          $metadata_assignments = [];
          
          foreach ($data['metadata'] as $metadata_name => $metadata_value) {
            if ($metadata_name == 'rating_vote' && $metadata_value['vote'] == 'up') {
              $comment_rating_voters = $this->get_rating_voters();

              array_push($metadata_assignments, sprintf('jsonb_set(metadata::jsonb, \'{ratingVoters}\', (metadata::jsonb->>\'ratingVoters\')::jsonb || \'{"%d": "%s"}\')', $metadata_value['voter_id'], $metadata_value['vote']));

              if (!isset($comment_rating_voters[$metadata_value['voter_id']])) {
                array_push($metadata_assignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int + 1)');
              } else {
                if ($comment_rating_voters[$metadata_value['voter_id']] != $metadata_value['vote']) {
                  array_push($metadata_assignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int + 2)');
                }
              }
            } else if ($metadata_name == 'rating_vote' && $metadata_value['vote'] == 'down') {
              $comment_rating_voters = $this->get_rating_voters();

              array_push($metadata_assignments, sprintf('jsonb_set(metadata::jsonb, \'{ratingVoters}\', (metadata::jsonb->>\'ratingVoters\')::jsonb || \'{"%d": "%s"}\')', $metadata_value['voter_id'], $metadata_value['vote']));
              if (!isset($comment_rating_voters[$metadata_value['voter_id']])) {
                array_push($metadata_assignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int - 1)');
              } else {
                if ($comment_rating_voters[$metadata_value['voter_id']] != $metadata_value['vote']) {
                  array_push($metadata_assignments, 'jsonb_build_object(\'rating\', (metadata::jsonb->\'rating\')::int - 2)');
                }
              }
            } else if ($metadata_name == 'is_hidden') {
              array_push($metadata_assignments, sprintf('jsonb_build_object(\'isHidden\', %d::int::bool)', $metadata_value));
            } else if ($metadata_name == 'hidden_reason') {
              array_push($metadata_assignments, sprintf('jsonb_build_object(\'hiddenReason\', \'%s\'::text)', $metadata_value));
            } else if ($metadata_name == 'parent_id') {
              array_push($metadata_assignments, sprintf('jsonb_build_object(\'parentID\', %d::int)', $metadata_value));
            }
          }

          if (!empty($metadata_assignments)) {
            $query_builder->statement->clause_set->add_column('metadata', sprintf('metadata::jsonb || %s', implode(' || ', $metadata_assignments)));
          }
        }
      }

      $query_builder->statement->clause_set->add_column('updated_unix_timestamp');
      $query_builder->statement->clause_set->assembly();
      $query_builder->statement->set_clause_where();
      $query_builder->statement->clause_where->add_condition('id = :id');
      $query_builder->statement->clause_where->assembly();
      $query_builder->statement->assembly();

      /** @var int $entry_updated_unix_timestamp Текущее время в UNIX-формате */
      $entry_comment_updated_unix_timestamp = time();

      $database_connection = $this->system_core->database_connector->database->connection;
      $database_query = $database_connection->prepare($query_builder->statement->assembled);
      error_log($query_builder->statement->assembled);
      foreach ($data as $data_name => $data_value) {
        if (!in_array($data_name, ['id', 'created_unix_timestamp', 'updated_unix_timestamp', 'metadata'])) {
          switch (gettype($data_value)) {
            case 'boolean': $data_value_type = \PDO::PARAM_BOOL; break;
            case 'integer': $data_value_type = \PDO::PARAM_INT; break;
            case 'string': $data_value_type = \PDO::PARAM_STR; break;
            case 'null': $data_value_type = \PDO::PARAM_NULL; break;
          }

          $database_query->bindParam(':' . $data_name, $data[$data_name], $data_value_type);
        }
      }

      $database_query->bindParam(':id', $this->id, \PDO::PARAM_INT);
      $database_query->bindParam(':updated_unix_timestamp', $entry_comment_updated_unix_timestamp, \PDO::PARAM_INT);
			$execute = $database_query->execute();

      return ($execute) ? true : false;
    }
  }
}

?>