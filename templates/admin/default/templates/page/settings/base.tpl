<form class="form page__form" data-element="main-form">
  <input type="hidden" name="_settings_locale" value="{SETTINGS_ADMIN_LOCALE}">
  <div class="grid-table page__grid-table">
    <!-- Поле: Название сайта -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_BASE_TITLE_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_BASE_TITLE_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_site_title" type="text" class="input form__input form__input_text" value="{SETTING_SITE_TITLE_VALUE}" placeholder="{LANG:PAGE_SETTINGS_SETTING_BASE_TITLE_PLACEHOLDER}" data-element="input-title" required>
    </div>
    <!-- Поле: Кодировка сайта -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_BASE_CHARSET_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_BASE_CHARSET_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="choice" data-choice="charset"></div>
    </div>
    <!-- Поле: Временная зона -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_BASE_TIMEZONE_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_BASE_TIMEZONE_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="choice" data-choice="timezone"></div>
    </div>
    <!-- Поле: Основная локализация сайта -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_SITE_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_SITE_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="choice" data-choice="locale-site"></div>
    </div>
    <!-- Поле: Основная локализация административной панели -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_ADMIN_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_BASE_LOCALE_ADMIN_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div data-element="choice" data-choice="locale-admin"></div>
    </div>
    <!-- Поле: Технические работы -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_BASE_ENGINEERING_WORK_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_BASE_ENGINEERING_WORK_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_engineering_works_status" id="I1474308110" value="{SETTING_ENGINEERING_WORKS_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308800" name="setting_{SETTINGS_NAME}_engineering_works_status" type="checkbox" {SETTING_ENGINEERING_WORKS_CHECKED_VALUE} data-logic-block="I1474308810" data-status-block="I1474308110">
        <label class="checkbox-container__label form__label" for="I1474308800"></label>
      </div>
      <textarea name="setting_{SETTINGS_NAME}_engineering_works_text" class="textarea form__textarea" id="I1474308810" cols="30" rows="10" placeholder="{LANG:PAGE_SETTINGS_SETTING_BASE_ENGINEERING_WORK_PLACEHOLDER}" data-element="input-engineering-works-text">{SETTING_ENGINEERING_WORKS_TEXT_VALUE}</textarea>
    </div>
    <!-- Панель формы -->
    <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
  </div>
</form>