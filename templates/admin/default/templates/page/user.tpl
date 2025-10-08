<article class="page main__page main__page_user">
  <nav id="SYSTEM_AP_SUBNAVIGATION" class="page__navigation navigation"></nav>
  <div class="page__title-container">
    <h1 class="page__title">
      {LANG:PAGE_USER_TITLE}
    </h1>
    <div class="page__interactive-container" data-element="header-interactive"></div>
  </div>
  <div class="page__content">
    <form class="form page__form" action="/handler/user" data-element="main-form">
      <input name="user_id" type="hidden" value="{USER_ID}">
      <div class="grid-table page__grid-table">
        <!-- Поле: Логин -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_LOGIN_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_LOGIN_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_login" type="text" class="input form__input form__input_text" value="{USER_LOGIN}" placeholder="User2315" data-element="input-login" required>
        </div>
        <!-- Поле: E-Mail -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_EMAIL_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_EMAIL_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_email" type="email" class="input form__input form__input_email" value="{USER_EMAIL}" placeholder="user2315@mail.ru" data-element="input-email" required>
        </div>
        <!-- Поле: Пароль -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_PASSWORD_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_PASSWORD_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_password" type="password" class="input form__input form__input_password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" data-element="input-password" cmsg-password-checker>
        </div>
        <!-- Поле: Пароль (повтор) -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_PASSWORD_REPEAT_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_PASSWORD_REPEAT_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_password" type="password" class="input form__input form__input_password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" data-element="input-password-repeat" cmsg-password-checker>
        </div>
        <!-- Поле: Имя -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_NAME_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_NAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_name" type="text" class="input form__input form__input_text" value="{USER_NAME}" placeholder="Ivan" data-element="input-name">
        </div>
        <!-- Поле: Фамилия -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_SURNAME_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_SURNAME_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_surname" type="text" class="input form__input form__input_text" value="{USER_SURNAME}" placeholder="Ivanov" data-element="input-surname">
        </div>
        <!-- Поле: Отчество -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_PATRONYMIC_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_PATRONYMIC_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_patronymic" type="text" class="input form__input form__input_text" value="{USER_PATRONYMIC}" placeholder="Ivanovich" data-element="input-patronymic">
        </div>
        <!-- Поле: День рождения -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_BIRTHDATE_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_BIRTHDATE_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <input name="user_birthdate" type="date" class="input form__input form__input_date" value="{USER_BIRTHDATE}" min="{USER_BIRTHDATE_MINIMUM}" max="{USER_BIRTHDATE_MAXIMUM}" data-element="input-birthdate">
        </div>
        <!-- Поле: Категория записи -->
        <div class="cell grid-table__cell grid-table__cell_text">
          <div class="cell__title">
            {LANG:PAGE_USER_USER_GROUP_TITLE}
          </div>
          <div class="cell__description">
            {LANG:PAGE_USER_USER_GROUP_DESCRIPTION}
          </div>
        </div>
        <div class="cell grid-table__cell grid-table__cell_data">
          <div data-element="choice" data-choice="group"></div>
        </div>
        {USER_ADDITIONAL_FIELDS}
        <!-- Панель формы -->
        <div class="cell grid-table__cell grid-table__cell_panel" data-element="panel"></div>
      </div>
    </form>
  </div>
</article>
<aside class="main__page-aside page-aside">
  <div class="page-aside__block">
    <h2 class="page-aside__block-title">{LANG:PAGE_USER_SIDEBAR_BLOCK_ABOUT_TITLE}</h2>
    <div class="page-aside__block-content block-content">
      <div class="note-block note-block_blue">
        <p class="block-content__phar">{LANG:PAGE_USER_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_1}</p>
        <p class="block-content__phar">{LANG:PAGE_USER_SIDEBAR_BLOCK_ABOUT_DESCRIPTION_2}</p>
      </div>
    </div>
  </div>
</aside>