<form class="form page__form" data-element="main-form">
  <div class="grid-table page__grid-table">
    <!-- Поле: Загрузка аватара -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_UPLOAD_AVATAR_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_UPLOAD_AVATAR_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_upload_avatar_status" id="I1474308126" value="{SETTING_USERS_UPLOAD_AVATAR_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308640" type="checkbox" {SETTING_USERS_UPLOAD_AVATAR_CHECKED_VALUE} data-status-block="I1474308126">
        <label class="checkbox-container__label form__label" for="I1474308640"></label>
      </div>
    </div>
    <!-- Поле: Максимальная длина логина -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_LENGTH_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_LENGTH_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_login_length_max" type="number" class="input form__input form__input_number" value="{SETTING_LOGIN_LENGTH_MAX_VALUE}" placeholder="64" min="0" data-element="input-login-length-max">
    </div>
    <!-- Поле: Минимальная длина логина -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_LENGTH_MIN_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_LENGTH_MIN_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_login_length_min" type="number" class="input form__input form__input_number" value="{SETTING_LOGIN_LENGTH_MIN_VALUE}" placeholder="4" min="0" data-element="input-login-length-min">
    </div>
    <!-- Поле: Загрузка аватара -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_EDIT_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_EDIT_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_login_edit_status" id="I1474308123" value="{SETTING_USERS_LOGIN_EDIT_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308637" type="checkbox" {SETTING_USERS_LOGIN_EDIT_CHECKED_VALUE} data-status-block="I1474308123">
        <label class="checkbox-container__label form__label" for="I1474308637"></label>
      </div>
    </div>
    <!-- Поле: Специальные символы в логине -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_login_special_symbols_status" id="I1474308125" value="{SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308639" type="checkbox" {SETTING_USERS_LOGIN_SPECIAL_SYMBOLS_CHECKED_VALUE} data-status-block="I1474308125">
        <label class="checkbox-container__label form__label" for="I1474308639"></label>
      </div>
    </div>
    <!-- Поле: Учет регистра символов в логине -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_login_register_accounting_status" id="I1474308128" value="{SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308642" type="checkbox" {SETTING_USERS_LOGIN_REGISTER_ACCOUNTING_CHECKED_VALUE} data-status-block="I1474308128">
        <label class="checkbox-container__label form__label" for="I1474308642"></label>
      </div>
    </div>
    <!-- Поле: Максимальная длина пароля -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_PASSWORD_LENGTH_MAX_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_PASSWORD_LENGTH_MAX_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_password_length_max" type="number" class="input form__input form__input_number" value="{SETTING_PASSWORD_LENGTH_MAX_VALUE}" placeholder="24" min="6" data-element="input-password-length-max">
    </div>
    <!-- Поле: Минимальная длина пароля -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_PASSWORD_LENGTH_MIN_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_PASSWORD_LENGTH_MIN_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_password_length_min" type="number" class="input form__input form__input_number" value="{SETTING_PASSWORD_LENGTH_MAX_VALUE}" placeholder="6" min="6" data-element="input-password-length-min">
    </div>
    <!-- Поле: Специальные символы в пароле -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_password_special_symbols_status" id="I1474308124" value="{SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308638" type="checkbox" {SETTING_USERS_PASSWORD_SPECIAL_SYMBOLS_CHECKED_VALUE} data-status-block="I1474308124">
        <label class="checkbox-container__label form__label" for="I1474308638"></label>
      </div>
    </div>
    <!-- Поле: Фильтрация логинов -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGINS_BLACKLIST_STATUS_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGINS_BLACKLIST_STATUS_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_logins_blacklist_status" id="I1474308127" value="{SETTING_USERS_LOGINS_BLACKLIST_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308641" type="checkbox" {SETTING_USERS_LOGINS_BLACKLIST_CHECKED_VALUE} data-status-block="I1474308127">
        <label class="checkbox-container__label form__label" for="I1474308641"></label>
      </div>
    </div>
    <!-- Поле: Минимальная длина пароля -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGINS_BLACKLIST_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_USERS_LOGINS_BLACKLIST_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" name="setting_{SETTINGS_NAME}_logins_blacklist" cols="30" rows="10" placeholder="admin, cms_girvas, cms, girvas">{SETTING_LOGINS_BLACKLIST_VALUE}</textarea>
    </div>
    <!-- Раздел: Дополнительные поля -->
    <div class="cell grid-table__cell grid-table__cell_header">
      {LANG:PAGE_SETTINGS_SETTING_USERS_ADDITIONAL_FIELDS_TITLE}
    </div>
    <div class="cell grid-table__cell grid-table__cell_text"></div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="additional-fields-locale"></div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_text"></div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="button-add-field"></div>
    </div>
    <!-- Панель формы -->
    <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
  </div>
</form>