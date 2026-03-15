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

import {Interactive} from "../../../interactive.class.js";

export class EntryComment {
  constructor(entry, data) {
    this.entry = entry;
    this.id = (typeof data.id != 'undefined') ? data.id : 0;
    this.entryID = (typeof data.entryID != 'undefined') ? data.entryID : 0;
    this.index = (typeof data.index != 'undefined') ? data.index : 0;
    this.indexLabel = (typeof data.indexLabel != 'undefined') ? data.indexLabel : 0;
    this.content = (typeof data.content != 'undefined') ? data.content : '';
    this.authorID = (typeof data.authorID != 'undefined') ? data.authorID : 0;
    this.isHidden = (typeof data.isHidden != 'undefined') ? data.isHidden : false;
    this.hiddenReason = (typeof data.hiddenReason != 'undefined') ? data.hiddenReason : '';
    this.rating = (typeof data.rating != 'undefined') ? data.rating : 0;
    this.ratingVoters = (typeof data.ratingVoters != 'undefined') ? data.ratingVoters : {};
    this.createdUnixTimestamp = (typeof data.createdUnixTimestamp != 'undefined') ? data.createdUnixTimestamp : 0;
    this.updatedUnixTimestamp = (typeof data.updatedUnixTimestamp != 'undefined') ? data.updatedUnixTimestamp : 0;
    this.answersCount = (typeof data.answersCount != 'undefined') ? data.answersCount : 0;
    this.answersLoadingOffset = (typeof data.answersLoadingOffset != 'undefined') ? data.answersLoadingOffset : 0;
    this.answersLoadingLimit = (typeof data.answersLoadingLimit != 'undefined') ? data.answersLoadingLimit : 0;
    this.parent = (typeof data.parent != 'undefined') ? data.parent : null;
    this.level = (typeof data.level != 'undefined') ? data.level : 0;
    this.elementAssembled = null;
  }

  getAnswersContainerElement() {
    return this.elementAssembled.children[2];
  }

  getAnswersListElement() {
    return this.getAnswersContainerElement().children[0];
  }

