<!-- Верхний ряд: Общая статистика + Топ пользователей -->
<div class="reports-grid_top">
  
  <!-- Общая статистика -->
  <section class="report-section report-section_overview">
    <h2 class="report-section__title">{LANG:PAGE_REPORTS_SECTION_OVERVIEW_TITLE}</h2>
    <ul class="report-section__list">
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_TOTAL_ACTIONS}</span>
        <span class="report-section__data-value">{TOTAL_ACTIONS}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_CONTENT_ACTIONS}</span>
        <span class="report-section__data-value">{TOTAL_CONTENT_ACTIONS}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_SECURITY_ACTIONS}</span>
        <span class="report-section__data-value">{TOTAL_SECURITY_ACTIONS}</span>
      </li>
    </ul>
  </section>
  
  <!-- Топ активных пользователей -->
  <section class="report-section report-section_top-users">
    <h2 class="report-section__title">{LANG:PAGE_REPORTS_SECTION_TOP_USERS_TITLE}</h2>
    <ul class="report-section__list">
      {TOP_USERS}
    </ul>
  </section>
</div>

<!-- Средний ряд: Контент | Пользователи | Безопасность -->
<div class="reports-grid_middle">
  
  <!-- Контент -->
  <section class="report-section report-section_content">
    <h2 class="report-section__title">{LANG:PAGE_REPORTS_SECTION_CONTENT_TITLE}</h2>
    <ul class="report-section__list">
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_ENTRIES_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_ENTRIES_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_ENTRIES_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_ENTRIES_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_ENTRIES_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_ENTRIES_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_PAGES_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_PAGES_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_PAGES_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_PAGES_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_PAGES_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_PAGES_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_MEDIA_UPLOADED}</span>
        <span class="report-section__data-value">{CONTENT_MEDIA_UPLOADED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_MEDIA_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_MEDIA_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_CATEGORIES_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_CATEGORIES_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_CATEGORIES_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_CATEGORIES_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_CATEGORIES_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_CATEGORIES_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_SAMPLES_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_SAMPLES_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_SAMPLES_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_SAMPLES_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_SAMPLES_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_SAMPLES_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_FORMS_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_FORMS_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_FORMS_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_FORMS_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_FORMS_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_FORMS_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_COMMENTS_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_COMMENTS_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_COMMENTS_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_COMMENTS_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_COMMENTS_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_COMMENTS_DELETED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_BLOCKS_CREATED}</span>
        <span class="report-section__data-value">{CONTENT_BLOCKS_CREATED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_BLOCKS_EDITED}</span>
        <span class="report-section__data-value">{CONTENT_BLOCKS_EDITED}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_CONTENT_STATS_BLOCKS_DELETED}</span>
        <span class="report-section__data-value">{CONTENT_BLOCKS_DELETED}</span>
      </li>
    </ul>
  </section>
  
  <!-- Раздел: Пользователи -->
  <div class="report-section">
    <h3 class="report-section__title">{LANG:PAGE_REPORTS_BASE_SECTION_USERS_TITLE}</h3>
    <ul class="report-section__list">
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_REGISTERED_SITE}</span>
        <span class="report-section__list-value">{USERS_REGISTERED_SITE}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_REGISTERED_ADMIN}</span>
        <span class="report-section__list-value">{USERS_REGISTERED_ADMIN}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_EDITED}</span>
        <span class="report-section__list-value">{USERS_EDITED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_DELETED}</span>
        <span class="report-section__list-value">{USERS_DELETED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_BANNED}</span>
        <span class="report-section__list-value">{USERS_BANNED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_UNBANNED}</span>
        <span class="report-section__list-value">{USERS_UNBANNED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_PERSONAL_DATA_VIEWS}</span>
        <span class="report-section__list-value">{USERS_PERSONAL_DATA_VIEWS}</span>
      </li>
    </ul>
  </div>

  <!-- Раздел: Согласия (152-ФЗ) -->
  <div class="report-section">
    <h3 class="report-section__title">{LANG:PAGE_REPORTS_BASE_SECTION_CONSENTS_TITLE}</h3>
    <ul class="report-section__list">
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_CONSENTS_GIVEN}</span>
        <span class="report-section__list-value">{USERS_CONSENTS_GIVEN}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_CONSENTS_REVOKED}</span>
        <span class="report-section__list-value">{USERS_CONSENTS_REVOKED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_SUBJECT_DATA_EXPORTED}</span>
        <span class="report-section__list-value">{USERS_SUBJECT_DATA_EXPORTED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_ANONYMIZED}</span>
        <span class="report-section__list-value">{USERS_ANONYMIZED}</span>
      </li>
      <li class="report-section__list-item">
        <span class="report-section__list-label">{LANG:PAGE_REPORTS_BASE_USERS_REPORTS_ROTATED}</span>
        <span class="report-section__list-value">{USERS_REPORTS_ROTATED}</span>
      </li>
    </ul>
  </div>
  
  <!-- Безопасность -->
  <section class="report-section report-section_security">
    <h2 class="report-section__title">{LANG:PAGE_REPORTS_SECTION_SECURITY_TITLE}</h2>
    <ul class="report-section__list">
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_AUTH_SUCCESS_ADMIN}</span>
        <span class="report-section__data-value">{SECURITY_AUTH_SUCCESS_ADMIN}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_AUTH_FAIL_ADMIN}</span>
        <span class="report-section__data-value">{SECURITY_AUTH_FAIL_ADMIN}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_AUTH_SUCCESS_SITE}</span>
        <span class="report-section__data-value">{SECURITY_AUTH_SUCCESS_SITE}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_AUTH_FAIL_SITE}</span>
        <span class="report-section__data-value">{SECURITY_AUTH_FAIL_SITE}</span>
      </li>
      <li>
        <span class="report-section__data-label">{LANG:PAGE_REPORTS_STATS_LOGS_VIEWS}</span>
        <span class="report-section__data-value">{SECURITY_LOGS_VIEWS}</span>
      </li>
    </ul>
  </section>
  
</div>

<!-- Нижний ряд: Последние события -->
<div class="reports-grid_bottom">
  <section class="report-section report-section_recent">
    <h2 class="report-section__title">{LANG:PAGE_REPORTS_SECTION_RECENT_TITLE}</h2>
    <ul class="report-section__list">
      {RECENT_EVENTS}
    </ul>
  </section>
</div>
