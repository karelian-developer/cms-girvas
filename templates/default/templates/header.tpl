<header class="header">
  <div class="header__container container" >
    <div class="header__logo-block">
      <a class="header__logo-link display-block" href="/" title="{CMS_TITLE}">
        <img src="{PROP:IMAGE_HEADER_LOGOTYPE_URL}" alt="{PROP:IMAGE_HEADER_LOGOTYPE_ALT}">
      </a>
    </div>
    <nav class="header__nav"> 
      <svg class="header__nav-burger" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 51.49" role="navagation-burger">
        <rect width="64" height="11.94"/>
        <rect y="19.78" width="64" height="11.94"/>
        <rect y="39.56" width="64" height="11.94"/>
      </svg>
      <ul class="header__nav-list">
        <li class="header__nav-item">
          <a class="header__nav-link display-block" href="/">
            <span class="header__nav-span">{LANG:DEFAULT_TEXT_MAIN}</span>
          </a>
        </li>
        <li class="header__nav-item">
          <a class="header__nav-link display-block" href="/entries">
            <span class="header__nav-span">{LANG:DEFAULT_TEXT_ENTRIES}</span>
          </a>
        </li>
        <li class="header__nav-item">
          <a class="header__nav-link display-block" href="/page/about">
            <span class="header__nav-span">{LANG:DEFAULT_TEXT_ABOUT}</span>
          </a>
        </li>
        <li class="header__nav-item">
          {NAVIGATION_PROFILE_LINK}
        </li>
        <li class="header__nav-item">
          {NAVIGATION_REGISTRATION_LINK}
        </li>
        <li class="header__nav-item">
          {F4E267_HELLO_WORLD}
        </li>
      </ul>
    </nav>
  </div>
</header>