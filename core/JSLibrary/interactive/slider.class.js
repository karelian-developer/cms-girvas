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

import {Item} from './slider/item.class.js';

export class Slider {
  constructor(interactiveObject) {
    this.interactiveObject = interactiveObject;

    this.element = null;
    this.items = [];
    this.slideCurrentIndex = 0;
    this.intervalStart = null;
    this.interval = 0;
  }

  addItem(element) {
    this.items.push(new Item(this, element));
  }

  start(interval) {
    this.intervalStart = setInterval(() => {
      this.changeSlide();
    }, interval);
  }

  changeSlide() {
    let computedStyle = window.getComputedStyle(this.items[0].element)
    let computedStyleMarginLeft = Number(computedStyle.getPropertyValue('margin-left').replace(/px/, ''));
    let computedStyleWidth = Number(computedStyle.getPropertyValue('width').replace(/px/, ''));
    
    if ((computedStyleMarginLeft * -1) >= computedStyleWidth * (this.items.length - 1)) {
      computedStyleMarginLeft = 0;
      this.slideCurrentIndex = 0;
    } else {
      computedStyleMarginLeft -= computedStyleWidth;
      this.slideCurrentIndex += 1;
    }

    this.items[0].element.style.marginLeft = `${computedStyleMarginLeft}px`;

    let indicatorsContainerElement = this.element.querySelector('.slider__indicators-container');
    let indicatorsElements = indicatorsContainerElement.querySelectorAll('.slider__indicator');
    for (let indicator of indicatorsElements) {
      indicator.classList.remove('indicator_active');
    }

    indicatorsElements[this.slideCurrentIndex].classList.add('indicator_active');
  }

  assembly() {
    let element, slidesContainerElement, controllerContainerElement, indicatorsContainerElement;
    
    element = document.createElement('div');
    element.classList.add('slider');

    slidesContainerElement = document.createElement('div');
    slidesContainerElement.classList.add('slider__slides-container');

    controllerContainerElement = document.createElement('div');
    controllerContainerElement.classList.add('slider__controller-container');

    indicatorsContainerElement = document.createElement('div');
    indicatorsContainerElement.classList.add('slider__indicators-container');

    let itemIndex = 0;
    for (let item of this.items) {
      slidesContainerElement.append(item.element);

      let indicatorElement = document.createElement('div');
      indicatorElement.classList.add('slider__indicator');
      indicatorElement.classList.add('indicator');
      indicatorElement.setAttribute('data-slide-number', itemIndex + 1);

      // indicatorElement.addEventListener('click', (event) => {
      //   event.preventDefault();

      //   let indicatorsElements = indicatorsContainerElement.querySelectorAll('.slider__indicator');
      //   for (let indicator of indicatorsElements) {
      //     indicator.classList.remove('indicator_active');
      //   }

      //   event.target.classList.add('indicator_active');

      //   clearInterval(this.intervalStart);
      //   this.intervalStart = null;
      //   this.slideCurrentIndex = itemIndex;
      //   this.changeSlide();
      //   this.start(this.interval);
      // });

      indicatorsContainerElement.append(indicatorElement);

      itemIndex++;
    }

    element.append(controllerContainerElement);
    element.append(indicatorsContainerElement);
    element.append(slidesContainerElement);

    this.element = element;
  }
}