<article class="page__article article">
  <div class="page__article-header" style="background-image: url('{SITE_TEMPLATE_URL}/images/entry/default_1024.png');">
    <div class="page__buttons-panel" data-role="profile-panel-buttons"></div>
    <h2 class="article__title">{USER_LOGIN}</h2>
  </div>
  <div class="article__content">
    <div class="profile article__profile profile_edit">
      <div class="profile__avatar-container">
        <img class="profile__avatar" src="{USER_AVATAR_URL}" alt="{USER_LOGIN}" data-role="button-user-avatar-imitate">
      </div>
      <div class="profile__information-container">
        <form id="SYSTEM_F0648538312" class="profile__form form form_profile" method="PATCH" action="/handler/user">
          <input type="hidden" name="user_id" value="{USER_ID}">
          <table class="table profile__information-table">
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_LOGIN_TITLE}</th>
              <td class="table__cell">
                <input name="user_login" type="text" class="form__input" value="{USER_LOGIN}" placeholder="User3425" required disabled  data-role="input-user-login">
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_EMAIL_TITLE}</th>
              <td class="table__cell">
                <input name="user_email" type="email" class="form__input" value="{USER_EMAIL}" placeholder="user3425@mail.com" required  data-role="input-user-email">
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_NAME_TITLE}</th>
              <td class="table__cell">
                <input name="user_name" type="text" class="form__input" value="{USER_NAME}" placeholder="Иван" data-role="input-user-name">
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_SURNAME_TITLE}</th>
              <td class="table__cell">
                <input name="user_surname" type="text" class="form__input" value="{USER_SURNAME}" placeholder="Иванов" data-role="input-user-surname">
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_PATRONYMIC_TITLE}</th>
              <td class="table__cell">
                <input name="user_patronymic" type="text" class="form__input" value="{USER_PATRONYMIC}" placeholder="Иванович" data-role="input-user-patronymic">
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_BIRTHDATE_TITLE}</th>
              <td class="table__cell">
                <input name="user_birthdate" type="date" class="form__input" value="{USER_BIRTHDATE}" placeholder="19.01.1997" data-role="input-user-birthday">
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header" colspan="2">{LANG:PAGE_PROFILE_SECURITY_GROUP_TITLE}</th>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_PASSWORD_OLD_TITLE}</th>
              <td class="table__cell">
                <div class="form__input-container">
                  <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
                  <input name="user_password_old" type="password" class="form__input form__input_password" value="" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" data-role="input-user-password-old">
                </div>
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_PASSWORD_TITLE}</th>
              <td class="table__cell">
                <div class="form__input-container">
                  <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
                  <input name="user_password" type="password" class="form__input form__input_password" value="" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" data-role="input-user-password" cmsg-password-checker>
                </div>
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_PASSWORD_REPEAT_TITLE}</th>
              <td class="table__cell">
                <div class="form__input-container">
                  <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
                  <input name="user_password_repeat" type="password" class="form__input form__input_password" value="" placeholder="&bull;&bull;&bull;&bull;&bull;&bull;" data-role="input-user-password-repeat">
                </div>
              </td>
            </tr>
            <tr class="table__row">
              <th class="table__cell table__cell_header" colspan="2">{LANG:PAGE_PROFILE_ADDITIONAL_FIELDS_GROUP_TITLE}</th>
            </tr>
            {PROFILE_ADDITIONAL_FIELDS}
          </table>
          <div class="profile__form-panel" data-role="profile-form-panel">
            <button class="form__button form__input_submit">{LANG:DEFAULT_TEXT_SAVE}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</article>
<aside class="sidebar" role="siteSidebarRight">
  <div class="sidebar__block">
    <h2 class="sidebar__block-title">{LANG:DEFAULT_INDEX_SIDEBAR_BLOCK_EXAMPLE_TITLE}</h2>
    <div class="sidebar__block-content">{LANG:DEFAULT_INDEX_SIDEBAR_BLOCK_EXAMPLE_DESCRIPTION}</div>
  </div>
</aside>