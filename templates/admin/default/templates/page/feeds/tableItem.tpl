<tr class="table__row table-web-channels__item" data-web-channel-id="{FEED_ID}" data-web-channel-name="{FEED_NAME}">
  <td class="table__cell" style="width: fit-content;">#{FEED_INDEX}</td>
  <td class="table__cell"><a href="/admin/feed/{FEED_ID}">{FEED_TITLE}</a></td>
  <td class="table__cell">{FEED_TYPE_TITLE}</td>
  <td class="table__cell">{FEED_CREATED_DATE_TIMESTAMP}</td>
  <td class="table__cell">{FEED_UPDATED_DATE_TIMESTAMP}</td>
  <td class="table__cell">
    <ul class="table-web-channels__item-buttons-list buttons-list list-reset">
      <li class="buttons-list__item">
        <button class="table-web-channels__item-button" role="web-channel-edit">
          <svg class="table-web-channels__item-button-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" xml:space="preserve">
            <rect x="17.5" y="16.3" transform="matrix(0.7071 0.7071 -0.7071 0.7071 33.6798 -9.1993)" width="20.9" height="39.6"/>
            <polygon points="0,64 19.7,59.1 4.9,44.3 "/>
            <rect x="39.6" y="4.9" transform="matrix(0.7071 0.7071 -0.7071 0.7071 24.5317 -31.2849)" width="20.9" height="18.2"/>
          </svg>
        </button>
      </li>
      <li class="buttons-list__item">
        <button class="table-web-channels__item-button" role="web-channel-remove" data-modal-call="admin-web-channel-delete" data-modal-params="webChannelID={FEED_ID}">
          <svg class="table-web-channels__item-button-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" xml:space="preserve">
            <rect x="13.9" y="16.1" width="37.3" height="47.9"/>
            <path d="M41.4,10l1-7.3L24.7,0.3l-1,7.3L10.2,5.7l-1,7.3l44.6,6.2l1-7.3L41.4,10z M25.6,7.8l0.7-5l13.8,1.9l-0.7,5L25.6,7.8z"/>
          </svg>
        </button>
      </li>
      <li class="buttons-list__item">
        <button class="table-web-channels__item-button" role="web-channel-view">
          <svg class="table-web-channels__item-button-icon" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
            <path d="M12.027 9.92L16 13.95 14 16l-4.075-3.976A6.465 6.465 0 0 1 6.5 13C2.91 13 0 10.083 0 6.5 0 2.91 2.917 0 6.5 0 10.09 0 13 2.917 13 6.5a6.463 6.463 0 0 1-.973 3.42zM1.997 6.452c0 2.48 2.014 4.5 4.5 4.5 2.48 0 4.5-2.015 4.5-4.5 0-2.48-2.015-4.5-4.5-4.5-2.48 0-4.5 2.014-4.5 4.5z" fill-rule="evenodd"/>
          </svg>
        </button>
      </li>
    </ul>
  </td>
</tr>