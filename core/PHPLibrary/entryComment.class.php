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
 * @copyright   Copyright (c) 2021 - 2027, ИП Шестаков А.Р., «Карельский разработчик» (https://карельский-разработчик.рф/)
 * Все права защищены.
 * 
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @author      Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * 
 * @support     support@karelian-developer.ru
 */

namespace core\PHPLibrary;

use \core\PHPLibrary\Database\QueryBuilder as DatabaseQueryBuilder;
use \core\PHPLibrary\Entities\Types\Content as EntityTypeContent;
use \PDOException as PDOException;

#[\AllowDynamicProperties]
class EntryComment implements EntityTypeContent
{
  /**
   * __construct
   *
   * @param CoreInterface $CMSCore
   * @param int $id
   * 
   * @return void
   */
  public function __construct(
    private CoreInterface $CMSCore,
    private int $id
  ) {}

  /**
   * Инициализация данных из БД
   *
   * @param  mixed $columns
   * @return void
   */
  public function initData(array $columns = ['*']) : void
  {
    $columnsData = $this->getDatabaseColumnsData($columns);
    foreach ($columnsData as $name => $data) {
      $this->{$name} = $data;
    }
  }
  
  /**
   * Получить идентификатор комментария
   *
   * @return int
   */
  public function getID() : int
  {
    return $this->id;
  }
  
  /**
   * Получить идентификатор записи, к которой написан комментарий
   *
   * @return int
   */
  public function getEntryID() : int
  {
    return $this->entryID ?? 0;
  }
  
  /**
   * Получить объект записи, к которой написан комментарий
   *
   * @return Entry|null
   */
  public function getEntry() : Entry|null
  {
    return new Entry($this->CMSCore, $this->entryID) ?? null;
  }
  
  /**
   * Получить идентификатор автора комментария
   *
   * @return int
   */
  public function getAuthorID() : int
  {
    return $this->authorID ?? 0;
  }
  
  /**
   * Получить объект автора комментария
   *
   * @return User|null
   */
  public function getAuthor() : User|null
  {
    return new User($this->CMSCore, $this->authorID) ?? null;
  }
  
  /**
   * Получить идентификатор записи, к которой написан комментарий
   *
   * @return string
   */
  public function getContent() : string
  {
    return $this->content ?? '';
  }
  
  /**
   * Получить дату создания в UNIX-формате
   *
   * @return int
   */
  public function getCreatedUnixTimestamp() : int
  {
    return $this->createdUnixTimestamp ??  0;
  }
  
  /**
   * Получить дату обновления в UNIX-формате
   *
   * @return int
   */
  public function getUpdatedUnixTimestamp() : int
  {
    return $this->updatedUnixTimestamp ?? 0;
  }

  /**
   * Получить статус отображения
   *
   * @return bool
   */
  public function isHidden() : bool
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['isHidden'])) {
        return (bool) $metadata['isHidden'];
      }
    }

    return false;
  }

  /**
   * Получить причину скрытия комментария
   *
   * @return string
   */
  public function getHiddenReason() : string
  {
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
  public function getAnswersCount() : int
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['count(id) AS count']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :parentID',
        $queryBuilder->dialect->jsonExtractInt('metadata', 'parentID')
      )
    );
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
    return $result ? $result['count'] : 0;
  }

  /**
   * Получить массив объектов ответов к комментарию
   * 
   * @return array
   */
  public function getAnswers() : array
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['id']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :parentID',
        $queryBuilder->dialect->jsonExtractInt('metadata', 'parentID')
      )
    );
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
  public function getParentID() : int
  {
    if (property_exists($this, 'metadata')) {
      $metadata = json_decode($this->metadata, true);

      if (isset($metadata['parentID'])) {
        return (int) $metadata['parentID'];
      }
    }

    return 0;
  }

  /**
   * Получить объект комментария-родителя
   *
   * @return string
   */
  public function getParent() : EntryComment|null
  {
    if (property_exists($this, 'metadata')) {
      $parentID = $this->getParentID();
      if ($parentID > 0) {
        return new EntryComment($this->CMSCore, $parentID);
      }
    }

    return null;
  }

  /**
   * Получить рейтинг комментария
   *
   * @return int
   */
  public function getRating() : int
  {
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
  public function getRatingVoters() : array
  {
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
  public function user_is_voted(int $user_id) : bool
  {
    if (property_exists($this, 'metadata')) {
      $voters = $this->getRatingVoters();
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
  private function getDatabaseColumnsData(array $columns = ['*']) : array|null
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections($columns);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :id',
        $queryBuilder->dialect->quoteIdentifier('id')
      )
    );
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();
    
    /** @var int $entryID Идентификационный номер записи */
    $entryID = $this->getID();

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
    return $result ? $result : null;
  }

  /**
   * Проверка наличия комментария по идентификационному номеру
   *
   * @param  SystemCore $CMSCore
   * @param  int $commentID
   * @return bool
   */
  public static function existsByID(SystemCore $CMSCore, int $commentID) : bool
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementSelect();
    $queryBuilder->statement->addSelections(['1']);
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :id',
        $queryBuilder->dialect->quoteIdentifier('id')
      )
    );
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->setClauseLimit(1);
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

    return $databaseQuery->fetchColumn() ? true : false;
  }
  
  /**
   * Удаление существующего комментария
   *
   * @return bool
   */
  public function delete() : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementDelete();
    $queryBuilder->statement->setClauseFrom();
    $queryBuilder->statement->clauseFrom->addTable('entries_comments');
    $queryBuilder->statement->clauseFrom->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :id',
        $queryBuilder->dialect->quoteIdentifier('id')
      )
    );
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
   * Создание нового комментария
   *
   * @param  SystemCore $CMSCore
   * @param  int $entryID
   * @param  int $authorID
   * @param  string $content
   * @return EntryComment|null
   */
  public static function create(SystemCore $CMSCore, int $entryID, int $authorID, string $content) : EntryComment|null
  {
    $CMSConfigurator = $CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');
    
    $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
    $queryBuilder->setStatementInsert();
    $queryBuilder->statement->setTable('entries_comments');
    $queryBuilder->statement->addColumn('authorID');
    $queryBuilder->statement->addColumn('entryID');
    $queryBuilder->statement->addColumn('content');
    $queryBuilder->statement->addColumn('createdUnixTimestamp');
    $queryBuilder->statement->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->addColumn('metadata');
    $queryBuilder->statement->setClauseReturning();
    $queryBuilder->statement->clauseReturning->addColumn('id');
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

    $dialect = $queryBuilder->dialect;

    if (!$dialect->supportsInsertReturning()) {
      $queryBuilder = new DatabaseQueryBuilder($CMSCore, $CMSConfigDatabase['dms']);
      $queryBuilder->setStatementSelect();
      $queryBuilder->statement->addSelections(['id']);
      $queryBuilder->statement->setClauseFrom();
      $queryBuilder->statement->clauseFrom->addTable('entries_comments');
      $queryBuilder->statement->clauseFrom->assembly();
      $queryBuilder->statement->setClauseWhere();
      $queryBuilder->statement->clauseWhere->addCondition(
        $dialect->getLastInsertedIDCondition('id')
      );
      $queryBuilder->statement->clauseWhere->assembly();
      $queryBuilder->statement->assembly();

      try {
        $databaseConnection = $CMSCore->databaseConnector->database->connection;
        $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
        $databaseQuery->execute();
      } catch (PDOException $exception) {
        die(json_encode([
          'message' => $exception->getMessage(),
          'statusCode' => 0,
          'outputData' => []
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
      }
    }

    if ($execute) {
      $result = $databaseQuery->fetch(\PDO::FETCH_ASSOC);
      return $result ? new EntryComment($CMSCore, (int) $result['id']) : null;
    }

    return null;
  }

  /**
   * Обновление существующего комментария
   *
   * @param  array $data Массив данных
   * @return bool
   */
  public function update(array $data) : bool
  {
    $CMSConfigurator = $this->CMSCore->configurator;
    $CMSConfigDatabase = $CMSConfigurator->get('database');

    $queryBuilder = new DatabaseQueryBuilder($this->CMSCore, $CMSConfigDatabase['dms']);
    $dialect = $queryBuilder->dialect;

    $queryBuilder->setStatementUpdate();
    $queryBuilder->statement->setTable('entries_comments');
    $queryBuilder->statement->setClauseSet();

    foreach ($data as $name => $value) {
      if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
        $queryBuilder->statement->clauseSet->addColumn($name);
      }
    }

    if (array_key_exists('metadata', $data) && !empty($data['metadata'])) {
      $dialect = $queryBuilder->dialect;
      $metadataPairs = [];

      foreach ($data['metadata'] as $name => $value) {
        if ($name === 'ratingVote' && is_array($value)) {
          $vote = $value['vote'] ?? '';
          $voterID = (int) ($value['voterID'] ?? 0);

          if ($voterID <= 0 || !in_array($vote, ['up', 'down'], true)) {
            continue;
          }

          $commentRatingVoters = $this->getRatingVoters();

          // ratingVoters: сливаем текущий объект с новым фрагментом {"id": "vote"}
          $voteFragment = sprintf("'{\"%d\": \"%s\"}'", $voterID, $vote);
          $metadataPairs['ratingVoters'] = $dialect->jsonObjectMergeKey(
            'metadata',
            'ratingVoters',
            $voteFragment
          );

          // rating: вычисляем дельту
          $delta = ($vote === 'up') ? 1 : -1;
          if (isset($commentRatingVoters[$voterID]) && $commentRatingVoters[$voterID] !== $vote) {
            $delta *= 2;
          }

          $metadataPairs['rating'] = sprintf(
            '%s + %d',
            $dialect->jsonExtractInt('metadata', 'rating'),
            $delta
          );
        } elseif ($name === 'isHidden') {
          $metadataPairs['isHidden'] = $value ? 'TRUE' : 'FALSE';
        } elseif ($name === 'hiddenReason') {
          $metadataPairs['hiddenReason'] = sprintf("'%s'", addslashes((string) $value));
        } elseif ($name === 'parentID') {
          $metadataPairs['parentID'] = (string) (int) $value;
        }
      }

      if (!empty($metadataPairs)) {
        $metadataPatch = $dialect->jsonBuildObject($metadataPairs);
        $queryBuilder->statement->clauseSet->addColumn(
          'metadata',
          $dialect->jsonMergePatches('metadata', [$metadataPatch])
        );
      }
    }

    $queryBuilder->statement->clauseSet->addColumn('updatedUnixTimestamp');
    $queryBuilder->statement->clauseSet->assembly();
    $queryBuilder->statement->setClauseWhere();
    $queryBuilder->statement->clauseWhere->addCondition(
      sprintf(
        '%s = :id',
        $queryBuilder->dialect->quoteIdentifier('id')
      )
    );
    $queryBuilder->statement->clauseWhere->assembly();
    $queryBuilder->statement->assembly();

    /** @var int $updatedUnixTimestamp Текущее время в UNIX-формате */
    $updatedUnixTimestamp = time();

    try {
      $databaseConnection = $this->CMSCore->databaseConnector->database->connection;
      $databaseQuery = $databaseConnection->prepare($queryBuilder->statement->assembled);
      
      foreach ($data as $name => $value) {
        if (!in_array($name, ['id', 'createdUnixTimestamp', 'updatedUnixTimestamp', 'metadata'])) {
          $valueTypeName = gettype($value);
          $valueType = match ($valueTypeName) {
            'boolean' => \PDO::PARAM_BOOL,
            'integer' => \PDO::PARAM_INT,
            'string' => \PDO::PARAM_STR,
            'null' => \PDO::PARAM_NULL,
          };

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

    return $execute ? true : false;
  }
}