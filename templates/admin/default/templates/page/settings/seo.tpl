<form class="form form_settings form_settings-seo">
  <table class="table table_settings">
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_PERMANENT_REDIRECT_WWW_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_PERMANENT_REDIRECT_WWW_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_permanent_redirect_www_status" id="I1474308101" value="{SETTING_PERMANENT_REDIRECT_WWW_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308801" type="checkbox" {SETTING_PERMANENT_REDIRECT_WWW_CHECKED_VALUE} data-status-block="I1474308101">
            <label class="checkbox-container__label form__label" for="I1474308801"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SEO_SITE_DESCRIPTION_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SEO_SITE_DESCRIPTION_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_site_description" id="" cols="30" rows="10" placeholder="{LANG:PAGE_SETTINGS_SETTING_SEO_SITE_DESCRIPTION_TITLE}">{SETTING_SITE_DESCRIPTION_VALUE}</textarea>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SEO_KEYWORDS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SEO_KEYWORDS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_site_keywords" id="" cols="30" rows="10" placeholder="{LANG:PAGE_SETTINGS_SETTING_SEO_INPUT_KEYWORDS_PLACEHOLDER}">{SETTING_SITE_KEYWORDS_VALUE}</textarea>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SEO_ROBOTS_TXT_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SEO_ROBOTS_TXT_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_robots_txt" id="" cols="30" rows="10">{SETTING_SITE_ROBOTS_TXT_VALUE}</textarea>
        </div>
      </td>
    </tr>
  </table>
  <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
</form>