<form class="form page__form" data-element="main-form">
  <div class="page__interactive-container" data-element="header-interactive"></div>

  <input name="setting_smtp_email_texts" type="hidden" value="" data-element="smtp-email-texts-hidden">

  <div class="grid-table page__grid-table">
    <!-- ===== SMTP-подключение ===== -->
    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_HOST_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_HOST_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_host" type="text" class="input form__input form__input_text" value="{SETTING_SMTP_HOST_VALUE}" placeholder="smtp.example.ru" data-element="input-host" required>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PORT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PORT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_port" type="number" class="input form__input form__input_number" value="{SETTING_SMTP_PORT_VALUE}" placeholder="465" min="0" data-element="input-port" required>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_USERNAME_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_USERNAME_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_username" type="text" class="input form__input form__input_text" value="{SETTING_SMTP_USERNAME_VALUE}" placeholder="User2315" data-element="input-user" required>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PASSWORD_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PASSWORD_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_password" type="password" class="input form__input form__input_password" value="{SETTING_SMTP_PASSWORD_VALUE}" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" data-element="input-password" required cmsg-password-checker>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_DOMAIN_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_DOMAIN_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input name="setting_{SETTINGS_NAME}_smtp_domain" type="text" class="input form__input form__input_text" value="{SETTING_SMTP_DOMAIN_VALUE}" placeholder="example.ru" data-element="input-domain" required>
    </div>

    <!-- ===== Письмо: Регистрация ===== -->
    <div class="cell grid-table__cell grid-table__cell_header">
      {LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_TITLE}
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_SUBJECT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_SUBJECT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="registrationTitle" value="">
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_CONTENT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_CONTENT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" rows="8" data-element="smtp-email-text-input" data-email-key="registrationContent"></textarea>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_COPYRIGHT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_REGISTRATION_COPYRIGHT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="registrationCopyright" value="">
    </div>

    <!-- ===== Письмо: Сброс пароля ===== -->
    <div class="cell grid-table__cell grid-table__cell_header">
      {LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_TITLE}
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_SUBJECT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_SUBJECT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="passwordResetTitle" value="">
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_CONTENT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_CONTENT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" rows="8" data-element="smtp-email-text-input" data-email-key="passwordResetContent"></textarea>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_COPYRIGHT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESET_COPYRIGHT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="passwordResetCopyright" value="">
    </div>

    <!-- ===== Письмо: Новый пароль после сброса ===== -->
    <div class="cell grid-table__cell grid-table__cell_header">
      {LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_TITLE}
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_SUBJECT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_SUBJECT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="passwordResetedTitle" value="">
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_CONTENT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_CONTENT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" rows="8" data-element="smtp-email-text-input" data-email-key="passwordResetedContent"></textarea>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_COPYRIGHT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_PASSWORD_RESETED_COPYRIGHT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="passwordResetedCopyright" value="">
    </div>

    <!-- ===== Письмо: Тестовое ===== -->
    <div class="cell grid-table__cell grid-table__cell_header">
      {LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_TITLE}
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_SUBJECT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_SUBJECT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="testTitle" value="">
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_CONTENT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_CONTENT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <textarea class="textarea form__textarea" rows="8" data-element="smtp-email-text-input" data-email-key="testContent"></textarea>
    </div>

    <div class="cell grid-table__cell grid-table__cell_text">
      <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_COPYRIGHT_TITLE}</div>
      <div class="cell__description">{LANG:PAGE_SETTINGS_SETTING_EMAIL_TEST_COPYRIGHT_DESCRIPTION}</div>
    </div>
    <div class="cell grid-table__cell grid-table__cell_data">
      <input type="text" class="input form__input form__input_text" data-element="smtp-email-text-input" data-email-key="testCopyright" value="">
    </div>

    <!-- Панель формы -->
    <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
  </div>
</form>