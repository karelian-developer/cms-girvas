<form class="form form_settings form_settings-security">
  <table class="table table_settings">
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_REGISTRATION_USERS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_REGISTRATION_USERS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_allowed_users_registration_status" id="I1474308115" value="{SETTING_ALLOWED_USERS_REGISTRATION_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308805" type="checkbox" {SETTING_ALLOWED_USERS_REGISTRATION_CHECKED_VALUE} data-status-block="I1474308115">
            <label class="checkbox-container__label form__label" for="I1474308805"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_RESTRICTING_ACCESS_TO_AP_BY_IP_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_RESTRICTING_ACCESS_TO_AP_BY_IP_DESCRIPTION_1}</div>
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_RESTRICTING_ACCESS_TO_AP_BY_IP_DESCRIPTION_2}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_allowed_admin_ip_status" id="I1474308110" value="{SETTING_ALLOWED_IP_ADMIN_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308800" type="checkbox" {SETTING_ALLOWED_IP_ADMIN_CHECKED_VALUE} data-logic-block="I1474308810" data-status-block="I1474308110">
            <label class="checkbox-container__label form__label" for="I1474308800"></label>
          </div>
        </div>
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_allowed_admin_ip" id="I1474308810" cols="30" rows="10" placeholder="127.0.0.1, 93.190.204.73">{SETTING_ALLOWED_IP_ADMIN_VALUE}</textarea>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_FILTER_REGISTRATION_BY_EMAIL_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_FILTER_REGISTRATION_BY_EMAIL_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_allowed_emails_status" id="I1474308111" value="{SETTING_ALLOWED_EMAILS_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308801" name="setting_{SETTINGS_NAME}_allowed_emails_status" type="checkbox" {SETTING_ALLOWED_EMAILS_CHECKED_VALUE} data-logic-block="I1474308811" data-status-block="I1474308111">
            <label class="checkbox-container__label form__label" for="I1474308801"></label>
          </div>
        </div>
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_allowed_emails" id="I1474308811" cols="30" rows="10" placeholder="yandex.ru, rambler.ru">{SETTING_ALLOWED_EMAILS_VALUE}</textarea>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_PREMODERATION_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_PREMODERATION_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_premoderation_create_status" id="I1474308112" value="{SETTING_PREMODERATION_CREATE_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308802" type="checkbox" {SETTING_PREMODERATION_CREATE_CHECKED_VALUE} data-status-block="I1474308112">
            <label class="checkbox-container__label form__label" for="I1474308802"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_NEGATIVE_THRESHOLD_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_NEGATIVE_THRESHOLD_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <input class="form__input form__input_number" type="number" name="setting_{SETTINGS_NAME}_negative_evaluation_threshold" id="I1474308813" placeholder="-6" value="{SETTING_NEGATIVE_EVALUATION_THRESHOLD_VALUE}">
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_PREMODERATION_FILTER_BY_EXTERNAL_LINKS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_PREMODERATION_FILTER_BY_EXTERNAL_LINKS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_premoderation_links_filter_status" id="I1474308113" value="{SETTING_PREMODERATION_LINKS_FILTER_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308803" type="checkbox" {SETTING_PREMODERATION_LINKS_FILTER_CHECKED_VALUE} data-status-block="I1474308113">
            <label class="checkbox-container__label form__label" for="I1474308803"></label>
          </div>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_PREMODERATION_FILTER_BY_WORDS_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_SECURITY_ENTRY_COMMENTS_PREMODERATION_FILTER_BY_WORDS_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <div class="form__checkbox-container checkbox-container">
            <input type="hidden" name="setting_{SETTINGS_NAME}_premoderation_words_filter_status" id="I1474308114" value="{SETTING_PREMODERATION_WORDS_FILTER_STATUS_VALUE}">
            <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308804" type="checkbox" {SETTING_PREMODERATION_WORDS_FILTER_CHECKED_VALUE} data-logic-block="I1474308815" data-status-block="I1474308114">
            <label class="checkbox-container__label form__label" for="I1474308804"></label>
          </div>
        </div>
        <div class="page__phar-block">
          <textarea class="form__textarea" name="setting_{SETTINGS_NAME}_premoderation_words_filter_list" id="I1474308815" cols="30" rows="10" placeholder="слово1, целая фраза, слово2">{SETTING_PREMODERATION_WORDS_FILTER_LIST_VALUE}</textarea>
        </div>
      </td>
    </tr>
  </table>
  <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
</form>