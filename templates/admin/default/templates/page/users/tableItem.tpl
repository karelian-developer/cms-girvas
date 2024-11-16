<tr class="table__row table-users__item" data-user-id="{USER_ID}">
  <td class="table__cell" style="width: fit-content;">#{USER_INDEX}</td>
  <td class="table__cell" style="font-weight: 700;"><a href="/admin/user/{USER_ID}">{USER_LOGIN}</a></td>
  <td class="table__cell"><a href="mailto:{USER_EMAIL}">{USER_EMAIL}</a></td>
  <td class="table__cell" style="font-weight: 700;">{USER_GROUP_TITLE}</td>
  <td class="table__cell">{USER_CREATED_DATE_TIMESTAMP}</td>
  <td class="table__cell">{USER_UPDATED_DATE_TIMESTAMP}</td>
  <td class="table__cell">
    <ul class="table-users__item-buttons-list buttons-list list-reset">
      <li class="buttons-list__item">
        <button class="table-users__item-button" role="user-edit">
          <svg class="table-users__item-button-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" xml:space="preserve">
            <rect x="17.5" y="16.3" transform="matrix(0.7071 0.7071 -0.7071 0.7071 33.6798 -9.1993)" width="20.9" height="39.6"/>
            <polygon points="0,64 19.7,59.1 4.9,44.3 "/>
            <rect x="39.6" y="4.9" transform="matrix(0.7071 0.7071 -0.7071 0.7071 24.5317 -31.2849)" width="20.9" height="18.2"/>
          </svg>
        </button>
      </li>
      <li class="buttons-list__item">
        <button class="table-users__item-button" role="user-remove" data-modal-call="admin-user-delete" data-modal-params="userID={USER_ID}">
          <svg class="table-users__item-button-icon" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 64 64" xml:space="preserve">
            <rect x="13.9" y="16.1" width="37.3" height="47.9"/>
            <path d="M41.4,10l1-7.3L24.7,0.3l-1,7.3L10.2,5.7l-1,7.3l44.6,6.2l1-7.3L41.4,10z M25.6,7.8l0.7-5l13.8,1.9l-0.7,5L25.6,7.8z"/>
          </svg>
        </button>
      </li>
    </ul>
  </td>
</tr>