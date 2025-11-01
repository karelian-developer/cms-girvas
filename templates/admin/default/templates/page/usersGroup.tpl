<article class="page main__page main__page_users-group">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_USERS_GROUP_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/usersGroup" data-element="main-form">
      <input name="user_group_id" type="hidden" value="{USERS_GROUP_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Техническое наименование -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_TECHNICAL_NAME_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USERS_GROUP_TECHNICAL_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_group_name" type="text" class="input form__input form__input_text" value="{USERS_GROUP_NAME}" placeholder="my-group" data-element="input-name" required>
        </div>
        <!-- Поле: Заголовок -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_TITLE_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USERS_GROUP_TITLE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_group_title_rus" type="text" class="input form__input form__input_text" value="{USERS_GROUP_TITLE}" placeholder="{PAGE_USERS_GROUP_TITLE_PLACEHOLDER}" data-element="input-title" required>
        </div>
        <!-- Раздел: Административные права -->
        <div class="cell grid-table__cell grid-table__cell_header">
          {LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_ADMIN_TITLE}
        </div>
        <!-- Поле: Авторизация в административной панели -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_AUTHORIZATION_IN_AP_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308970" name="user_group_permissions[]" type="checkbox" value="admin_panel_auth" {USERS_GROUP_PERMISSION_ADMIN_PANEL_AUTH_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308970"></label>
          </div>
        </div>
        <!-- Поле: Управление пользователями -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_USERS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308972" name="user_group_permissions[]" type="checkbox" value="admin_users_management" {USERS_GROUP_PERMISSION_ADMIN_USERS_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308972"></label>
          </div>
        </div>
        <!-- Поле: Управление группами пользователей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_USERS_GROUPS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308982" name="user_group_permissions[]" type="checkbox" value="admin_users_groups_management" {USERS_GROUP_PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308982"></label>
          </div>
        </div>
        <!-- Поле: Управление темами CMS -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_THEMES_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308974" name="user_group_permissions[]" type="checkbox" value="admin_templates_management" {USERS_GROUP_PERMISSION_ADMIN_TEMPLATES_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308974"></label>
          </div>
        </div>
        <!-- Поле: Управление модулями CMS -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_MODULES_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308453" name="user_group_permissions[]" type="checkbox" value="admin_modules_management" {USERS_GROUP_PERMISSION_ADMIN_MODULES_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308453"></label>
          </div>
        </div>
        <!-- Поле: Управление фидами CMS -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_FEEDS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308977" name="user_group_permissions[]" type="checkbox" value="admin_feeds_management" {USERS_GROUP_PERMISSION_ADMIN_FEEDS_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308977"></label>
          </div>
        </div>
        <!-- Поле: Управление формами CMS -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_FORMS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308912" name="user_group_permissions[]" type="checkbox" value="admin_forms_management" {USERS_GROUP_PERMISSION_ADMIN_FORMS_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308912"></label>
          </div>
        </div>
        <!-- Поле: Управление настройками CMS -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_SETTINGS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308975" name="user_group_permissions[]" type="checkbox" value="admin_settings_management" {USERS_GROUP_PERMISSION_ADMIN_SETTINGS_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308975"></label>
          </div>
        </div>
        <!-- Поле: Просмотр отчетов CMS -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_CMS_REPORTS_VIEW_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308976" name="user_group_permissions[]" type="checkbox" value="admin_viewing_logs" {USERS_GROUP_PERMISSION_ADMIN_VIEWING_LOGS_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308976"></label>
          </div>
        </div>
        <!-- Раздел: Права модерации -->
        <div class="cell grid-table__cell grid-table__cell_header">
          {LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_MODERATOR_TITLE}
        </div>
        <!-- Поле: Блокировка пользователей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_BAN_USERS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308971" name="user_group_permissions[]" type="checkbox" value="moder_users_ban" {USERS_GROUP_PERMISSION_MODER_USERS_BAN_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308971"></label>
          </div>
        </div>
        <!-- Поле: Управление комментариями к записям -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_COMMENTS_ENTRIES_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308980" name="user_group_permissions[]" type="checkbox" value="moder_entries_comments_management" {USERS_GROUP_PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308980"></label>
          </div>
        </div>
        <!-- Поле: Выдача предупреждений пользователям -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_GIVE_WARNINGS_FROM_USERS_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308978" name="user_group_permissions[]" type="checkbox" value="moder_users_warns" {USERS_GROUP_PERMISSION_MODER_USERS_WARNS_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308978"></label>
          </div>
        </div>
        <!-- Раздел: Права редакции -->
        <div class="cell grid-table__cell grid-table__cell_header">
          {LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_EDITOR_TITLE}
        </div>
        <!-- Поле: Редактирование записей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_EDITING_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308979" name="user_group_permissions[]" type="checkbox" value="editor_entries_edit" {USERS_GROUP_PERMISSION_EDITOR_ENTRIES_EDIT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308979"></label>
          </div>
        </div>
        <!-- Поле: Редактирование категорий записей -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_CATEGORIES_EDITING_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308983" name="user_group_permissions[]" type="checkbox" value="editor_entries_categories_edit" {USERS_GROUP_PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308983"></label>
          </div>
        </div>
        <!-- Поле: Редактирование статических страниц -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_STATIC_PAGES_EDITING_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308981" name="user_group_permissions[]" type="checkbox" value="editor_pages_static_edit" {USERS_GROUP_PERMISSION_EDITOR_PAGES_STATIC_EDIT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308981"></label>
          </div>
        </div>
        <!-- Поле: Управление медиа-файлами -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_MEDIA_FILES_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308985" name="user_group_permissions[]" type="checkbox" value="editor_media_files_management" {USERS_GROUP_PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308985"></label>
          </div>
        </div>
        <!-- Раздел: Базовые права -->
        <div class="cell grid-table__cell grid-table__cell_header">
          {LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_BASE_TITLE}
        </div>
        <!-- Поле: Создание новых комментариев -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_COMMENTS_CREATING_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308984" name="user_group_permissions[]" type="checkbox" value="base_entry_comment_create" {USERS_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CREATE_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308984"></label>
          </div>
        </div>
        <!-- Поле: Изменение собственных комментариев -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_COMMENTS_CHANGING_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308988" name="user_group_permissions[]" type="checkbox" value="base_entry_comment_change" {USERS_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CHANGE_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308988"></label>
          </div>
        </div>
        <!-- Поле: Изменение рейтинга комментариев (за исключением своих) -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_COMMENTS_RATING_CHANGING_TITLE}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div class="form__checkbox-container checkbox-container">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308933" name="user_group_permissions[]" type="checkbox" value="base_entry_comment_rate" {USERS_GROUP_PERMISSION_BASE_ENTRY_COMMENT_RATE_VALUE}>
            <label class="checkbox-container__label form__label" for="I1474308933"></label>
          </div>
        </div>
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_USERS_GROUP_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_USERS_GROUP_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_USERS_GROUP_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </div>
</aside>