<div class="grid-table__item grid-table__item_entry" data-is-published="{ENTRY_PUBLISHED_STATUS}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/entry/{ENTRY_ID}" target="_blank">{ENTRY_TITLE}</a>
    <span class="grid-table__draft">черновик</span>
    <span class="grid-table__id">ID: {ENTRY_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {ENTRY_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {ENTRY_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_category">
        <span class="grid-table__metadata-label">Категория</span>
        <span class="grid-table__metadata-value">{ENTRY_CATEGORY_TITLE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_author">
        <span class="grid-table__metadata-label">Автор</span>
        <span class="grid-table__metadata-value">{ENTRY_AUTHOR_LOGIN}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span>
        <span class="grid-table__metadata-value">{ENTRY_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-published">
        <span class="grid-table__metadata-label">Дата публикации</span>
        <span class="grid-table__metadata-value">{ENTRY_PUBLISHED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span>
        <span class="grid-table__metadata-value">{ENTRY_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item">
        <a href="/admin/analytics/entry/{ENTRY_ID}" class="grid-table__panel-link">Статистика</a>
      </li>
      <li class="grid-table__panel-item">
        <a href="/admin/entry/{ENTRY_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item" data-event="remove" data-params="entryID={ENTRY_ID}">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
      <li class="grid-table__panel-item">
        <a href="{ENTRY_URL}" class="grid-table__panel-link">Посмотреть на сайте</a>
      </li>
    </ul>
  </div>
</div>