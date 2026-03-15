/**
 * CMS «ГИРВАС»
 * 
 * Включена в Реестр российского программного обеспечения Минцифры РФ.
 * Реестровый номер: №25012 от 27.11.2024
 * 
 * @copyright Copyright (c) 2021 - 2026, ИП Шестаков А.Р., «Карельский разработчик».
 *             Все права защищены.
 * @license   https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 * @see       https://gitflic.ru/project/garbalo/cms-girvas Репозиторий продукта
 * @see       https://cms-girvas.ru Сайт продукта
 * @author    Андрей Шестаков <andrey.shestakov@karelian-developer.ru>
 * @support   support@karelian-developer.ru
 */

'use strict';

import {Interactive} from "../../interactive.class.js";
import {ElementButton} from "../../interactive/form/elementButton.class.js";
import {ElementTextarea} from "../../interactive/form/elementTextarea.class.js";

export class PageProfile {
  constructor(page, params = {}) {
    this.page = page;
  }

  init() {
    let locales;

    this.clientUserPermissions = {};
    this.clientUserData = {};

    fetch('/handler/locales', {method: 'GET'}).then((response) => {

      return (response.ok) ? response.json() : Promise.reject(response);
    
    }).then((data) => {

      locales = data.outputData.locales;

      return window.CMSCore.locales.base.getData();

    }, (rejectionReason) => {

      this.page.showPopupNotification(rejectionReason, 0);

    }).then((localeData) => {

      this.localeBaseData = localeData;
      
      return fetch(`/handler/user/@me?localeMessage=${window.CMSCore.locales.base.name}`, {method: 'GET'});
    
    }, (rejectionReason) => {

      this.page.showPopupNotification(rejectionReason, 0);

    }).then((response) => {

      return (response.ok) ? response.json() : Promise.reject(response);
    
    }).then((data) => {
      
      this.clientUserData = data.outputData.hasOwnProperty('user')
        ? Object.assign(data.outputData.user)
        : this.clientUserData;
      
      return fetch(`/handler/user/@me/permissions?localeMessage=${window.CMSCore.locales.base.name}`, {method: 'GET'});
    
    }, (rejectionReason) => {

      this.page.showPopupNotification(rejectionReason, 0);

    }).then((response) => {

      return response.ok ? response.json() : Promise.reject(response);

    }).then((data) => {

      if (data.outputData.user !== undefined) {
        this.clientUserPermissions = data.outputData.user.permissions;

        if (this.clientUserPermissions.admin_users_management || this.clientUserData.login === this.page.core.searchParams.getPathPart(2) || this.page.core.searchParams.getPathPart(2) === null) {
          let profileAvatarElement = document.querySelector('[role="profile-avatar"]');
          let profileFormElement = document.querySelector('#SYSTEM_F0648538312');
          
          if (profileAvatarElement !== null && profileFormElement !== null) {
            let formInputUserID = profileFormElement.querySelector('input[name="user_id"]');

            let profileAvatarInput = document.createElement('input');
            profileAvatarInput.setAttribute('type', 'file');
            profileAvatarInput.setAttribute('name', 'user_avatar');
            profileAvatarInput.setAttribute('role', 'profileFormInputUserAvatar');

            profileAvatarInput.style.display = 'none';

            profileFormElement.append(profileAvatarInput);
            profileAvatarInput.addEventListener('change', (event) => {
              if (profileAvatarInput.files.length > 0 && formInputUserID !== null) {
                let formData = new FormData();
                formData.append('user_id', formInputUserID.getAttribute('value'));
                formData.append('avatarFile', profileAvatarInput.files[0]);

                let request = new Interactive('request', {
                  method: 'POST',
                  url: '/handler/user/avatar?localeMessage=' + window.CMSCore.locales.base.name
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  if (data.statusCode === 1 && data.outputData.hasOwnProperty('file')) {
                    let fileName, fileURL;

                    fileName = data.outputData.file.fullname;
                    fileURL = data.outputData.file.url;

                    profileAvatarElement.style.backgroundImage = `url('${fileURL}')`;
                    profileAvatarInput.remove();
                  }
                });
              }
            });

            profileAvatarElement.addEventListener('click', (event) => {
              profileAvatarInput.click();
            });
          }

          const profilePanelButtonsElement = document.querySelector('[data-role="profile-panel-buttons"]');
          if (profilePanelButtonsElement !== null) {
            if (this.page.core.searchParams.getParam('event') !== 'edit') {

              let interactiveButtonEdit = new Interactive('button');
              interactiveButtonEdit.target.setLabel(this.localeBaseData.BUTTON_EDIT_LABEL);
              interactiveButtonEdit.target.setCallback((event) => {
                window.location.href = '?event=edit';
              });
              interactiveButtonEdit.assembly();

              profilePanelButtonsElement.append(interactiveButtonEdit.target.element);
            }

            if (this.page.core.searchParams.getParam('event') === 'edit') {
              const profileFormPanelElement = document.querySelector('[data-role="profile-form-panel"]');
              const profilePasswordInput = document.querySelector('[data-role="input-user-password"]');
              const profilePasswordRepeatInput = document.querySelector('[data-role="input-user-password-repeat"]');
              const profilePasswordOldInput = document.querySelector('[data-role="input-user-password-old"]');
              
              const interactiveButtonBack = new Interactive('button');
              const interactiveButtonEditAvatar = new Interactive('button');
              const interactiveButtonSave = new Interactive('button');

              interactiveButtonBack.target.setLabel(this.localeBaseData.DEFAULT_TEXT_BACK);
              interactiveButtonEditAvatar.target.setLabel(this.localeBaseData.BUTTON_EDIT_AVATAR_LABEL);
              interactiveButtonSave.target.setLabel(this.localeBaseData.BUTTON_SAVE_LABEL);

              interactiveButtonBack.target.setCallback((event) => {
                window.location.href = '/profile';
              });

              interactiveButtonEditAvatar.target.setCallback((event) => {
                if (profileAvatarInput !== null) {
                  profileAvatarInput.click();
                }
              });

              profilePasswordInput.addEventListener('change', (event) => {
                event.preventDefault();

                if (event.target.value !== '') {
                  profilePasswordInput.setAttribute('required', '');
                  profilePasswordRepeatInput.setAttribute('required', '');
                  profilePasswordOldInput.setAttribute('required', '');
                } else {
                  if (profilePasswordRepeatInput.value === '') {
                    profilePasswordInput.removeAttribute('required');
                    profilePasswordRepeatInput.removeAttribute('required');
                    profilePasswordOldInput.removeAttribute('required');
                  }
                }
              });

              profilePasswordRepeatInput.addEventListener('change', (event) => {
                event.preventDefault();

                if (event.target.value !== '') {
                  profilePasswordInput.setAttribute('required', '');
                  profilePasswordRepeatInput.setAttribute('required', '');
                  profilePasswordOldInput.setAttribute('required', '');
                } else {
                  if (profilePasswordRepeatInput.value === '') {
                    profilePasswordInput.removeAttribute('required');
                    profilePasswordRepeatInput.removeAttribute('required');
                    profilePasswordOldInput.removeAttribute('required');
                  }
                }
              });

              profilePasswordOldInput.addEventListener('change', (event) => {
                event.preventDefault();

                if (event.target.value !== '') {
                  profilePasswordInput.setAttribute('required', '');
                  profilePasswordRepeatInput.setAttribute('required', '');
                  profilePasswordOldInput.setAttribute('required', '');
                } else {
                  if (profilePasswordRepeatInput.value === '') {
                    profilePasswordInput.removeAttribute('required');
                    profilePasswordRepeatInput.removeAttribute('required');
                    profilePasswordOldInput.removeAttribute('required');
                  }
                }
              });

              interactiveButtonBack.assembly();
              interactiveButtonEditAvatar.assembly();
              interactiveButtonSave.assembly();

              if (profilePanelButtonsElement !== null) {
                profilePanelButtonsElement.append(interactiveButtonBack.target.element);
                profilePanelButtonsElement.append(interactiveButtonEditAvatar.target.element);
              }

              if (profileFormPanelElement !== null) {
                profileFormPanelElement.append(interactiveButtonSave.target.element);
              }
            }
          }
        }
      }
    }, (rejectionReason) => {

      this.page.showPopupNotification(rejectionReason, 0);

    });
  }
}