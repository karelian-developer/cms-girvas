<article class="page__article article" data-entry-id="{ENTRY_ID}" role="entry">
  <div class="page__article-header">
    <h2 class="article__title">Регистрация на сайте</h2>
    <div class="article__metadata metadata"></div>
  </div>
  <div class="article__content">
    <form action="/handler/utils/registration" class="form form_registration">
      <div class="form__input-container input-container">
        <input class="form__input form__input_text" type="text" name="user_login" placeholder="User2311">
      </div>
      <div class="form__input-container input-container">
        <input class="form__input form__input_email" type="email" name="user_email" placeholder="User2311@mail.com">
      </div>
      <div class="form__input-container input-container">
        <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
        <input class="form__input form__input_password" type="password" name="user_password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" cmsg-password-checker>
      </div>
      <div class="form__input-container input-container">
        <div class="form__input-tip input-tip" role="passwords-show">{LANG:BUTTON_SHOW_HIDE_LABEL}</div>
        <input class="form__input form__input_password" type="password" name="user_password_repeat" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;">
      </div>
      <div class="form__input-container input-container">
        <input class="form__input form__input_submit" type="submit" value="Регистрация">
      </div>
    </form>
  </div>
</article>
<aside class="sidebar" role="siteSidebarRight">
  <div class="sidebar__block block">
    <h2 class="block__title title">{LANG:DEFAULT_INDEX_SIDEBAR_BLOCK_EXAMPLE_TITLE}</h2>
    <div class="block__content content">{LANG:DEFAULT_INDEX_SIDEBAR_BLOCK_EXAMPLE_DESCRIPTION}</div>
  </div>
</aside>