<form class="form page__form" data-element="main-form">
  <div class="grid-table page__grid-table">
    <!-- Поле: Хост SMTP-сервера -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_HOST_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_HOST_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_host" type="text" class="input form__input form__input_text" value="{SETTING_SMTP_HOST_VALUE}" placeholder="smtp.example.ru" data-element="input-host" required>
    </div>
    <!-- Поле: Порт SMTP-сервера -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PORT_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PORT_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_port" type="number" class="input form__input form__input_number" value="{SETTING_SMTP_PORT_VALUE}" placeholder="465" min="0" data-element="input-port" required>
    </div>
    <!-- Поле: Имя пользователя SMTP-клиента -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_USERNAME_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_USERNAME_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_username" type="text" class="input form__input form__input_text" value="{SETTING_SMTP_USERNAME_VALUE}" placeholder="User2315" data-element="input-user" required>
    </div>
    <!-- Поле: Пароль пользователя SMTP-клиента -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PASSWORD_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PASSWORD_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_password" type="password" class="input form__input form__input_password" value="{SETTING_SMTP_PASSWORD_VALUE}" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" data-element="input-password" required cmsg-password-checker>
    </div>
    <!-- Поле: Домен отправителя для системы -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_DOMAIN_TITLE}
      </div>
      <div class="cell__description">
        {LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_DOMAIN_DESCRIPTION}
      </div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_domain" type="text" class="input form__input form__input_text" value="{SETTING_SMTP_DOMAIN_VALUE}" placeholder="example.ru" data-element="input-domain" required>
    </div>
    <!-- Панель формы -->
    <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
  </div>
</form>