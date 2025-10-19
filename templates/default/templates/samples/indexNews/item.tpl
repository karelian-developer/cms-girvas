<li id="SYSTEM_ENTRY_{ENTRY_ID}" class="entries-list__item item" data-item-id="{ENTRY_ID}" style="background-image: url('{ENTRY_PREVIEW_URL}');">
  <article class="item__article article">
    <a href="{ENTRY_URL}" title="{ENTRY_TITLE}" class="article__interactive-block display-block" tabindex="0">
      <h2 class="article__title">{ENTRY_TITLE}</h2>
      <p class="article__description">{ENTRY_DESCRIPTION}</p>
    </a>
    <div class="article__metadata metadata">
      <div class="metadata__datetime datetime flex">
        <svg class="metadata__icon" width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
          <g clip-path="url(#clip0_105_35)">
            <path d="M9 1.6875C13.0219 1.6875 16.3125 4.97813 16.3125 9C16.3125 13.0219 13.0219 16.3125 9 16.3125C4.97813 16.3125 1.6875 13.0219 1.6875 9C1.6875 4.97813 4.97813 1.6875 9 1.6875ZM9 0C4.02187 0 0 4.02187 0 9C0 13.9781 4.02187 18 9 18C13.9781 18 18 13.9781 18 9C18 4.02187 13.9781 0 9 0Z" fill="white" />
            <path d="M9 2.70001C8.49375 2.70001 8.07188 3.12189 8.07188 3.62814V8.29689C7.93125 8.49376 7.84688 8.71876 7.84688 8.97189C7.84688 9.61876 8.38125 10.125 9 10.125C9.61875 10.125 10.1531 9.59064 10.1531 8.97189C10.1531 8.94376 10.1531 8.94376 10.1531 8.94376L12.0938 7.00314C12.4594 6.63751 12.4594 6.04689 12.0938 5.68126C11.8969 5.48439 11.6719 5.40001 11.4188 5.40001C11.1656 5.40001 10.9406 5.48439 10.7438 5.68126L9.9 6.52501V3.65626C9.92813 3.12189 9.50625 2.70001 9 2.70001Z" fill="white" />
          </g>
          <defs>
            <clipPath>
              <rect width="18" height="18" fill="white" />
            </clipPath>
          </defs>
        </svg>
        <time class="metadata__value" datetime="{ENTRY_CREATED_DATE_TIMESTAMP}">{ENTRY_CREATED_DATE_TIMESTAMP}</time>
      </div>
      <a class="article__group-link" href="{ENTRY_CATEGORY_URL}">{ENTRY_CATEGORY_TITLE}</a>
    </div>
  </article>
</li>