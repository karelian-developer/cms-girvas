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
import {EntryComment} from "./entry/comment.class.js";

export class PageEntry {
  constructor(page, params = {}) {
    this.page = page;
    
    this.commentsLimit = 0;
    this.commentsData = [];
    this.commentForm = null;
  }

  init() {
    let elementEntry = document.querySelector('[role="entry"]');
    let entryCommentsListElement = (elementEntry !== null) ? elementEntry.querySelector('[role="entryCommentsList"]') : null;
    let entryID = (elementEntry !== null) ? elementEntry.getAttribute('data-entry-id') : 0;
    this.clientUserPermissions = {};
    this.clientUserData = {};

    this.commentsLimit = (entryCommentsListElement !== null) ? entryCommentsListElement.querySelectorAll('[role="entryComment"]').length : 0;
    this.commentsOffset = 0;
    this.postLoadComments = [];
    this.comments = [];

    this.page.core.locales.base.getData().then((localeData) => {
      this.localeBaseData = localeData;
      return fetch(`/handler/user/@me/permissions?localeMessage=${window.CMSCore.locales.base.name}`, {method: 'GET'});
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      if (typeof data.outputData.user !== 'undefined') {
        this.clientUserPermissions = data.outputData.user.permissions;

        if (this.clientUserPermissions.base_entry_comment_create || this.clientUserPermissions.base_entry_comment_change) {
          /** @type {Interactive} */
          let commentForm = new Interactive('form');
          commentForm.target.init({
            id: 'E7429674077',
            method: 'PUT',
            action: `/handler/entry/comment`,
            role: 'entryCommentForm'
          });

          let commentFormElement = commentForm.target.element.querySelector('form');
          commentFormElement.classList.add('form_entry-comment');
          commentFormElement.setAttribute('readonly', '');

          commentForm.target.successCallback = (data) => {
            if (data.outputData.hasOwnProperty('comment')) {
              if (commentForm.target.element.firstChild.getAttribute('method') === 'PUT') {
                let commentID = data.outputData.comment.id;
                let commentData = {}, authorData = {};
                
                fetch(`/handler/entry/comment/${commentID}?localeMessage=${window.CMSCore.locales.base.name}`, {method: 'GET'}).then((response) => {
                  return (response.ok) ? response.json() : Promise.reject(response);
                }).then((commentCreatedData) => {
                  commentData = commentCreatedData.outputData.comment;
                  return fetch(`/handler/user/${commentData.authorID}?localeMessage=${window.CMSCore.locales.base.name}`, {method: 'GET'});
                }).then((response) => {
                  return (response.ok) ? response.json() : Promise.reject(response);
                }).then((authorCommentCreatedData) => {
                  authorData = authorCommentCreatedData.outputData.user;

                  let newEntryComment = new EntryComment(this, commentData);
                  newEntryComment.assembly({login: authorData.login, avatarURL: authorData.avatarURL, group: authorData.group}, (commentElement) => {
                    entryCommentsListElement.prepend(commentElement);
                    newEntryComment.initPanel(this.clientUserData, this.clientUserPermissions);
                  });
                });
              }

              if (commentForm.target.element.firstChild.getAttribute('method') === 'PATCH') {
                let commentID = data.outputData.comment.id;
                let commentContent = data.outputData.comment.content;
                let commentElement = elementEntry.querySelector(`[data-comment-id="${commentID}"]`);
                if (commentElement !== null) {
                  let commentContentElement = commentElement.querySelector('[role="entryCommentContent"]');
                  commentContentElement.innerHTML = commentContent;
                }
              }
            } else {
              let notification = new PopupNotification(data.message, document.body, true);
              notification.show();
            }
          };
          
          /** @type {ElementInput} */
          let formInputEntryID = commentForm.target.createElementInput();
          formInputEntryID.init({
            name: 'comment_entry_id',
            type: 'hidden'
          });

          formInputEntryID.element.value = entryID;
          
          /** @type {ElementInput} */
          let formInputParentID = commentForm.target.createElementInput();
          formInputParentID.init({
            name: 'comment_parent_id',
            type: 'hidden'
          });
          
          /** @type {ElementTextarea} */
          let formTextarea = commentForm.target.createElementTextarea();
          formTextarea.init({
            name: 'comment_content',
            placeholder: this.localeBaseData.ENTRY_COMMENT_TEXTAREA_PLACEHOLDER,
            rows: 6
          });

          /** @type {ElementButton} */
          let formButton = commentForm.target.createElementButton();
          formButton.setStringLabel(this.localeBaseData.BUTTON_SEND_LABEL);
          formButton.setClickEvent((event) => {
            event.preventDefault();

            if (formTextarea.element.value !== '') {
              commentForm.target.send();
            }

            formTextarea.element.value = '';

            let formInputCommentID = commentForm.target.element.querySelector('[name="comment_id"]');
            if (formInputCommentID !== null) {
              let commentTargetElement = elementEntry.querySelector(`[data-comment-id="${formInputCommentID.value}"]`);
              commentTargetElement.scrollIntoView({block: "center", behavior: "smooth"});
              formInputCommentID.remove();

              if (commentForm.target.element.firstChild.hasAttribute('method')) {
                if (commentForm.target.element.firstChild.getAttribute('method') === 'PATCH') {
                  commentForm.target.element.firstChild.setAttribute('method', 'PUT');
                }
              }
            }

            let formButtonRest = commentForm.target.element.querySelector('[role="comment-form-button-reset"]');
            if (formButtonRest !== null) formButtonRest.remove();
          });
          formButton.init({
            role: 'comment-form-button-send'
          });

          const commentFormContainerElement = document.querySelector('[data-role="entry-comment-form-container"]');

          if (commentFormContainerElement !== null) {
            const commentFormPanelElement = document.createElement('div');
            commentFormPanelElement.classList.add('form__panel-container');
            commentFormPanelElement.append(formButton.element);

            commentForm.target.element.setAttribute('id', 'E7443753064');
            commentForm.target.element.firstChild.append(formInputParentID.element);
            commentForm.target.element.firstChild.append(formInputEntryID.element);
            commentForm.target.element.firstChild.append(formTextarea.element);
            commentForm.target.element.firstChild.append(commentFormPanelElement);
            commentForm.assembly();

            this.commentForm = commentForm;

            commentFormContainerElement.append(this.commentForm.target.element);
          }
        }
      }

      return fetch(`/handler/entry/${entryID}/comments?localeMessage=${window.CMSCore.locales.base.name}&limit=${this.commentsLimit}&offset=${this.commentsOffset}&sortColumn=createdUnixTimestamp&sortType=desc&parentID=0`, {method: 'GET'});
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      this.postLoadComments = data.outputData.comments;
      
      let entryCommentsContainerElement = (elementEntry !== null) ? elementEntry.querySelector('[data-role="entry-comments-container"]') : null;
      let interactiveButtonCommentsLoad = new Interactive('button');
      interactiveButtonCommentsLoad.target.setLabel(this.localeBaseData.BUTTON_LOAD_MORE_COMMENTS_LABEL);
      interactiveButtonCommentsLoad.target.setCallback((event) => {
        let request = new Interactive('request', {
          method: 'GET',
          url: `/handler/entry/${entryID}/comments?localeMessage=${window.CMSCore.locales.base.name}&limit=${this.commentsLimit}&offset=${this.commentsOffset}&sortColumn=createdUnixTimestamp&sortType=desc&parentID=0`
        });

        request.target.showingNotification = false;

        request.target.send().then((data1) => {
          if (data1.statusCode === 1 && data1.outputData.hasOwnProperty('comments')) {
            let comments = data1.outputData.comments, commentLoadedIndex = 0;
            
            let appendComment = (commentData, clientUserPermissions) => {
              let requestAppend = new Interactive('request', {
                method: 'GET',
                url: `/handler/user/${commentData.authorID}`
              });

              requestAppend.target.showingNotification = false;

              requestAppend.target.send().then((authorLoadedData) => {
                let authorData = authorLoadedData.outputData.user;
                
                commentData.index = entryCommentsListElement.querySelectorAll('[role="entryComment"]').length + 1;
                commentData.entryID = entryID;
                commentData.answersLoadingLimit = 4;

                let entryComment = new EntryComment(this, commentData);
                entryComment.assembly({login: authorData.login, avatarURL: authorData.avatarURL, group: authorData.group}, (commentElement) => {
                  commentLoadedIndex++;
                  this.commentsOffset++;
                  entryCommentsListElement.append(commentElement);

                  entryComment.initPanel(this.clientUserData, clientUserPermissions);

                  if (commentLoadedIndex < comments.length) {
                    appendComment(comments[commentLoadedIndex], clientUserPermissions);
                  }
                  
                  if (commentData.answersCount > 0) {
                    entryComment.initAnswersPanel(this.clientUserData, clientUserPermissions);
                  }
                });
              });
            };

            if (comments.length > 0) {
              appendComment(comments[0], this.clientUserPermissions);
            }
          }
        });
      });

      interactiveButtonCommentsLoad.assembly();

      if (entryCommentsContainerElement !== null) {
        entryCommentsContainerElement.append(interactiveButtonCommentsLoad.target.element);
      }
      
      return fetch(`/handler/user/@me?localeMessage=${window.CMSCore.locales.base.name}`, {method: 'GET'});
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    }).then((response) => {
      return (response.ok) ? response.json() : Promise.reject(response);
    }).then((data) => {
      this.clientUserData = (data.outputData.hasOwnProperty('user')) ? Object.assign(data.outputData.user) : this.clientUserData;

      let commentsElements = (entryCommentsListElement !== null) ? entryCommentsListElement.querySelectorAll('[role="entryComment"]') : [];
      commentsElements.forEach((comment, commentIndex) => {
        if (typeof(this.postLoadComments) !== 'undefined') {
          this.postLoadComments[commentIndex].entryID = entryID;
          let entryComment = new EntryComment(this, this.postLoadComments[commentIndex]);
          entryComment.elementAssembled = comment;
          entryComment.answersLoadingLimit = 4;
          entryComment.initPanel(this.clientUserData, this.clientUserPermissions);
          
          if (entryComment.answersCount > 0) {
            entryComment.initAnswersPanel(this.clientUserData, this.clientUserPermissions);
          }
          
          if (entryComment.isHidden) {
            comment.classList.add('comment_is-hidden');
          }

          this.commentsOffset++;
        }
      });
    }, (rejectionReason) => {
      this.page.showPopupNotification(rejectionReason, 0);
    });

    let commentFormElement = document.querySelector('[role="entryCommentForm"]');
    if (commentFormElement !== null) {
      if (commentFormElement.hasAttribute('readonly')) {
        commentFormElement.removeAttribute('readonly');
      }
    }
  }
}