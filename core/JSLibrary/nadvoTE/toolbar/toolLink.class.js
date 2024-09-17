/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {Tool} from './tool.class.js';
import {Interactive} from '../../interactive.class.js';

export class ToolLink extends Tool {
  constructor(editor, element) {
    super(editor, {
      name: 'link',
      type: 'button',
      iconPath: '/core/JSLibrary/nadvoTE/images/icons/toolbar/link.svg',
      element: element
    });

    this.initClickEvent();
  }

  initClickEvent() {
    super.addClickEvent(() => {
      console.log(`[NADVO TE] Tool ${this.name} clicked!`);
      let stringSelection = this.editor.getSelectionString();

      let inputLinkLabelElement = document.createElement('input');
      inputLinkLabelElement.setAttribute('placeholder', 'Название ссылки');
      inputLinkLabelElement.setAttribute('name', 'link_label');
      inputLinkLabelElement.classList.add('form__input');
      inputLinkLabelElement.style.width = '100%';
      inputLinkLabelElement.style.marginBottom = '10px';
      let inputLinkElement = document.createElement('input');
      inputLinkElement.classList.add('form__input');
      inputLinkElement.setAttribute('placeholder', 'https://www.site.ru/...');
      inputLinkElement.setAttribute('name', 'link');
      inputLinkElement.style.width = '100%';

      let formElement = document.createElement('form');
      formElement.classList.add('form');
      formElement.appendChild(inputLinkLabelElement);
      formElement.appendChild(inputLinkElement);

      let inputsGroupContainer = document.createElement('div');
      inputsGroupContainer.append(formElement);

      let interactiveModal = new Interactive('modal', {title: "Вставить ссылку", content: inputsGroupContainer});
      interactiveModal.target.addButton('Вставить', () => {
        let inputLinkLabelElement = interactiveModal.target.element.querySelector('[name="link_label"]');
        let inputLinkElement = interactiveModal.target.element.querySelector('[name="link"]');
        
        let linkLabel = (inputLinkLabelElement.value.trim().length == 0) ? stringSelection : inputLinkLabelElement.value;
        let link = inputLinkElement.value;
        
        this.editor.textarea.replaceStringSelection(
          `[${linkLabel}](${link})`
        );

        interactiveModal.target.close();
      });
      interactiveModal.target.addButton('Отмена', () => {
        interactiveModal.target.close();
      });
      interactiveModal.assembly();
      document.body.appendChild(interactiveModal.target.element);
      interactiveModal.target.show();
    });
  }
}