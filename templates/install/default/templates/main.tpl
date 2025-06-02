<main class="main">
  <section class="main__section section">
    <div class="main__container container">
      <div class="main__title-container title-container">
        <div class="title-container__step-container" role="installer-step-data">
          <span class="title-container__step-label" role="installer-step-label">{LANG:INSTALLER_STAGE}</span>
          <span class="title-container__step-number" role="installer-step-number">1</span>
        </div>
        <h2 class="main__title" role="installer-step-title">{LANG:INSTALLER_STAGE_ACQUAINTANCE_TITLE}</h2>
      </div>
      <div class="main__page-container">
        <div class="main__page page" style="display: none;" role="language-page">
          {LANG:MD:STAGE_LANGUAGE_SELECT}
          <div role="language-select"></div>
        </div>
        <div class="main__page page" data-page-index="0">
          {LANG:MD:STAGE_ABOUT_ACQUAINTANCE}
        </div>
        <div class="main__page page" data-page-index="1">
          {LANG:MD:STAGE_COMPATIBILITY_CHECK}
        </div>
        <div class="main__page page" data-page-index="2">
          {LANG:MD:STAGE_CHECKING_INTEGRITY}
        </div>
        <div class="main__page page" data-page-index="3">
          {LANG:MD:STAGE_CHECKING_ACCESS_RIGHTS}
        </div>
        <div class="main__page page" data-page-index="4">
          {LANG:MD:STAGE_CHECKING_PDO_DRIVERS}
        </div>
        <div class="main__page page" data-page-index="5">
          {LANG:MD:STAGE_GENERATING_LOCAL_CONFIGURATIONS}
          <div class="page__phar">
            <form class="form" role="form-database">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_NAME_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_NAME_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="domain" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="example.ru">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_ALIASES_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_ALIASES_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <textarea name="domain_aliases" class="form__textarea"></textarea>
                    <input name="domain_aliases" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="localhost, 127.0.0.1, ...">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_NAME_FOR_COOKIE_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_NAME_FOR_COOKIE_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="domain_cookies" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="example.ru">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_NAME_FOR_EMAIL_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_DOMAIN_NAME_FOR_EMAIL_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="domain_email" type="text" class="form__input" value="{CONFIGURATION_DATABASE_DOMAIN}" placeholder="example.ru">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_USING_SSL_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_USING_SSL_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div class="page__phar-block">
                      <div class="form__checkbox-container checkbox-container">
                        <input type="hidden" name="domain_ssl" id="I1474308110" value="off">
                        <input class="checkbox-container__input form__input form__input_checkbox" id="I1474301200" name="domain_ssl_status" type="checkbox" data-status-block="I1474308110">
                        <label class="checkbox-container__label form__label" for="I1474301200"></label>
                      </div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_SMDB_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_SMDB_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <select name="database_dms">
                      <option value="DMS::PostgreSQL">PostgreSQL</option>
                    </select>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_PREFIX_DATABASE_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_PREFIX_DATABASE_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_prefix" type="text" class="form__input" value="{CONFIGURATION_DATABASE_PREFIX}" placeholder="base">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_SCHEME_DATABASE_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCAL_CONFIGURATION_SCHEME_DATABASE_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_scheme" type="text" class="form__input" value="{CONFIGURATION_DATABASE_SCHEME}" placeholder="public">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_HOST_DATABASE_LABEL}</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_host" type="text" class="form__input" value="{CONFIGURATION_DATABASE_HOST}" placeholder="127.0.0.1">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_USER_DATABASE_LABEL}</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_user" type="text" class="form__input" value="{CONFIGURATION_DATABASE_USER}" placeholder="db_user">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_PASSWORD_DATABASE_LABEL}</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_pass" type="password" class="form__input" value="{CONFIGURATION_DATABASE_PASSWORD}" placeholder="db_pass">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCAL_CONFIGURATION_NAME_DATABASE_LABEL}</div>
                  </td>
                  <td class="table__cell cell">
                    <input name="database_name" type="text" class="form__input" value="{CONFIGURATION_DATABASE_NAME}" placeholder="db_name">
                  </td>
                </tr>
              </table>
            </form>
          </div>
        </div>
        <div class="main__page page" data-page-index="6">
          {LANG:MD:STAGE_GENERATING_DATABASE_TABLES}
          <p class="page__phar" role="cms-dms-tables-generate"></p>
        </div>
        <div class="main__page page" data-page-index="7">
          {LANG:MD:STAGE_CONFIGURING_LOCALIZATION_AND_TIME}
          <div class="page__phar">
            <form class="form" role="form-locale">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCALIZATION_AND_TIMEZONE_CONFIGURATION_LANGUAGE_SITE_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCALIZATION_AND_TIMEZONE_CONFIGURATION_LANGUAGE_SITE_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div id="E85485302311" class="page__interactive-container"></div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCALIZATION_AND_TIMEZONE_CONFIGURATION_LANGUAGE_AP_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCALIZATION_AND_TIMEZONE_CONFIGURATION_LANGUAGE_AP_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div id="E85485302312" class="page__interactive-container"></div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_LOCALIZATION_AND_TIMEZONE_CONFIGURATION_LANGUAGE_TIMEZONE_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_LOCALIZATION_AND_TIMEZONE_CONFIGURATION_LANGUAGE_TIMEZONE_DESCRIPTION}</div>
                    </div>
                  </td>
                  <td class="table__cell cell">
                    <div id="E85485302313" class="page__interactive-container"></div>
                  </td>
                </tr>
              </table>
            </form>
          </div>
          <p class="page__phar" role="cms-locale-and-timezone"></p>
        </div>
        <div class="main__page page" data-page-index="8">
          {LANG:MD:STAGE_WEBSITE_METADATA}
          <div class="page__phar">
            <form class="form" role="form-metadata">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_TITLE_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_TITLE_DESCRIPTION}</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="site_title" type="text" class="form__input" value="{SITE_TITLE_VALUE}" placeholder="{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_TITLE_PLACEHOLDER_LABEL}">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_DESCRIPTION_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_DESCRIPTION_DESCRIPTION}</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <textarea name="site_description" id="" cols="30" rows="10" class="form__textarea" placeholder="{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_DESCRIPTION_PLACEHOLDER_LABEL}">{SITE_DESCRIPTION_VALUE}</textarea>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_KEYWORDS_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_KEYWORDS_DESCRIPTION}</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <textarea class="form__textarea" name="site_keywords" id="" cols="30" rows="10" placeholder="{LANG:INSTALLER_SITE_METADATA_CONFIGURATION_KEYWORDS_PLACEHOLDER_LABEL}">{SITE_KEYWORDS_VALUE}</textarea>
                  </td>
                </tr>
              </table>
            </form>
          </div>
          <p class="page__phar" role="cms-metadata"></p>
        </div>
        <div class="main__page page" data-page-index="9">
          {LANG:MD:STAGE_CREATING_AN_ADMINISTRATOR_ACCOUNT}
          <div class="page__phar">
            <form class="form" role="form-admin-create">
              <table class="table">
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_LOGIN_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_LOGIN_DESCRIPTION}</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_login" type="text" class="form__input" value="" placeholder="ILoveThisCMS_97">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_EMAIL_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_EMAIL_DESCRIPTION}</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_email" type="email" class="form__input" value="" placeholder="admin@domain.com">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_PASSWORD_LABEL}</div>
                    <div class="cell__description">
                      <div class="page__phar-block">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_PASSWORD_DESCRIPTION}</div>
                    </div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_password" type="password" class="form__input" value="" placeholder="">
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <div class="cell__title">{LANG:INSTALLER_ADMINISTRATOR_ACCOUNT_CONFIGURATION_PASSWORD_REPEAT_LABEL}</div>
                  </td>
                </tr>
                <tr class="table__row">
                  <td class="table__cell cell">
                    <input name="admin_password_repeat" type="password" class="form__input" value="" placeholder="">
                  </td>
                </tr>
              </table>
            </form>
          </div>
          <p class="page__phar" role="cms-admin-create"></p>
        </div>
        <div class="main__page page" data-page-index="10">
          {LANG:MD:STAGE_GENERATING_A_SECRET_KEY}
          <p class="page__phar" role="cms-secret-key"></p>
        </div>
        <div class="main__page page" data-page-index="11">
          {LANG:MD:STAGE_FINISHING}
        </div>
        <div class="main__page-panel-container panel-container" role="installation-buttons-panel"></div>
      </div>
    </div>
  </section>
</main>