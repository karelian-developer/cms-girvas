<div class="grid-table__item grid-table__item_entries-sample" data-element="entries-sample" data-id="{ENTRIES_SAMPLE_ID}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/entriesSample/{ENTRIES_SAMPLE_ID}" target="_blank">{ENTRIES_SAMPLE_TITLE}</a>
    <span class="grid-table__id">ID: {ENTRIES_SAMPLE_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {ENTRIES_SAMPLE_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {ENTRIES_SAMPLE_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_categories">
    <span class="grid-table__categories-title">Категории</span>
    <ul class="grid-table__categories">
      {ENTRIES_SAMPLE_CATEGORIES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_method-sort">
        <span class="grid-table__metadata-label">Способ сортировки</span>
        <span class="grid-table__metadata-value">{ENTRIES_SAMPLE_METHOD_SORT_LABEL}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_limit-count">
        <span class="grid-table__metadata-label">Лимит записей</span>
        <span class="grid-table__metadata-value">{ENTRIES_SAMPLE_ENTRIES_LIMIT_COUNT}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{ENTRIES_SAMPLE_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{ENTRIES_SAMPLE_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/entriesSample/{ENTRIES_SAMPLE_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
    </ul>
  </div>
</div>