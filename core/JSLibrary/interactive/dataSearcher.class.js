/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

/**
 * Класс интерактивного элемента "Искатель данных"
 */
export class DataSearcher {
  constructor(interactiveObject) {
    this.interactiveObject = interactiveObject;

    this.element = null;
    this.inputSearchElementData = {
      name: null,
      placeholder: ''
    };

    this.inputValueElementData = {
      name: null
    };

    this.items = [];
  }

  addItem(name, value) {
    this.items.push({
      name: name,
      value: value
    });
  }
  
  assemblyInputSearchElement() {
    let element = document.createElement('input');
    element.classList.add('data-searcher__input');
    element.classList.add('input');

    if (Object.hasOwn(this.inputSearchElementData, 'name')) {
      element.setAttribute('name', this.inputSearchElementData.name);
    }

    if (Object.hasOwn(this.inputSearchElementData, 'placeholder')) {
      element.setAttribute('placeholder', this.inputSearchElementData.placeholder);
    }

    return element;
  }

  assemblyInputValueElement() {
    let element = document.createElement('input');
    element.classList.add('data-searcher__input');
    element.classList.add('input');

    if (Object.hasOwn(this.inputValueElementData, 'name')) {
      element.setAttribute('name', this.inputValueElementData.name);
    }

    element.setAttribute('type', 'hidden');

    return element;
  }

  assemblyOptionsContainerElement(options) {
    let element = document.createElement('ul');
    element.classList.add('data-searcher__options-list');
    element.classList.add('options-list');

    element.style.display = 'none';

    if (options.length > 0) {
      options.forEach((item, itemIndex) => {
        let itemElement = document.createElement('li');
        itemElement.classList.add('options-list__item');
        itemElement.classList.add('item');

        itemElement.setAttribute('data-value', item.value);

        let itemLabel = document.createElement('span');
        itemLabel.classList.add('item__label');
        itemLabel.classList.add('label');
        itemLabel.innerText = item.name;

        itemElement.appendChild(itemLabel);
        element.appendChild(itemElement);
      });
    }

    return element;
  }

  assembly() {
    let optionsContainerElement = this.assemblyOptionsContainerElement(this.items);
    let optionsElements = optionsContainerElement.querySelectorAll('li');

    let inputValueElement = this.assemblyInputValueElement();
    let inputSearchElement = this.assemblyInputSearchElement();
    optionsElements.forEach((element, elementIndex) => {
      element.addEventListener('click', (event) => {
        event.preventDefault();
        
        inputSearchElement.value = element.innerText;
        inputValueElement.value = element.getAttribute('data-value');
      });
    });

    inputSearchElement.addEventListener('input', (event) => {
      event.preventDefault();

      if (inputSearchElement.value != null) {
        let newItemsList = this.items.filter(item => item.name.toLowerCase().includes(inputSearchElement.value.toLowerCase()));
        let newOptionsContainerElement = this.assemblyOptionsContainerElement(newItemsList);

        optionsContainerElement.innerHTML = '';

        let newOptionsElements = newOptionsContainerElement.querySelectorAll('li');
        newOptionsElements.forEach((element, elementIndex) => {
          optionsContainerElement.appendChild(element);

          element.addEventListener('click', (event) => {
            event.preventDefault();
            
            inputSearchElement.value = element.innerText;
            inputValueElement.value = element.getAttribute('data-value');

            console.log(inputValueElement.value);
          });
        });
      }
    });
    
    inputSearchElement.addEventListener('click', (event) => {
      event.preventDefault();

      optionsContainerElement.style.display = 'block';
    });

    document.addEventListener('click', (event) => {
      if (event.target != inputSearchElement && event.target != optionsContainerElement) {
        optionsContainerElement.style.display = 'none';
      }
    });

    let element = document.createElement('div');

    let elementContainerElement = document.createElement('div');
    elementContainerElement.classList.add('interactive__data-searcher');
    elementContainerElement.classList.add('data-searcher');

    elementContainerElement.appendChild(inputValueElement);
    elementContainerElement.appendChild(inputSearchElement);
    elementContainerElement.appendChild(optionsContainerElement);

    element.appendChild(elementContainerElement);

    this.element = element;
  }
}