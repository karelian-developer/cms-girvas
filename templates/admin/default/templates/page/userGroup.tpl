<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_USERS_GROUP_TITLE}</h1>
    <div id="E8548530785" class="page__interactive-container"></div>
  </div>
  <div class="page__content">
    <form class="form form_user-group" action="/handler/usersGroup">
      <input name="user_group_id" type="hidden" value="{USER_GROUP_ID}" required>
      <table class="table">
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USERS_GROUP_TITLE_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USERS_GROUP_TITLE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_group_title_rus" typde="text" class="form__input form__input_text" value="{USER_GROUP_TITLE}" placeholder="My Group" role="usersGroupTitle" required></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USERS_GROUP_TECHNICAL_NAME_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USERS_GROUP_TECHNICAL_NAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_group_name" typde="text" class="form__input form__input_text" value="{USER_GROUP_NAME}" placeholder="my-group" role="usersGroupName" required></div>
          </td>
        </tr>
        <tr class="table__row">
          <th class="table__cell table__cell_header" colspan="2">{LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_ADMIN_TITLE}</th>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_AUTHORIZATION_IN_AP_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308970" name="user_group_permissions[]" type="checkbox" value="admin_panel_auth" {USER_GROUP_PERMISSION_ADMIN_PANEL_AUTH_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308970"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_USERS_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308972" name="user_group_permissions[]" type="checkbox" value="admin_users_management" {USER_GROUP_PERMISSION_ADMIN_USERS_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308972"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_USERS_GROUPS_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308982" name="user_group_permissions[]" type="checkbox" value="admin_users_groups_management" {USER_GROUP_PERMISSION_ADMIN_USERS_GROUPS_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308982"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_TEMPLATES_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308974" name="user_group_permissions[]" type="checkbox" value="admin_templates_management" {USER_GROUP_PERMISSION_ADMIN_TEMPLATES_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308974"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_MODULES_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308453" name="user_group_permissions[]" type="checkbox" value="admin_modules_management" {USER_GROUP_PERMISSION_ADMIN_MODULES_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308453"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_FEEDS_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308977" name="user_group_permissions[]" type="checkbox" value="admin_feeds_management" {USER_GROUP_PERMISSION_ADMIN_FEEDS_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308977"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_CMS_SETTINGS_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308975" name="user_group_permissions[]" type="checkbox" value="admin_settings_management" {USER_GROUP_PERMISSION_ADMIN_SETTINGS_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308975"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_CMS_REPORTS_VIEW_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308976" name="user_group_permissions[]" type="checkbox" value="admin_viewing_logs" {USER_GROUP_PERMISSION_ADMIN_VIEWING_LOGS_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308976"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <th class="table__cell table__cell_header" colspan="2">{LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_MODERATOR_TITLE}</th>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_BAN_USERS_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308971" name="user_group_permissions[]" type="checkbox" value="moder_users_ban" {USER_GROUP_PERMISSION_MODER_USERS_BAN_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308971"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_COMMENTS_ENTRIES_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308980" name="user_group_permissions[]" type="checkbox" value="moder_entries_comments_management" {USER_GROUP_PERMISSION_MODER_ENTRIES_COMMENTS_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308980"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_GIVE_WARNINGS_FROM_USERS_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308978" name="user_group_permissions[]" type="checkbox" value="moder_users_warns" {USER_GROUP_PERMISSION_MODER_USERS_WARNS_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308978"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <th class="table__cell table__cell_header" colspan="2">{LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_EDITOR_TITLE}</th>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_EDITING_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308979" name="user_group_permissions[]" type="checkbox" value="editor_entries_edit" {USER_GROUP_PERMISSION_EDITOR_ENTRIES_EDIT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308979"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_CATEGORIES_EDITING_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308983" name="user_group_permissions[]" type="checkbox" value="editor_entries_categories_edit" {USER_GROUP_PERMISSION_EDITOR_ENTRIES_CATEGORIES_EDIT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308983"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_STATIC_PAGES_EDITING_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308981" name="user_group_permissions[]" type="checkbox" value="editor_pages_static_edit" {USER_GROUP_PERMISSION_EDITOR_PAGES_STATIC_EDIT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308981"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_MANAGE_MEDIA_FILES_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308985" name="user_group_permissions[]" type="checkbox" value="editor_media_files_management" {USER_GROUP_PERMISSION_EDITOR_MEDIA_FILES_MANAGEMENT_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308985"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <th class="table__cell table__cell_header" colspan="2">{LANG:PAGE_USERS_GROUP_PERSMISSIONS_GROUP_BASE_TITLE}</th>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_COMMENTS_CREATING_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308984" name="user_group_permissions[]" type="checkbox" value="base_entry_comment_create" {USER_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CREATE_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308984"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_COMMENTS_CHANGING_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308988" name="user_group_permissions[]" type="checkbox" value="base_entry_comment_change" {USER_GROUP_PERMISSION_BASE_ENTRY_COMMENT_CHANGE_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308988"></label>
            </div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell">{LANG:PAGE_USERS_GROUP_PERSMISSION_ENTRIES_COMMENTS_RATING_CHANGING_TITLE}</td>
          <td class="table__cell">
            <div class="form__checkbox-container checkbox-container">
              <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308933" name="user_group_permissions[]" type="checkbox" value="base_entry_comment_rate" {USER_GROUP_PERMISSION_BASE_ENTRY_COMMENT_RATE_VALUE}>
              <label class="checkbox-container__label form__label" for="I1474308933"></label>
            </div>
          </td>
        </tr>
      </table>
      <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_USERS_GROUP_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_USERS_GROUP_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_USERS_GROUP_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </article>
</aside>