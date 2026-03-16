<header class="header">
  <div class="header__container container">
    <div class="header__brand-container">
      <div class="header__brand-image-container">
        <a href="/" class="header__brand-link" title="{SITE_CONFIG_TITLE}">
          <img class="header__brand-image" src="{PROP:IMAGE_HEADER_LOGOTYPE_URL}" alt="{PROP:IMAGE_HEADER_LOGOTYPE_ALT}">
        </a>
      </div>
      <div class="header__brand-labels-container">
        <div class="header__brand-title">{SITE_CONFIG_TITLE}</div>
        <div class="header__brand-slogan">{PROP:SITE_SLOGAN}</div>
      </div>
    </div>
    <nav class="header__nav"> 
      <svg class="header__nav-burger" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 51.49" role="navagation-burger">
        <rect width="64" height="11.94"/>
        <rect y="19.78" width="64" height="11.94"/>
        <rect y="39.56" width="64" height="11.94"/>
      </svg>
      <ul class="header__nav-list">
        <li class="header__nav-item">
          <a class="header__nav-link" href="/">
            <span class="header__nav-span">{LANG:DEFAULT_TEXT_MAIN}</span>
          </a>
        </li>
        <li class="header__nav-item">
          <a class="header__nav-link" href="/entries">
            <span class="header__nav-span">{LANG:DEFAULT_TEXT_ENTRIES}</span>
          </a>
        </li>
        <li class="header__nav-item">
          <a class="header__nav-link" href="/page/about">
            <span class="header__nav-span">{LANG:DEFAULT_TEXT_ABOUT}</span>
          </a>
        </li>
        {NAVIGATION_PROFILE_LINK}
        {NAVIGATION_REGISTRATION_LINK}
        {NAVIGATION_EXIT_LINK}
      </ul>
    </nav>
  </div>
</header>