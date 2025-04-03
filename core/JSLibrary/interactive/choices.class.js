/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

/**
 * Класс интерактивного элемента "Выпадающий список"
 * 
 * Интерактивный вариант выпадающего списка позволяет имитировать работу
 * стандартного выпадающего списка HTML-разметки элемента "Select". При
 * использовании данного элемента создается элемент "Select", после чего
 * он скрывается и его работу имитируют отдельные элементы, позволяющие
 * стилизовать выпадающий список любым способом посредством JavaScript или CSS.
 */
export class Choices {
  constructor(interactiveObject, isDisclosed = false) {
    this.interactiveObject = interactiveObject;
    this.isDisclosed = isDisclosed;

    this.element = null;
    this.elementSelect = null;
    this.elementInteractive = null;
    this.name = null;

    this.items = [];
    this.itemSelectedIndex = 0;
    this.assembled = null;
  }

  /**
   * Установить индекс выбранного элемента
   * 
   * @param {Number} index 
   */
  setItemSelectedIndex(index) {
    this.itemSelectedIndex = index;
  }

  /**
   * Получить массив элементов
   * 
   * @returns 
   */
  getItems() {
    return this.items;
  }

  /**
   * Получить значение выбранного элемента
   * 
   * @returns 
   */
  getValue() {
    return (this.elementSelect != null) ? this.elementSelect.value : null;
  }

  /**
   * Установить имя элемента
   * 
   * @param {String} value 
   */
  setName(value) {
    this.name = value;
  }

  /**
   * Добавить элемент выборки
   * 
   * @param {any} label 
   * @param {any} value 
   */
  addItem(label, value) {
    this.items.push({
      'label': label,
      'value': value
    });
  }

  /**
   * Сборка интерактивного элемента
   * @param {HTMLSelectElement} elementSelect 
   * @returns 
   */
  assemblyInteractive(elementSelect) {
    let selectContainerElement = document.createElement('div');
    selectContainerElement.classList.add('interactive__select-imitation');
    selectContainerElement.classList.add('select-imitation');

    if (this.isDisclosed) {
      selectContainerElement.classList.add('select-imitation_is-disclosed');
    }

    let selectedItemContainerElement = document.createElement('div');
    let selectContainerButton, selectContainerButtonIcon;

    if (!this.isDisclosed) {
      selectedItemContainerElement.classList.add('select-imitation__selected-item-container');

      selectContainerButton = document.createElement('button');
      selectContainerButton.classList.add('select-imitation__button');

      selectContainerButtonIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      selectContainerButtonIcon.setAttribute('version', '1.1');
      selectContainerButtonIcon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
      selectContainerButtonIcon.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
      selectContainerButtonIcon.setAttribute('x', '0px');
      selectContainerButtonIcon.setAttribute('y', '0px');
      selectContainerButtonIcon.setAttribute('viewBox', '0 0 64 64');
      selectContainerButtonIcon.setAttribute('xml:space', 'preserve');

      selectContainerButtonIcon.classList.add('select-imitation__button-icon');
    }

    let iconPolygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    iconPolygon.setAttribute('points', '0,29 32,48 64,29 64,16 32,34.7 0,16');
    
    if (!this.isDisclosed) {
      selectContainerButtonIcon.append(iconPolygon);
      selectContainerButton.append(selectContainerButtonIcon);
    }

    let dropedListContainerElement = document.createElement('ul');
    dropedListContainerElement.classList.add('select-imitation__droped-list');
    dropedListContainerElement.classList.add('droped-list');
    dropedListContainerElement.classList.add('list-reset');

    if (!this.isDisclosed) {
      selectedItemContainerElement.addEventListener('click', (event) => {
        event.preventDefault();
        this.collapseOther();

        dropedListContainerElement.classList.toggle('droped-list_is-showed');
      });
    }

    if (!this.isDisclosed) {
      selectContainerButton.addEventListener('click', (event) => {
        event.preventDefault();
        this.collapseOther();

        dropedListContainerElement.classList.toggle('droped-list_is-showed');
      });
    }

    let choicesItems = this.getItems();
    choicesItems.forEach((item, itemIndex) => {
      let isSelected = (itemIndex == this.itemSelectedIndex) ? true : false;
      if (this.isDisclosed) {
        isSelected = false;
      }

      if (!isSelected) {
        let dropedListItemContainerElement = document.createElement('li');
        dropedListItemContainerElement.classList.add('droped-list__item');
        dropedListItemContainerElement.classList.add('item');

        if (this.isDisclosed && itemIndex == 0) {
          dropedListItemContainerElement.classList.add('item_is-selected');
        }

        dropedListItemContainerElement.setAttribute('data-option-value', item.value);

        dropedListItemContainerElement.addEventListener('click', (event) => {
          this.itemSelectedIndex = itemIndex;
          elementSelect.value = item.value;
          elementSelect.dispatchEvent(new Event('change'));

          dropedListItemContainerElement.classList.add('item_is-selected');
          
          let listItemsOtherElements = dropedListContainerElement.querySelectorAll('li');
          if (listItemsOtherElements.length > 0) {
            listItemsOtherElements.forEach((element) => {
              let itemValue = element.getAttribute('data-option-value');

              if (itemValue != item.value) {
                element.classList.remove('item_is-selected');
              }
            });
          }

          if (!this.isDisclosed) {
            selectContainerElement.innerHTML = '';

            let newAssembledInteractive = this.assemblyInteractive(elementSelect);
            selectContainerElement.replaceWith(newAssembledInteractive);
          }
        });

        dropedListItemContainerElement.innerHTML = item.label;

        dropedListContainerElement.append(dropedListItemContainerElement);
      } else {
        let selectedItemElement = document.createElement('div');
        selectedItemElement.classList.add('select-imitation__selected-item');
        selectedItemElement.setAttribute('data-option-value', item.value);
        selectedItemElement.innerHTML = item.label;
        elementSelect.value = item.value;

        selectedItemContainerElement.append(selectedItemElement);
      }
    });
    
    if (!this.isDisclosed) {
      selectContainerElement.append(selectedItemContainerElement);
    }

    selectContainerElement.append(dropedListContainerElement);

    if (!this.isDisclosed) {
      selectContainerElement.append(selectContainerButton);
    }

    return selectContainerElement;
  }

