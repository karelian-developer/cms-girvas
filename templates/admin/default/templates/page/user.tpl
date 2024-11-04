<article class="main__page page page_{ADMIN_PANEL_PAGE_NAME}">
  {PAGE_NAVIGATION}
  <div class="page__title-container">
    <h1 class="page__title">{LANG:PAGE_USER_TITLE}</h1>
  </div>
  <div class="page__content">
    <form class="form form_user" action="/handler/user">
      <input name="user_id" type="hidden" value="{USER_ID}" required>
      <table id="SYSTEM_F0648538231" class="table table_user">
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_LOGIN_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_LOGIN_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_login" type="text" class="form__input user-editor-form__input" value="{USER_LOGIN}" placeholder="user2311" required role="userFormInputLogin"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_EMAIL_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_EMAIL_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_email" type="email" class="form__input user-editor-form__input" value="{USER_EMAIL}" placeholder="user@mail.ru" required role="userFormInputEmail"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_PASSWORD_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_PASSWORD_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_password" type="password" class="form__input user-editor-form__input" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" role="userFormInputUserPassword"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_PASSWORD_REPEAT_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_PASSWORD_REPEAT_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_password_repeat" type="password" class="form__input user-editor-form__input" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" role="userFormInputUserPasswordRepeat"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_NAME_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_NAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_name" type="text" class="form__input user-editor-form__input" value="{USER_NAME}" placeholder="Ivan" role="userFormInputName"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_SURNAME_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_SURNAME_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_surname" type="text" class="form__input user-editor-form__input" value="{USER_SURNAME}" placeholder="Ivanov" role="userFormInputSurname"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_PATRONYMIC_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_PATRONYMIC_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_patronymic" type="text" class="form__input user-editor-form__input" value="{USER_PATRONYMIC}" placeholder="Ivanovich" role="userFormInputPatronymic"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_BIRTHDATE_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_BIRTHDATE_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div class="page__phar-block"><input name="user_birthdate" type="date" class="form__input user-editor-form__input" value="{USER_BIRTHDATE}" role="userFormInputBirthdate" min="{USER_BIRTHDATE_MINIMUM}" max="{USER_BIRTHDATE_MAXIMUM}"></div>
          </td>
        </tr>
        <tr class="table__row">
          <td class="table__cell cell">
            <div class="cell__title">{LANG:PAGE_USER_USER_GROUP_TITLE}</div>
            <div class="cell__description">
              <div class="page__phar-block">{LANG:PAGE_USER_USER_GROUP_DESCRIPTION}</div>
            </div>
          </td>
          <td class="table__cell cell">
            <div id="SYSTEM_E6372840180" class="page__phar-block"></div>
          </td>
        </tr>
        {USER_ADDITIONAL_FIELDS}
      </table>
      <div class="form__bottom-panel" id="SYSTEM_E3724126170"></div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <article class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_USER_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_USER_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_USER_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </article>
</aside>