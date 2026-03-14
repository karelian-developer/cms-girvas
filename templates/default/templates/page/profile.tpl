<article class="page__article article">
  <div class="page__article-header" style="background-image: url('{SITE_TEMPLATE_URL}/images/entry/default_1024.png');">
    <div class="page__buttons-panel" role="profilePanelButtons"></div>
    <h1 class="article__title">{USER_LOGIN}</h1>
  </div>
  <div class="article__content">
    <div class="profile">
      <div class="profile__avatar-container">
        <div class="profile__avatar" style="background-image: url('{USER_AVATAR_URL}');"></div>
      </div>
      <div class="profile__information-container">
        <table class="table profile__information-table">
          <tr class="table__row">
            <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_LOGIN_TITLE}</th>
            <td class="table__cell">{USER_LOGIN}</td>
          </tr>
          <tr class="table__row">
            <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_EMAIL_TITLE}</th>
            <td class="table__cell">{USER_EMAIL}</td>
          </tr>
          <tr class="table__row">
            <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_NAME_TITLE}</th>
            <td class="table__cell">{USER_NAME}</td>
          </tr>
          <tr class="table__row">
            <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_SURNAME_TITLE}</th>
            <td class="table__cell">{USER_SURNAME}</td>
          </tr>
          <tr class="table__row">
            <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_PATRONYMIC_TITLE}</th>
            <td class="table__cell">{USER_PATRONYMIC}</td>
          </tr>
          <tr class="table__row">
            <th class="table__cell table__cell_header">{LANG:PAGE_PROFILE_USER_BIRTHDATE_TITLE}</th>
            <td class="table__cell">{USER_BIRTHDATE}</td>
          </tr>
          {PROFILE_ADDITIONAL_FIELDS}
        </table>
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