  initAnswersPanel(clientUserData = {}, clientUserPermissions = {}) {
    let interactivePanelButtonContainerElement = document.createElement('div');
    interactivePanelButtonContainerElement.classList.add('comment__answers-container');
    interactivePanelButtonContainerElement.setAttribute('data-parent-id', this.id);

    let interactivePanelButton = new Interactive('button');
    interactivePanelButton.target.setLabel(this.entry.localeBaseData.BUTTON_LOAD_ANSWERS_COMMENTS_LABEL);
    interactivePanelButton.target.setCallback((event) => {
      let commentParentElement = document.querySelector(`[data-comment-id="${this.id}"]`);
      let answersContainerParentElement = this.getAnswersListElement();
      let answersCount = (answersContainerParentElement != null) ? answersContainerParentElement.children.length : 0;
      console.log(answersCount);

      let request = new Interactive('request', {
        method: 'GET',
        url: `/handler/entry/${this.entryID}/comments?sortColumn=createdUnixTimestamp&sortType=desc&parentID=${this.id}&limit=${this.answersLoadingLimit}&offset=${answersCount}&localeMessage=${window.CMSCore.locales.base.name}`
      });

      request.target.showingNotification = false;

      request.target.send().then((data) => {
        if (data.statusCode == 1 && data.outputData.hasOwnProperty('comments')) {
          let comments = data.outputData.comments, commentLoadedIndex = 0;
          let answersListElementQuery = this.getAnswersListElement();
          console.log(this.level);
          let answersListElement;
          if (answersListElementQuery == null) {
            answersListElement = document.createElement('ul');
            answersListElement.classList.add('comment__answers-list');
            answersListElement.classList.add('answers-list');
            answersListElement.classList.add('list-reset');
          } else {
            answersListElement = answersListElementQuery;
          }

          let appendComment = (commentData, commentParentElement) => {
            let requestAppend = new Interactive('request', {
              method: 'GET',
              url: `/handler/user/${commentData.authorID}`
            });
      
            requestAppend.target.showingNotification = false;

            requestAppend.target.send().then((authorLoadedData) => {
              let authorData = authorLoadedData.outputData.user;
              let answersContainerParentElement = this.getAnswersListElement();
              
              commentData.entryID = this.entryID;
              commentData.answersLoadingLimit = this.answersLoadingLimit;
              commentData.index = (answersContainerParentElement != null) ? answersContainerParentElement.children.length + 1 : 1;
              commentData.indexLabel = `${commentParentElement.id}_${commentData.index}`;
              
              const entryComment = new EntryComment(this.entry, commentData);
              entryComment.level = this.level <= 2 ? this.level + 1 : this.level;
              entryComment.parent = this;

              console.log(this.id);

              let targetContainer;
              
              if (this.level >= 3) {
                let level2Comment = this;
                while (level2Comment.level > 2 && level2Comment.parent) {
                  level2Comment = level2Comment.parent;
                }
                
                if (level2Comment.level === 2) {
                  targetContainer = level2Comment.getAnswersListElement();
                } else {
                  targetContainer = answersContainerParentElement;
                }
              } else if (this.level === 2) {
                targetContainer = this.getAnswersListElement();
              } else {
                targetContainer = commentParentElement.querySelector('[data-role="entry-comments-container"] .comments-list');
              }

              entryComment.assembly({login: authorData.login, avatarURL: authorData.avatarURL, group: authorData.group}, (commentElement) => {
                commentLoadedIndex++;
                
                if (targetContainer) {
                  targetContainer.append(commentElement);
                } else {
                  answersListElement.append(commentElement);
                }
                
                entryComment.initPanel(clientUserData, clientUserPermissions);
                
                if (commentLoadedIndex < comments.length) {
                  if (this.level >= 3) {
                    appendComment(comments[commentLoadedIndex], targetContainer?.closest('.comment') || commentParentElement);
                  } else {
                    appendComment(comments[commentLoadedIndex], commentParentElement);
                  }
                }

                if (entryComment.answersCount > 0) {
                  entryComment.initAnswersPanel(clientUserData, clientUserPermissions);
                }

                entryComment.elementAssembled.setAttribute('data-role', 'entry-comments-answer');
                entryComment.elementAssembled.classList.add('comment_answer');
              });
            });
          };
  
          if (answersListElementQuery == null) {
            interactivePanelButtonContainerElement.append(answersListElement);
          }

          if (typeof(comments) != 'undefined') {
            if (comments.length > 0) {
              appendComment(comments[0], commentParentElement);
            }
          }
        }
      });
    });

    interactivePanelButton.assembly();
    interactivePanelButton.target.element.setAttribute('role', 'entryCommentLoadAnswers');

    let commentPanel = this.elementAssembled.querySelector('[id^=E7453975856\_]');
    commentPanel.append(interactivePanelButton.target.element);
    
    this.elementAssembled.append(interactivePanelButtonContainerElement);
  }

