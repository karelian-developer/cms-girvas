<form class="form page__form" data-element="main-form">
  <div class="grid-table page__grid-table">
    <!-- Поле: Код для верификации в сервисе «Яндекс: Вебмастер» -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_SEO_CODE_YANDEX_WEBMASTER_TITLE}
      </div>
      <div class="cell__description">
        {LANG:MD:PAGE_SETTINGS_SETTING_SEO_CODE_YANDEX_WEBMASTER_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input class="input form__input form__input_text" name="setting_{SETTINGS_NAME}_code_yandex_webmaster" placeholder="b0dbc8c312c05273" value="{SETTING_CODE_YANDEX_WEBMASTER_VALUE}">
    </div>
    <!-- Поле: Принудительная переадресация на поддомен WWW -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_SEO_PERMANENT_REDIRECT_WWW_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_SEO_PERMANENT_REDIRECT_WWW_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <div class="form__checkbox-container checkbox-container">
        <input type="hidden" name="setting_{SETTINGS_NAME}_permanent_redirect_www_status" id="I1474308101" value="{SETTING_PERMANENT_REDIRECT_WWW_STATUS_VALUE}">
        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474308801" type="checkbox" {SETTING_PERMANENT_REDIRECT_WWW_CHECKED_VALUE} data-status-block="I1474308101">
        <label class="checkbox-container__label form__label" for="I1474308801"></label>
      </div>
    </div>
    <!-- Поле: Описание сайта -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_SEO_SITE_DESCRIPTION_TITLE}
      </div>
      <div class="cell__description">
        {LANG:MD:PAGE_SETTINGS_SETTING_SEO_SITE_DESCRIPTION_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" name="setting_{SETTINGS_NAME}_site_description" cols="30" rows="10" placeholder="{LANG:PAGE_SETTINGS_SETTING_SEO_SITE_DESCRIPTION_TITLE}">{SETTING_SITE_DESCRIPTION_VALUE}</textarea>
    </div>
    <!-- Поле: Ключевые слова -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_SEO_KEYWORDS_TITLE}
      </div>
      <div class="cell__description">
        {LANG:MD:PAGE_SETTINGS_SETTING_SEO_KEYWORDS_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" name="setting_{SETTINGS_NAME}_site_keywords" cols="30" rows="10" placeholder="{LANG:PAGE_SETTINGS_SETTING_SEO_INPUT_KEYWORDS_PLACEHOLDER}">{SETTING_SITE_KEYWORDS_VALUE}</textarea>
    </div>
    <!-- Поле: Содержимое файла robots.txt -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_SEO_ROBOTS_TXT_TITLE}
      </div>
      <div class="cell__description">
        {LANG:MD:PAGE_SETTINGS_SETTING_SEO_ROBOTS_TXT_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" name="setting_{SETTINGS_NAME}_robots_txt" cols="30" rows="10">{SETTING_SITE_ROBOTS_TXT_VALUE}</textarea>
    </div>
    <!-- Панель формы -->
    <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
  </div>
</form>