<div class="grid-table__item grid-table__item_form" data-element="form" data-id="{FORM_ID}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/form/{FORM_ID}" target="_blank">{FORM_TITLE}</a>
    <span class="grid-table__id">ID: {FORM_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {FORM_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {FORM_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_method">
        <span class="grid-table__metadata-label">Метод</span>
        <span class="grid-table__metadata-value">{FORM_METHOD}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{FORM_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{FORM_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/form/{FORM_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
    </ul>
  </div>
</div>