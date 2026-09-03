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

import {Tool} from './tool.class.js';
import {Interactive} from '../../interactive.class.js';

export class ToolCode extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'image',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/source.svg',
      element: element
    });

    this.modal = null;
    this.imagesListGroup = 0;
    this.filesPath = '';

    this.initClickEvent();
    this.bindHotkey('Ctrl+Shift+C');
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);
      
      const selection = this.editor.getSelectionString();

      if (selection) {
        this.editor.clearSelection();
      }

      const modalBodyContent = document.createElement('div');
      modalBodyContent.classList.add('code-manager');
      
      const inputLanguageLabelElement = document.createElement('input');
      inputLanguageLabelElement.setAttribute('placeholder', 'Язык программирования/командной строки');
      inputLanguageLabelElement.setAttribute('name', 'language_name');
      inputLanguageLabelElement.classList.add('form__input');
      inputLanguageLabelElement.value = selection;

      const textareaCodeElement = document.createElement('textarea');
      textareaCodeElement.classList.add('form__textarea');
      textareaCodeElement.setAttribute('placeholder', 'echo \'Hello, world!\';');
      textareaCodeElement.setAttribute('name', 'code');
      textareaCodeElement.rows = '20';

      const formElement = document.createElement('form');
      formElement.classList.add('form');
      formElement.classList.add('code-manager__form');
      formElement.append(inputLanguageLabelElement);
      formElement.append(textareaCodeElement);
      
      const inputsGroupContainer = document.createElement('div');
      inputsGroupContainer.classList.add('code-manager__fixed-panel');
      inputsGroupContainer.append(formElement);

      modalBodyContent.append(inputsGroupContainer);

      this.modal = new Interactive('modal',
        {
          title: "Вставить код",
          content: modalBodyContent,
          width: window.innerWidth - 100
        }
      );
      
      let self = this;

      this.modal.target.onClose(() => {
        // ...
      });

      this.modal.target.addButton('Вставить', () => {
        const inputLanguageLabelElement = this.modal.target.element.querySelector('[name="language_name"]');
        const textareaCodeElement = this.modal.target.element.querySelector('[name="code"]');
        
        const inputLanguageLabel = inputLanguageLabelElement.value;
        const textareaCode = textareaCodeElement.value;
        
        this.editor.textarea.replaceStringSelection(
          `\`\`\`${inputLanguageLabel}` + "\n" +  `${textareaCode}` + "\n\`\`\`"
        );

        this.modal.target.close();
      });

      this.modal.target.addButton('Отмена', () => {
        this.modal.target.close();
      });

      this.modal.assembly();
      document.body.appendChild(this.modal.target.element);
      this.modal.target.show();
    });
  }
}