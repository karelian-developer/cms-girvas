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
  </table>
  <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
</form>