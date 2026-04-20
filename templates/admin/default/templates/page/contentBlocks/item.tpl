<div class="grid-table__item grid-table__item_content-block" data-element="content-block" data-id="{CONTENT_BLOCK_ID}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/contentBlock/{CONTENT_BLOCK_ID}" target="_blank">{CONTENT_BLOCK_TITLE}</a>
    <span class="grid-table__id">ID: {CONTENT_BLOCK_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {CONTENT_BLOCK_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {CONTENT_BLOCK_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_method">
        <span class="grid-table__metadata-label">Тип контента</span>
        <span class="grid-table__metadata-value">{CONTENT_BLOCK_TYPE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{CONTENT_BLOCK_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{CONTENT_BLOCK_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/contentBlock/{CONTENT_BLOCK_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
    </ul>
  </div>
</div>