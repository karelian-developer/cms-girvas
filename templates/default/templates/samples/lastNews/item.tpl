<article class="sample__item">
  <a href="/entry/{ENTRY_NAME}" class="sample__item-title-link">
    <h3 class="sample__item-title">
      <span class="sample__item-title-label">
        {ENTRY_TITLE}
      </span>
    </h3>
  </a>
  <div class="sample__item-description">
    {ENTRY_DESCRIPTION}
  </div>
  <div class="sample__item-bottom-bar">
    <time class="sample__item-datetime" datetime="Y-m-d">
      <span class="sample__item-datetime-label">
        {ENTRY_PUBLISHED_DATE_TIMESTAMP_ISO_8601_WITHOUT_TIME}
      </span>
    </time>
    <a href="/entry/{ENTRY_NAME}" class="sample__item-read-link" title="{ENTRY_TITLE}">
      <span class="sample__item-read-label">
        {LANG:DEFAULT_TEXT_READ}
      </span>
    </a>
  </div>
</article>