/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

export class Choices {
  constructor() {
    this.element = null;
    this.elementSelect = null;
    this.elementInteractive = null;
    this.name = null;

    this.items = [];
    this.itemSelectedIndex = 0;
    this.assembled = null;
  }

  setItemSelectedIndex(index) {
    this.itemSelectedIndex = index;
  }

  getItems() {
    return this.items;
  }

  setName(value) {
    this.name = value;
  }

  addItem(label, value) {
    this.items.push({
      'label': label,
      'value': value
    });
  }

  // getItemLabel(index) {
  //   let item = this.items[index];
  //   let element = document.createElement('div');
  //   element.append(item.label);


  // }

  assemblyInteractive(elementSelect) {
    let selectContainerElement = document.createElement('div');
    selectContainerElement.classList.add('interactive__select-imitation');
    selectContainerElement.classList.add('select-imitation');

    let selectedItemContainerElement = document.createElement('div');
    selectedItemContainerElement.classList.add('select-imitation__selected-item-container');

    let selectContainerButton = document.createElement('button');
    selectContainerButton.classList.add('select-imitation__button');

    let selectContainerButtonIcon = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
    selectContainerButtonIcon.setAttribute('version', '1.1');
    selectContainerButtonIcon.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
    selectContainerButtonIcon.setAttribute('xmlns:xlink', 'http://www.w3.org/1999/xlink');
    selectContainerButtonIcon.setAttribute('x', '0px');
    selectContainerButtonIcon.setAttribute('y', '0px');
    selectContainerButtonIcon.setAttribute('viewBox', '0 0 64 64');
    selectContainerButtonIcon.setAttribute('xml:space', 'preserve');

    selectContainerButtonIcon.classList.add('select-imitation__button-icon');

    let iconPolygon = document.createElementNS('http://www.w3.org/2000/svg', 'polygon');
    iconPolygon.setAttribute('points', '0,29 32,48 64,29 64,16 32,34.7 0,16');

    selectContainerButtonIcon.append(iconPolygon);
    selectContainerButton.append(selectContainerButtonIcon);

    let dropedListContainerElement = document.createElement('ul');
    dropedListContainerElement.classList.add('select-imitation__droped-list');
    dropedListContainerElement.classList.add('droped-list');
    dropedListContainerElement.classList.add('list-reset');

    selectContainerButton.addEventListener('click', (event) => {
      event.preventDefault();
      dropedListContainerElement.classList.toggle('droped-list_is-showed');
    });

    let choicesItems = this.getItems();
    choicesItems.forEach((item, itemIndex) => {
      let isSelected = (itemIndex == this.itemSelectedIndex) ? true : false;

      if (!isSelected) {
        let dropedListItemContainerElement = document.createElement('li');
        dropedListItemContainerElement.classList.add('droped-list__item');
        dropedListItemContainerElement.setAttribute('data-option-value', item.value);

        dropedListItemContainerElement.addEventListener('click', (event) => {
          this.itemSelectedIndex = itemIndex;
          elementSelect.value = item.value;
          elementSelect.dispatchEvent(new Event('change'));
          selectContainerElement.innerHTML = '';

          let newAssembledInteractive = this.assemblyInteractive(elementSelect);
          selectContainerElement.replaceWith(newAssembledInteractive);
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
 
    selectContainerElement.append(selectedItemContainerElement);
    selectContainerElement.append(dropedListContainerElement);
    selectContainerElement.append(selectContainerButton);

    return selectContainerElement;
  }

  assemblySelect() {
    let element = document.createElement('select');
    element.classList.add('interactive__select');
    element.style.display = 'none';

    return element;
  }

  assemblyOption(choicesItem, isSelected = false) {
    let element = document.createElement('option');

    if (isSelected && !element.hasAttribute('selected')) {
      element.setAttribute('selected', 'selected');
    }

    element.setAttribute('value', choicesItem.value);

    return element;
  }

  assembly() {
    // this.items.sort((a, b) => {
    //   if (a.value[0] > b.value[0]) return -1;
    //   if (a.value[0] < b.value[0]) return 1;

    //   return b.value[1] < a.value[1] ? 1 : -1;
    // });

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
}