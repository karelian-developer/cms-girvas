<div class="grid-table__item grid-table__item_user" data-element="user" data-id="{USER_ID}">
  <div class="grid-table__cell grid-table__cell_avatar">
    <a class="grid-table__link" href="/admin/user/{USER_ID}" target="_blank">
      <img class="grid-table__avatar" src="{USER_AVATAR_URL}" alt="{USER_LOGIN}">
    </a>
  </div>
  <div class="grid-table__cell grid-table__cell_title">
    <a class="grid-table__link" href="/admin/user/{USER_ID}" target="_blank">{USER_LOGIN}</a>
    <span class="grid-table__id">ID: {USER_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_data">
    <ul class="grid-table__data-list">
      <li class="grid-table__data grid-table__data-group">
        <span class="grid-table__data-label">Группа</span>
        <span class="grid-table__data-value">{USER_GROUP_TITLE}</span>
      </li>
      <li class="grid-table__data grid-table__data-email">
        <span class="grid-table__data-label">Электронная почта</span>
        <a class="grid-table__data-value" href="mailto:{USER_EMAIL}">{USER_EMAIL}</a>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_metadata">
    <ul class="grid-table__metadata-list">
      <li class="grid-table__metadata grid-table__metadata_ip-registration">
        <span class="grid-table__metadata-label">IP регистрации</span>
        <span class="grid-table__metadata-value">{USER_REGISTRATION_IP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_status-label">
        <span class="grid-table__metadata-label">Статус</span>
        <span class="grid-table__metadata-value">{USER_STATUS_LABEL}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-created">
        <span class="grid-table__metadata-label">Дата создания</span><br>
        <span class="grid-table__metadata-value">{USER_CREATED_DATE_TIMESTAMP}</span>
      </li>
      <li class="grid-table__metadata grid-table__metadata_date-updated">
        <span class="grid-table__metadata-label">Дата обновления</span><br>
        <span class="grid-table__metadata-value">{USER_UPDATED_DATE_TIMESTAMP}</span>
      </li>
    </ul>
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <ul class="grid-table__panel-list">
      <li class="grid-table__panel-item grid-table__panel-item_edit">
        <a href="/admin/user/{USER_ID}" class="grid-table__panel-link">Редактировать</a>
      </li>
      <li class="grid-table__panel-item grid-table__panel-item_remove" data-event="remove">
        <a href="#" class="grid-table__panel-link grid-table__panel-link_red">Удалить</a>
      </li>
    </ul>
  </div>
</div>