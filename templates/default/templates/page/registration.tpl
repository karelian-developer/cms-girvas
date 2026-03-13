<article class="page__article article" data-entry-id="{ENTRY_ID}" role="entry">
  <div class="page__article-header">
    <h2 class="article__title">{LANG:PAGE_REGISTRATION_TITLE}</h2>
    <div class="article__metadata metadata"></div>
  </div>
  <div class="article__content">
    <form action="/handler/utils/registration" class="form form_registration">
      <div class="form__input-container input-container">
        <input class="form__input form__input_text" type="text" name="user_login" placeholder="UserName" required>
      </div>
      <div class="form__input-container input-container">
        <input class="form__input form__input_email" type="email" name="user_email" placeholder="username@example.ru" required>
      </div>
      <div class="form__input-container input-container">
        <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
        <input class="form__input form__input_password" type="password" name="user_password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" cmsg-password-checker required>
      </div>
      <div class="form__input-container input-container">
        <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
        <input class="form__input form__input_password" type="password" name="user_password_repeat" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" required>
      </div>
      <div class="form__input-container input-container input-container_flex-checkbox">
        <input name="user_agreement" type="checkbox" class="form__input form__input_checkbox" required>
        <div class="input-container__label label">{LANG:DEFAULT_TEXT_USER_REGISTRATION_AGREEMENT}</div>
      </div>
      <div class="form__input-container input-container">
        <input class="form__input form__input_submit" type="submit" value="{LANG:BUTTON_REGISTRATION_LABEL}">
      </div>
    </form>
  </div>
</article>
<aside class="sidebar" role="siteSidebarRight">
  <div class="sidebar__block block">
    <h2 class="sidebar__block-title">{LANG:DEFAULT_INDEX_SIDEBAR_BLOCK_EXAMPLE_TITLE}</h2>
    <div class="sidebar__block-content">{LANG:DEFAULT_INDEX_SIDEBAR_BLOCK_EXAMPLE_DESCRIPTION}</div>
  </div>
</aside>