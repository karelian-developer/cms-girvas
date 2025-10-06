<div class="grid-table__item grid-table__item_entry-comment" data-element="entry-comment" data-is-hidden="{COMMENT_IS_HIDDEN_STATUS}" data-id="{COMMENT_ID}">
  <div class="grid-table__cell grid-table__cell_content">
    {COMMENT_CONTENT}
    <span class="grid-table__id">ID: {COMMENT_ID}</span>
    <span class="grid-table__hidden">Скрыто по причине: {COMMENT_HIDDEN_REASON}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_entry-title">
        <span class="grid-table__metadata-label">Запись</span>
        <span class="grid-table__metadata-value">{COMMENT_ENTRY_TITLE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_author">
        <span class="grid-table__metadata-label">Автор</span>
        <span class="grid-table__metadata-value">{COMMENT_AUTHOR_LOGIN}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{COMMENT_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{COMMENT_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit" data-event="show">
        <a href="#" class="grid-table__panel-link">Опубликовать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_edit" data-event="hide">
        <a href="#" class="grid-table__panel-link">Снять с публикации</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
    </ul>
  </div>
</div>