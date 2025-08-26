<form class="form form_settings form_settings-email">
  <table class="table table_settings">
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_HOST_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_HOST_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <input name="setting_{SETTINGS_NAME}_smtp_host" type="string" class="form__input form__input_text" placeholder="smtp.example.ru" value="{SETTING_SMTP_HOST_VALUE}" required>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PORT_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PORT_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <input name="setting_{SETTINGS_NAME}_smtp_port" type="number" class="form__input form__input_number" placeholder="465" value="{SETTING_SMTP_PORT_VALUE}" min="0" required>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_USERNAME_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_USERNAME_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <input name="setting_{SETTINGS_NAME}_smtp_username" type="text" class="form__input form__input_text" placeholder="Username" value="{SETTING_SMTP_USERNAME_VALUE}" required>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PASSWORD_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_PASSWORD_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <input name="setting_{SETTINGS_NAME}_smtp_password" type="password" class="form__input form__input_password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" value="{SETTING_SMTP_PASSWORD_VALUE}" cmsg-password-checker required>
        </div>
      </td>
    </tr>
    <tr class="table__row">
      <td class="table__cell cell">
        <div class="cell__title">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_DOMAIN_TITLE}</div>
        <div class="cell__description">
          <div class="page__phar-block">{LANG:PAGE_SETTINGS_SETTING_EMAIL_SMTP_DOMAIN_DESCRIPTION}</div>
        </div>
      </td>
      <td class="table__cell cell">
        <div class="page__phar-block">
          <input name="setting_{SETTINGS_NAME}_smtp_domain" type="text" class="form__input form__input_text" placeholder="example.ru" value="{SETTING_SMTP_DOMAIN_VALUE}" required>
        </div>
      </td>
    </tr>
  </table>
  <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
</form>