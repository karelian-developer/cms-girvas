<div class="grid-table__item grid-table__item_consent" data-element="consent" data-id="{CONSENT_ID}">
  <div class="grid-table__cell grid-table__cell_id">
    <span class="grid-table__id">ID: {CONSENT_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_user">
    <a class="grid-table__link" href="/admin/user/{USER_ID}" target="_blank">{USER_LOGIN}</a>
    <span class="grid-table__id">ID: {USER_ID}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_document">
    <a class="grid-table__link" href="/admin/page-static/{PAGE_STATIC_ID}" target="_blank">{DOCUMENT_TITLE}</a>
    <span class="grid-table__version">v. {DOCUMENT_VERSION}</span>
  </div>
  <div class="grid-table__cell grid-table__cell_locale">
    {LOCALE_TITLE}
  </div>
  <div class="grid-table__cell grid-table__cell_source">
    {SOURCE_LABEL}
  </div>
  <div class="grid-table__cell grid-table__cell_status">
    {STATUS_LABEL}
  </div>
  <div class="grid-table__cell grid-table__cell_date">
    {CONSENTED_DATE}
  </div>
  <div class="grid-table__cell grid-table__cell_ip">
    {IP}
  </div>
  <div class="grid-table__cell grid-table__cell_user-agent">
    {USER_AGENT_SHORT}
  </div>
  <div class="grid-table__cell grid-table__cell_panel" data-element="panel">
    <!-- Кнопка отзыва — только если есть право -->
    {REVOKE_BUTTON}
  </div>
</div>