  initPanel(clientUserData = {}, clientUserPermissions = {}) {
    let elementEntry = document.querySelector('[role="entry"]');
    
    if (this.elementAssembled != null) {
      let commentPanel = this.elementAssembled.querySelector('[id^=E7453975856\_]');
      
      if (clientUserData.isLogged) {
        // Edit comment
        if (clientUserPermissions.base_entry_comment_change && clientUserData.id == this.authorID || clientUserPermissions.moder_entries_comments_management) {
          let interactiveButtonEdit = new Interactive('button');
          interactiveButtonEdit.target.setLabel(this.entry.localeBaseData.BUTTON_EDIT_LABEL);
          interactiveButtonEdit.target.setCallback((event) => {
            let form = elementEntry.querySelector('[role="entryCommentForm"]');
            form.setAttribute('method', 'PATCH');
            
            let formTextarea = elementEntry.querySelector('[name="comment_content"]');
            formTextarea.value = this.content;
            formTextarea.scrollIntoView({block: "center", behavior: "smooth"});
            formTextarea.focus();

            /** @type {ElementInput} */
            let formInputID = this.entry.commentForm.target.createElementInput();
            formInputID.init({
              name: 'comment_id',
              type: 'hidden',
            });

            form.prepend(formInputID.element);
            formInputID.element.value = this.id;

            /** @type {ElementButton} */
            let formButtonReset = this.entry.commentForm.target.createElementButton();
            formButtonReset.setStringLabel(this.entry.localeBaseData.BUTTON_RESET_LABEL);
            formButtonReset.setClickEvent((event) => {
              event.preventDefault();
              form.setAttribute('method', 'PUT');
              formTextarea.value = '';
              formButtonReset.element.remove();
              formInputID.element.remove();
            });
            formButtonReset.init({
              role: 'comment-form-button-reset'
            });

            let formPanelElement = form.querySelector('.form__panel-container');
            formPanelElement.append(formButtonReset.element);
          });
          interactiveButtonEdit.assembly();
          commentPanel.append(interactiveButtonEdit.target.element);
        }

        // Hide comment with reason
        if (clientUserPermissions.moder_entries_comments_management) {
          let interactiveButtonHide = new Interactive('button');
          interactiveButtonHide.target.setLabel(this.entry.localeBaseData.BUTTON_HIDE_LABEL);
          interactiveButtonHide.target.setStyle('red');
          interactiveButtonHide.target.setCallback((event) => {
            let elementForm = document.createElement('form');
            elementForm.classList.add('form');
            let elementTextarea = document.createElement('textarea');
            elementTextarea.classList.add('form__textarea');
            elementTextarea.style.width = '100%';
            elementTextarea.setAttribute('name', 'comment_hidden_reason');
            elementTextarea.setAttribute('placeholder', 'Укажите причину...');
            elementForm.append(elementTextarea);
            
            let interactiveModal = new Interactive('modal', {title: this.entry.localeBaseData.MODAL_COMMENT_HIDE_TITLE, content: elementForm});
            interactiveModal.target.addButton(this.entry.localeBaseData.BUTTON_SUBMIT_LABEL, () => {
              let formData = new FormData();
              formData.append('comment_id', this.id);
              formData.append('comment_is_hidden', 'on');
              formData.append('comment_hidden_reason', elementTextarea.value);

              let request = new Interactive('request', {
                method: 'PATCH',
                url: `/handler/entry/comment?localeMessage=${window.CMSCore.locales.base.name}`
              });
    
              request.target.data = formData;
    
              request.target.send().then((data) => {
                interactiveModal.target.close();

                if (data.statusCode == 1) {
                  window.location.reload();
                }
              });
            });

            interactiveModal.target.addButton('Отмена', () => {
              interactiveModal.target.close();
            });

            interactiveModal.assembly();
            document.body.appendChild(interactiveModal.target.element);
            interactiveModal.target.show();
          });

          interactiveButtonHide.assembly();
          commentPanel.append(interactiveButtonHide.target.element);

          if (this.isHidden) {
            interactiveButtonHide.target.element.style.display = 'none';
          }
        }

        // Show comment
        if (clientUserPermissions.moder_entries_comments_management) {
          let interactiveButtonPublish = new Interactive('button');
          interactiveButtonPublish.target.setLabel(this.entry.localeBaseData.BUTTON_SHOW_LABEL);
          interactiveButtonPublish.target.setStyle('green');
          interactiveButtonPublish.target.setCallback((event) => {
            event.preventDefault();

            let interactiveModal = new Interactive('modal', {title: this.entry.localeBaseData.MODAL_COMMENT_SHOW_TITLE, content: this.entry.localeBaseData.MODAL_COMMENT_SHOW_DESCRIPTION});
            interactiveModal.target.addButton(this.entry.localeBaseData.BUTTON_YES_LABEL, () => {
              let formData = new FormData();
              formData.append('comment_id', this.id);
              formData.append('comment_is_hidden', 'off');
              formData.append('comment_hidden_reason', '');

              let request = new Interactive('request', {
                method: 'PATCH',
                url: `/handler/entry/comment?localeMessage=${window.CMSCore.locales.base.name}`
              });
    
              request.target.data = formData;
    
              request.target.send().then((data) => {
                interactiveModal.target.close();

                if (data.statusCode == 1) {
                  window.location.reload();
                }
              });
            });

            interactiveModal.target.addButton(this.entry.localeBaseData.BUTTON_NO_LABEL, () => {
              interactiveModal.target.close();
            });

            interactiveModal.assembly();
            document.body.appendChild(interactiveModal.target.element);
            interactiveModal.target.show();
          });
          interactiveButtonPublish.assembly();
          commentPanel.append(interactiveButtonPublish.target.element);

          if (!this.isHidden) {
            interactiveButtonPublish.target.element.style.display = 'none';
          }
        }

        // Delete comment
        if (clientUserPermissions.base_entry_comment_change && clientUserData.id == this.authorID || clientUserPermissions.moder_entries_comments_management) {
          let interactiveButtonDelete = new Interactive('button');
          interactiveButtonDelete.target.setLabel(this.entry.localeBaseData.BUTTON_DELETE_LABEL);
          interactiveButtonDelete.target.setStyle('red');
          interactiveButtonDelete.target.setCallback((event) => {
            let formData = new FormData();
            formData.append('comment_id', this.id);

            let interactiveModal = new Interactive('modal', {title: this.entry.localeBaseData.MODAL_ENTRY_COMMENT_DELETE_TITLE, content: this.entry.localeBaseData.MODAL_ENTRY_COMMENT_DELETE_DESCRIPTION});
            interactiveModal.target.addButton(this.entry.localeBaseData.BUTTON_YES_LABEL, () => {
              if (this.answersCount == 0) {
                let formData = new FormData();
                formData.append('comment_id', this.id);

                let request = new Interactive('request', {
                  method: 'DELETE',
                  url: '/handler/entry/comment'
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  interactiveModal.target.close();

                  if (data.statusCode == 1) {
                    this.elementAssembled.remove();
                  }
                });
              } else {
                let formData = new FormData();
                formData.append('comment_id', this.id);
                formData.append('comment_is_hidden', 'on');
                formData.append('comment_hidden_reason', this.entry.localeBaseData.MODAL_COMMENT_HIDEN_REASON_DELETED);

                let request = new Interactive('request', {
                  method: 'PATCH',
                  url: `/handler/entry/comment?localeMessage=${window.CMSCore.locales.base.name}`
                });
      
                request.target.data = formData;
      
                request.target.send().then((data) => {
                  interactiveModal.target.close();

                  if (data.statusCode == 1) {
                    window.location.reload();
                  }
                });
              }
            });

            interactiveModal.target.addButton(this.entry.localeBaseData.BUTTON_NO_LABEL, () => {
              interactiveModal.target.close();
            });

            interactiveModal.assembly();
            document.body.appendChild(interactiveModal.target.element);
            interactiveModal.target.show();
          });
          interactiveButtonDelete.assembly();
          commentPanel.append(interactiveButtonDelete.target.element);
        }
      }

      if (!this.isHidden) {
        if (clientUserData.isLogged) {
          if (clientUserPermissions.base_entry_comment_create) {
            // Answer to comment
            let interactiveButtonAnswer = new Interactive('button');
            interactiveButtonAnswer.target.setLabel(this.entry.localeBaseData.BUTTON_ANSWER_LABEL);
            interactiveButtonAnswer.target.setCallback((event) => {
              let form = elementEntry.querySelector('[role="entryCommentForm"]');
              let formTextarea = elementEntry.querySelector('[name="comment_content"]');
              let formInputCommentParentIDElement = form.querySelector('[name="comment_parent_id"]');
              formInputCommentParentIDElement.value = this.id;
              formTextarea.focus();
              formTextarea.scrollIntoView({block: "center", behavior: "smooth"});
            });
            interactiveButtonAnswer.assembly();
            commentPanel.append(interactiveButtonAnswer.target.element);
          }
        }
          
        let commentRatePanel = this.elementAssembled.querySelector('[role="entryCommentRate"]');
        let rateCountElement = document.createElement('div');
        rateCountElement.classList.add('comment__rating-count');

        if (clientUserData.isLogged) {
          // Rate comment
          if (clientUserPermissions.base_entry_comment_rate && clientUserData.id != this.authorID) {
            let interactiveButtonRateUp = new Interactive('button');
            interactiveButtonRateUp.target.setLabel('🡅');
            interactiveButtonRateUp.target.setCallback((event) => {
              let formData = new FormData();
              formData.append('comment_id', this.id);
              formData.append('comment_rating_vote', 'up');

              let request = new Interactive('request', {
                method: 'PATCH',
                url: `/handler/entry/comment?localeMessage=${window.CMSCore.locales.base.name}`
              });
    
              request.target.data = formData;
    
              request.target.send().then((data) => {
                if (data.statusCode == 1 && data.outputData.hasOwnProperty('comment')) {
                  let commentElement = document.querySelector(`[data-comment-id="${this.id}"]`);
                  let rateCountElement = commentElement.querySelector('.comment__rating-count');
                  rateCountElement.innerHTML = data.outputData.comment.rating;
                }
              });
            });
            interactiveButtonRateUp.assembly();

            let interactiveButtonElement = interactiveButtonRateUp.target.element.querySelector('button');
            interactiveButtonElement.classList.add('interactive__button_green');
            
            commentRatePanel.append(interactiveButtonRateUp.target.element);
          }
        }

        rateCountElement.append(this.rating);
        commentRatePanel.append(rateCountElement);

        if (clientUserData.isLogged) {
          if (clientUserPermissions.base_entry_comment_rate && clientUserData.id != this.authorID) {
            let interactiveButtonRateDown = new Interactive('button');
            interactiveButtonRateDown.target.setLabel('🡇');
            interactiveButtonRateDown.target.setCallback((event) => {
              let formData = new FormData();
              formData.append('comment_id', this.id);
              formData.append('comment_rating_vote', 'down');

              let request = new Interactive('request', {
                method: 'PATCH',
                url: `/handler/entry/comment?localeMessage=${window.CMSCore.locales.base.name}`
              });
    
              request.target.data = formData;
    
              request.target.send().then((data) => {
                if (data.statusCode == 1 && data.outputData.hasOwnProperty('comment')) {
                  let commentElement = document.querySelector(`[data-comment-id="${this.id}"]`);
                  let rateCountElement = commentElement.querySelector('.comment__rating-count');
                  rateCountElement.innerHTML = data.outputData.comment.rating;
                }
              });
            });
            interactiveButtonRateDown.assembly();

            let interactiveButtonElement = interactiveButtonRateDown.target.element.querySelector('button');
            interactiveButtonElement.classList.add('interactive__button_red');

            commentRatePanel.append(interactiveButtonRateDown.target.element);
          }
        }
      }
    }
  }

