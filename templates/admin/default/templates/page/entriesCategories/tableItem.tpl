<div class="grid-table__item grid-table__item_entries-category" data-id="{ENTRIES_CATEGORY_ID}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/entriesCategory/{ENTRIES_CATEGORY_ID}" target="_blank">{ENTRIES_CATEGORY_TITLE}</a>
    <span class="grid-table__id">ID: {ENTRIES_CATEGORY_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {ENTRIES_CATEGORY_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {ENTRIES_CATEGORY_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_parent-category">
        <span class="grid-table__metadata-label">Род. категория</span>
        <span class="grid-table__metadata-value">{ENTRY_CATEGORY_PARENT_TITLE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_count-entries">
        <span class="grid-table__metadata-label">Кол-во записей</span>
        <span class="grid-table__metadata-value">{ENTRIES_CATEGORY_ENTRIES_COUNT}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{ENTRIES_CATEGORY_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{ENTRIES_CATEGORY_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/entriesCategory/{ENTRIES_CATEGORY_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
    </ul>
  </div>
</div>