  /**
   * Сборка элемента "Select"
   * 
   * @returns 
   */
  assemblySelect() {
    let element = document.createElement('select');
    element.classList.add('interactive__select');
    element.style.display = 'none';

    return element;
  }

  /**
   * Сборка элемента "Option"
   * 
   * @param {HTMLSelectElement} choicesItem 
   * @param {Boolean} isSelected 
   * @returns 
   */
  assemblyOption(choicesItem, isSelected = false) {
    let element = document.createElement('option');

    if (isSelected && !element.hasAttribute('selected')) {
      element.setAttribute('selected', 'selected');
    }

    element.setAttribute('value', choicesItem.value);

    return element;
  }

  /**
   * Итоговая сборка интерактивного элемента
   */
  assembly() {
    this.elementSelect = this.assemblySelect();
    this.elementInteractive = this.assemblyInteractive(this.elementSelect);

    let choicesItemIndex = 0;
    for (let choicesItem of this.getItems()) {
      let isSelected = (choicesItemIndex == this.itemSelectedIndex) ? true : false;
      this.elementSelect.append(this.assemblyOption(choicesItem, isSelected));

      choicesItemIndex++;
    }

    if (this.name != null) {
      this.elementSelect.setAttribute('name', this.name);
    }
    
    let element = document.createElement('div');
    element.append(this.elementSelect);
    element.append(this.elementInteractive);

    this.element = element;
  }

  /**
   * Сворачивание других открытых выпадающих списков
   */
  collapseOther() {
    let interactiveObjectUID = this.interactiveObject.id;
    let classModificatorInteractiveChoiceName = `interactive_${this.constructor.name.toLocaleLowerCase()}`;
    let interactiveChoicesOther = document.querySelectorAll(`.${classModificatorInteractiveChoiceName}`);
    
    if (interactiveChoicesOther.length > 0) {
      interactiveChoicesOther.forEach((element) => {
        let interactiveElementUID = element.getAttribute('cmsg-interactive-uid');

        if (interactiveElementUID != interactiveObjectUID) {
          let oDropedListContainerElement = element.querySelector('.select-imitation__droped-list');
          if (oDropedListContainerElement != null) {
            oDropedListContainerElement.classList.remove('droped-list_is-showed');
          }
        }
      });
    }
  }
}