  assembly(params = {}, callback = (htmlElement) => {}) {
    let content = (!this.isHidden) ? this.content : `${CMSCore.localeData.PAGE_ENTRY_COMMENT_HIDE_LABEL}: ${this.hiddenReason}`;
    let authorLogin = (typeof params.login != 'undefined') ? params.login : '';
    let authorAvatarURL = (typeof params.avatarURL != 'undefined') ? params.avatarURL : '';
    let authorGroupTitle = (typeof params.group != 'undefined') ? params.group.title : '';
    
    let createdDate = new Date(this.createdUnixTimestamp * 1000);
    let createdDay = (createdDate.getDay() + 1).toString().padStart(2, '0');
    let createdMonth = createdDate.getMonth().toString().padStart(2, '0');
    let createdYear = createdDate.getFullYear().toString().padStart(2, '0');
    let createdHours = createdDate.getHours().toString().padStart(2, '0');
    let createdMinutes = createdDate.getMinutes().toString().padStart(2, '0');
    let createdSeconds = createdDate.getSeconds().toString().padStart(2, '0');

    let requestData = {
      templateCategory: 'base',
      templateFilePath: 'templates/page/entry/comment.tpl',
      patternNames: [
        'COMMENT_ID',
        'COMMENT_INDEX',
        'COMMENT_CONTENT',
        'COMMENT_AUTHOR_LOGIN',
        'COMMENT_AUTHOR_AVATAR_URL',
        'COMMENT_AUTHOR_GROUP_TITLE',
        'COMMENT_CREATED_DATE_TIMESTAMP'
      ],
      patternValues: [
        this.id,
        this.index,
        content.replaceAll(",", "{DELIM}"),
        authorLogin,
        authorAvatarURL,
        authorGroupTitle,
        `${createdDay}.${createdMonth}.${createdYear} ${createdHours}:${createdMinutes}:${createdSeconds}`
      ]
    };

    let urlSearchParams = new URLSearchParams(requestData);

    let request = new Interactive('request', {
      method: 'GET',
      url: `/handler/template/assembly?localeMessage=${window.CMSCore.locales.base.name}&` + urlSearchParams.toString()
    });

    request.target.showingNotification = false;

    request.target.send().then((data) => {
      if (data.statusCode == 1 && data.outputData.hasOwnProperty('templateAssembled')) {
        let templateAssembled = data.outputData.templateAssembled;
        let elementAssembled = document.createElement('div');
        elementAssembled.innerHTML = templateAssembled;
        this.elementAssembled = elementAssembled.firstChild;

        if (this.isHidden) {
          this.elementAssembled.classList.add('comment_is-hidden');
        }

        this.elementAssembled.id = (this.indexLabel == 0) ? `comment_${this.index}` : this.indexLabel;
        
        let indexLinkElement = this.elementAssembled.querySelector(`a[role="entryCommentIndex"]`);
        if (indexLinkElement != null) {
          indexLinkElement.setAttribute('href', `#${this.elementAssembled.id}`);
          indexLinkElement.innerHTML = `#${this.elementAssembled.id.replace('comment_', '')}`;
        }

        callback(this.elementAssembled);
      }
    });
  }
}