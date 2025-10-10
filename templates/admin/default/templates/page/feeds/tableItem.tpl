<div class="grid-table__item grid-table__item_feed" data-element="feed" data-id="{FEED_ID}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/feed/{FEED_ID}" target="_blank">{FEED_TITLE}</a>
    <span class="grid-table__id">ID: {FEED_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {FEED_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {FEED_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_category">
        <span class="grid-table__metadata-label">Категория</span>
        <span class="grid-table__metadata-value">{FEED_CATEGORY_TITLE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_specification">
        <span class="grid-table__metadata-label">Спецификация</span>
        <span class="grid-table__metadata-value">{FEED_SPECIFICATION_TITLE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{FEED_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{FEED_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/feed/{FEED_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_view">
        <a href="/feed/{FEED_NAME}" class="grid-table__panel-link" target="_blank">Посмотреть на сайте</a>
      </li>
    </ul>
  </div>
</div>