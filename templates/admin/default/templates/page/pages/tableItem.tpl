<div class="grid-table__item grid-table__item_page" data-is-published="{PAGE_STATIC_PUBLISHED_STATUS}">
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/page/{PAGE_STATIC_ID}" target="_blank">{PAGE_STATIC_TITLE}</a>
    <span class="grid-table__draft">черновик</span>
    <span class="grid-table__id">ID: {PAGE_STATIC_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_description">
    {PAGE_STATIC_DESCRIPTION}
  </div>
  <div class="grid-table__cell grid-table__cell_locales">
    <span class="grid-table__locales-title">Локализации</span>
    <ul class="grid-table__locales">
      {PAGE_STATIC_LOCALES_LIST}
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_category">
        <span class="grid-table__metadata-label">Категория</span>
        <span class="grid-table__metadata-value">{PAGE_STATIC_CATEGORY_TITLE}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_author">
        <span class="grid-table__metadata-label">Автор</span>
        <span class="grid-table__metadata-value">{PAGE_STATIC_AUTHOR_LOGIN}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_seo">
        <span class="grid-table__metadata-label">SEO</span>
        <span class="grid-table__metadata-value">{PAGE_STATIC_SEO_STATUS}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{PAGE_STATIC_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-published">
        <span class="grid-table__metadata-label">Дата публикации</span><br>
        <span class="grid-table__metadata-value">{PAGE_STATIC_PUBLISHED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{PAGE_STATIC_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_stat">
        <a href="/admin/analytics/page/{PAGE_STATIC_ID}" class="grid-table__panel-link">Статистика</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/page/{PAGE_STATIC_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove" data-params="pageStaticID={PAGE_STATIC_ID}">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_view">
        <a href="{PAGE_STATIC_URL}" class="grid-table__panel-link">Посмотреть на сайте</a>
      </li>
    </ul>
  </div>
</div>