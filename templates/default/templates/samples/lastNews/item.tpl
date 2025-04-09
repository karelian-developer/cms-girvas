<article class="sample__item item">
  <a href="/entry/{ENTRY_NAME}" class="item__link link link_title">
    <h3 class="item__title title">{ENTRY_TITLE}</h3>
  </a>
  <div class="item__description description">
    {ENTRY_DESCRIPTION}
  </div>
  <div class="item__bottom-bar bottom-bar">
    <time class="item__datetime" datetime="Y-m-d">{ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME}</time>
    <a href="/entry/{ENTRY_NAME}" class="item__link link link_read" title="{ENTRY_TITLE}">{LANG:DEFAULT_TEXT_READ}</a>
  </div>
</article>