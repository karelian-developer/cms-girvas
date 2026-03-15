<article class="page__article article">
  <div class="page__article-header" style="background-image: url('{SITE_TEMPLATE_URL}/images/entry/preview/default-1024.webp');">
    <div class="page__buttons-panel" data-role="profile-panel-buttons"></div>
    <h1 class="article__title">{USER_LOGIN}</h1>
  </div>
  <div class="article__content">
    <div class="profile article__profile">
      <div class="profile__avatar-container">
        <img class="profile__avatar" src="{USER_AVATAR_URL}" alt="{USER_LOGIN}" data-role="profile-avatar">
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
  {SIDEBAR_BLOCK_DEMO}
</aside>