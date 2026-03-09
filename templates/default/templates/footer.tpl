<footer class="footer">
  <div class="footer__container container">
    <div class="footer-top flex">
      <div class="footer-top__left">
        <nav class="footer__nav">
          <ul class="footer__nav-list">
            <li class="footer__nav-item">
              <a class="footer__nav-link" href="/entries/news">
                <span class="footer__nav-span">{LANG:DEFAULT_TEXT_NEWS}</span>
              </a>
            </li>
            <li class="footer__nav-item">
              <a class="footer__nav-link" href="/entries/articles">
                <span class="footer__nav-span">{LANG:DEFAULT_TEXT_ARTICLES}</span>
              </a>
            </li>
            <li class="footer__nav-item">
              <a class="footer__nav-link" href="/page/reviews">
                <span class="footer__nav-span">{LANG:DEFAULT_TEXT_REVIEWS}</span>
              </a>
            </li>
            <li class="footer__nav-item">
              <a class="footer__nav-link" href="/page/about">
                <span class="footer__nav-span">{LANG:DEFAULT_TEXT_ABOUT}</span>
              </a>
            </li>
          </ul>
        </nav>
        <div class="footer__text-block">
            <p class="footer__text">{LANG:DEFAULT_FOOTER_CMS_DESCRIPTION}</p>
        </div>
      </div>
      <div class="footer-top__right">
        <a class="footer-top__logo-link display-block" href="/" title="CMS GIRVAS">
          <picture>
            <img src="{PROP:IMAGE_FOOTER_LOGOTYPE_URL}" alt="{PROP:IMAGE_FOOTER_LOGOTYPE_ALT}">
          </picture>
        </a>
      </div>
    </div>
    <div class="footer__bottom flex">
      <div class="footer__bottom-left-block">{CMS_COPYRIGHT}</div>
      <ul class="information-list footer__bottom-information-list flex">
        <li class="information-list__item">
          <a class="information-list__link" href="{PROP:LINK_DOCUMENT_LICENSE_AGREEMENT}">{LANG:DEFAULT_FOOTER_META_LINK_LICENSE_AGREEMENT}</a>
        </li>
        <li class="information-list__item">
          <a class="information-list__link" href="{PROP:LINK_DOCUMENT_TERMS_OF_USE}">{LANG:DEFAULT_FOOTER_META_LINK_TERMS_OF_USE}</a>
        </li>
        <li class="information-list__item">
          <a class="information-list__link" href="{PROP:LINK_DOCUMENT_COOKIE_ABOUT}">{LANG:DEFAULT_FOOTER_META_LINK_COOKIE_ABOUT}</a>
        </li>
      </ul>
    </div>
  </div>
</footer>