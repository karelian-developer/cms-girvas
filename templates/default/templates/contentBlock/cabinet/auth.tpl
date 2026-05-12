<form action="/handler/utils/authorization?method=base" class="form mini-cabinet__form mini-cabinet__form_auth" method="POST">
  <input class="form__input form__input_text" type="text" name="user_login" autocomplete="username" required placeholder="User2141">
  <input class="form__input form__input_password" type="password" name="user_password" autocomplete="current-password" required placeholder="&bull;&bull;&bull;&bull;&bull;&bull;&bull;&bull;">
  <div class="form__input-container input-container input-container_flex-checkbox">
    <input type="checkbox" name="user_remember_me" id="">
    <div class="input-container__label label">{LANG:DEFAULT_TEXT_USER_AUTHORIZATION_REMEMBER_ME}</div>
  </div>
  <input class="form__input form__input_submit" type="submit" value="Авторизоваться">
</form>