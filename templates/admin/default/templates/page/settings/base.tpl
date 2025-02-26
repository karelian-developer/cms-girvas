<form class="form form_settings form_settings-base">
  <table class="table table_settings">
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_TITLE_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_TITLE_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block"><input name="setting_{SETTINGS_NAME}_site_title" type="text" class="form__input form__input_text" placeholder="{LANG:PAGE_SETTINGS_SETTING_BASE_TITLE_PLACEHOLDER}" value="{SETTING_SITE_TITLE_VALUE}" required></div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_CHARSET_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_CHARSET_DESCRIPTION}</div>
        </div>
      </td>
      <td id="TC6474389682" class="table__cell cell"></td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_TIMEZONE_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_TIMEZONE_DESCRIPTION}</div>
        </div>
      </td>
      <td id="TC6474389679" class="table__cell cell"></td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_SITE_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_SITE_DESCRIPTION}</div>
        </div>
      </td>
      <td id="TC6474389680" class="table__cell cell"></td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_ADMIN_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_ADMIN_DESCRIPTION}</div>
        </div>
      </td>
      <td id="TC6474389681" class="table__cell cell"></td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_ENGINEERING_WORK_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_ENGINEERING_WORK_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_engineering_works_status" id="I1474308110" value="{SETTING_ENGINEERING_WORKS_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308800" name="setting_{SETTINGS_NAME}_engineering_works_status" type="checkbox" {SETTING_ENGINEERING_WORKS_CHECKED_VALUE} data-logic-block="I1474308810" data-status-block="I1474308110">
            <label class="checkbox-container__label form__label" for="I1474308800"></label>
          </div>
        </div>
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_engineering_works_text" id="I1474308810" cols="30" rows="10" placeholder="{LANG:PAGE_SETTINGS_SETTING_BASE_ENGINEERING_WORK_PLACEHOLDER}">{SETTING_ENGINEERING_WORKS_TEXT_VALUE}</textarea>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell cell_additional-fields-title-container" colspan="2">
        <div class="cell__title">
          <div class="cell__title-value">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_SECTIONS_TITLE}</div>
          <div role="profile-additional-fields-locale"></div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_ENTRIES_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_ENTRIES_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_entries_status" id="I1474301200" value="{SETTING_SECTION_ENTRIES_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301300" name="setting_{SETTINGS_NAME}_section_entries_status" type="checkbox" {SETTING_SECTION_ENTRIES_CHECKED_VALUE} data-status-block="I1474301200">
            <label class="checkbox-container__label form__label" for="I1474301300"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_STATIC_PAGES_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_STATIC_PAGES_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_static_pages_status" id="I1474301201" value="{SETTING_SECTION_STATIC_PAGES_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301301" name="setting_{SETTINGS_NAME}_section_static_pages_status" type="checkbox" {SETTING_SECTION_STATIC_PAGES_CHECKED_VALUE} data-status-block="I1474301201">
            <label class="checkbox-container__label form__label" for="I1474301301"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_MODULES_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_MODULES_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_modules_status" id="I1474301202" value="{SETTING_SECTION_MODULES_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301302" name="setting_{SETTINGS_NAME}_section_modules_status" type="checkbox" {SETTING_SECTION_MODULES_CHECKED_VALUE} data-status-block="I1474301202">
            <label class="checkbox-container__label form__label" for="I1474301302"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_TEMPLATES_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_TEMPLATES_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_templates_status" id="I1474301203" value="{SETTING_SECTION_TEMPLATES_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301303" name="setting_{SETTINGS_NAME}_section_templates_status" type="checkbox" {SETTING_SECTION_TEMPLATES_CHECKED_VALUE} data-status-block="I1474301203">
            <label class="checkbox-container__label form__label" for="I1474301303"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_USERS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_USERS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_users_status" id="I1474301204" value="{SETTING_SECTION_USERS_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301304" name="setting_{SETTINGS_NAME}_section_users_status" type="checkbox" {SETTING_SECTION_USERS_CHECKED_VALUE} data-status-block="I1474301204">
            <label class="checkbox-container__label form__label" for="I1474301304"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_MEDIA_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_MEDIA_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_media_status" id="I1474301205" value="{SETTING_SECTION_MEDIA_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301305" name="setting_{SETTINGS_NAME}_section_media_status" type="checkbox" {SETTING_SECTION_MEDIA_CHECKED_VALUE} data-status-block="I1474301205">
            <label class="checkbox-container__label form__label" for="I1474301305"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_FEEDS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_FEEDS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_feeds_status" id="I1474301206" value="{SETTING_SECTION_FEEDS_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301306" name="setting_{SETTINGS_NAME}_section_feeds_status" type="checkbox" {SETTING_SECTION_FEEDS_CHECKED_VALUE} data-status-block="I1474301206">
            <label class="checkbox-container__label form__label" for="I1474301306"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_ANALYTICS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_BASE_SECTION_ANALYTICS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_section_analytics_status" id="I1474301207" value="{SETTING_SECTION_ANALYTICS_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301307" name="setting_{SETTINGS_NAME}_section_analytics_status" type="checkbox" {SETTING_SECTION_ANALYTICS_CHECKED_VALUE} data-status-block="I1474301207">
            <label class="checkbox-container__label form__label" for="I1474301307"></label>
          </div>
        </div>
      </td>
    </tr>
  </table>
  <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
</form>