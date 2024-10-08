<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{SITE_TITLE} (Admin)</title>
  {SITE_STYLES}
  {SITE_SCRIPTS}
</head>
<body class="body body_base">
  <div class="admin-panel__wrapper">
    <nav class="admin-panel__navigation admin-navigation">
      <div class="admin-navigation__burger" role="mainNavigationBurger">
        <svg class="admin-navigation__burger-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 51.49" style="enable-background:new 0 0 64 64;">
          <rect width="64" height="11.94"/>
          <rect y="19.78" width="64" height="11.94"/>
          <rect y="39.56" width="64" height="11.94"/>
        </svg>
      </div>
      <ul class="admin-navigation-list list-reset">
        <li class="admin-navigation-list__item item item_index" role="mainNavigationIndex">
          <a href="/admin" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_MAIN_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <rect width="25.9" height="25.9"/>
              <rect x="38.1" y="38.1" width="25.9" height="25.9"/>
              <path d="M20.3,20.3v23.3h23.3V20.3H20.3z M37.7,37.7H26.3V26.3h11.4V37.7z"/>
              <path d="M40.7,0v23.3H64V0H40.7z M58,17.4H46.6V6H58V17.4z"/>
              <path d="M0,40.7V64h23.3V40.7H0z M17.4,58H6V46.6h11.4V58z"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_MAIN_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_entries" role="mainNavigationEntries">
          <a href="/admin/entries" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_ENTRIES_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <polygon points="12.3,16.5 23.3,5.5 50.6,5.5 50.6,0 15.4,0 4.4,10.9 4.4,57.5 12.3,57.5 "/>
              <polygon points="27.9,10 17.6,20.3 17.6,64 60.9,64 60.9,10 "/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_ENTRIES_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_pages" role="mainNavigationPages">
          <a href="/admin/pages" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_PAGES_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <polygon points="12.3,16.5 23.3,5.5 50.6,5.5 50.6,0 15.4,0 4.4,10.9 4.4,57.5 12.3,57.5 "/>
              <polygon points="27.9,10 17.6,20.3 17.6,64 60.9,64 60.9,10 "/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_PAGES_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_media" role="mainNavigationMedia">
          <a href="/admin/media" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_ENTRIES_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" id="Слой_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M64,36.1V9.7V0H0v58v5.5V64h3.8H64V36.1z M40.9,53.7c-0.7,0.1-21-20.1-21-20.1S11,44.6,4,53.1V4h56v35.2 C52.4,45.1,41.3,53.6,40.9,53.7z"/>
              <circle cx="41.3" cy="24.4" r="11.8"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_MEDIA_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_users" role="mainNavigationUsers">
          <a href="/admin/users" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_USERS_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 16.933332 16.933281" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <circle cx="8.4666662" cy="6.0901923" r="6" />
              <path d="M 8.466667,12.604044 A 8.4666364,4.3292742 0 0 0 3.3333469e-7,16.933333 H 16.933333 A 8.4666364,4.3292742 0 0 0 8.466667,12.604044 Z" />
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_USERS_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_feeds" role="mainNavigationFeeds">
          <a href="/admin/webChannels" class="admin-navigation-list__item-link" title="Веб-каналы">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M9.3,64H0C0,31.7,31.7,2,64,2v9.3C36.9,11.3,9.3,36.9,9.3,64z"/>
              <path d="M24.9,64h-9.3c0-21.4,22.8-46.6,48.3-46.6v9.3C42.8,26.7,24.9,48.8,24.9,64z"/>
              <path d="M42.5,64h-9.3c0-17.5,14.4-30.9,30.8-30.9v9.3C51.7,42.4,42.5,52.8,42.5,64z"/>
              <circle cx="57.4" cy="57.4" r="6.6"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_WEB_CHANNELS_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_modules" role="mainNavigationModules">
          <a href="/admin/modules" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_MODULES_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <rect x="8.2" y="48.5" width="15.5" height="15.5"/>
              <rect x="48.5" y="7.5" width="15.5" height="15.5"/>
              <rect x="48.5" y="48.5" width="15.5" height="15.5"/>
              <rect x="13.7" y="31.9" width="4.4" height="16.7"/>
              <rect x="54" y="22.9" width="4.4" height="25.6"/>
              <rect x="38" y="6.9" transform="matrix(-1.836970e-16 1 -1 -1.836970e-16 55.3883 -24.982)" class="st0" width="4.4" height="16.7"/>
              <path d="M0,0v31.9h31.9V0H0z M23.7,23.7H8.2V8.2h15.5V23.7z"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_MODULES_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_templates" role="mainNavigationTemplates">
          <a href="/admin/templates" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_TEMPLATES_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <rect y="6.7" width="24" height="50.7"/>
              <rect x="9.9" y="57.3" width="4.3" height="3.1"/>
              <polygon points="36.3,29.9 36.3,0 32,0 14.1,0 9.9,0 9.9,6.7 14.1,6.7 14.1,4.3 32,4.3 32,29.9 32,32 32,34.1 42.2,34.1 42.2,29.9 "/>
              <rect x="42.2" y="26.5" width="21.8" height="10.9"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_TEMPLATES_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_templates" role="mainNavigationTemplates">
          <a href="/admin/analytics" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_ANALYTICS_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <rect x="34" y="20.3" width="12" height="43.7"/>
              <rect x="50" y="9.1" width="14" height="54.9"/>
              <rect y="26.5" width="14" height="37.4"/>
              <rect x="18" y="11.6" width="12" height="52.4"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_ANALYTICS_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_settings" role="mainNavigationSettings">
          <a href="/admin/settings" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_SETTINGS_CMS_LABEL}">
            <svg class="admin-navigation-list__item-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" style="enable-background:new 0 0 64 64;" xml:space="preserve">
              <path d="M64,38.9V25.1h-6.9c-0.6-2.1-1.4-4.1-2.5-6l4.8-4.8l-9.8-9.8l-4.8,4.8c-1.9-1.1-3.9-1.9-6-2.5V0H25.1v6.8
                c-2.1,0.6-4.1,1.4-6,2.5l-4.8-4.8l-9.8,9.8l4.8,4.8c-1.1,1.9-1.9,3.9-2.5,6H0v13.8h6.9c0.6,2.1,1.4,4.1,2.5,6l-4.8,4.8l9.8,9.8
                l4.8-4.8c1.9,1.1,3.9,1.9,6,2.5V64h13.8v-6.8c2.1-0.6,4.1-1.4,6-2.5l4.8,4.8l9.8-9.8l-4.8-4.8c1.1-1.9,1.9-3.9,2.5-6H64z M32,49.3
                c-9.5,0-17.3-7.7-17.3-17.3c0-9.5,7.7-17.3,17.3-17.3S49.3,22.5,49.3,32C49.3,41.5,41.5,49.3,32,49.3z"/>
              <circle cx="32" cy="32" r="12.8"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_SETTINGS_CMS_LABEL}</div>
          </a>
        </li>
        <li class="admin-navigation-list__item item item_exit" role="mainNavigationExit">
          <a href="#" class="admin-navigation-list__item-link" title="{LANG:MAIN_NAVIGATION_EXIT_CMS_LABEL}">
            <svg class="admin-navigation-list__item-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
              <polygon points="50.93 45.04 57.89 38.07 57.92 38.11 64 32.03 63.97 32 64 31.96 57.92 25.89 57.89 25.92 50.93 18.96 44.85 25.04 47.52 27.7 14.93 27.7 14.93 36.29 47.52 36.29 44.85 38.96 50.93 45.04"/>
              <polygon points="37.44 16.23 43.81 0 0 0 0 64 43.81 64 37.44 47.87 11.36 40.89 11.36 23.11 37.44 16.23"/>
            </svg>
            <div class="admin-navigation-list__item-label">{LANG:MAIN_NAVIGATION_EXIT_CMS_LABEL}</div>
          </a>
        </li>
      </ul>
    </nav>
    <div class="admin-panel__basis">
      {ADMIN_PANEL_HEADER}
      {ADMIN_PANEL_MAIN}
      {ADMIN_PANEL_FOOTER}
    </div>
  </div>
</body>
</html>