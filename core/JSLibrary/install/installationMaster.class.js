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

import {URLParser} from '../../JSLibrary/urlParser.class.js';
import {Locale} from '../../JSLibrary/core/locale.class.js';
import {Interactive} from "../interactive.class.js";

export class InstallationMaster {
  constructor(stepsCount) {
    this.searchParams = new URLParser();

    if (this.searchParams.getParam('locale') !== null) {
      this.setStepIndex(0);
    } else {
      this.setStepIndex(-1);
    }

    this.setStepsCount(stepsCount);
    this.buttons = {};
    this.stepsData = [];
    this.progressItems = [];
    
    let installationProgress = document.querySelector('[role="installer-progress"]');
    let installationPages = document.querySelectorAll('[data-page-index]');

    if (this.searchParams.getParam('locale') !== null) {
      for (let stepIndex = 0; stepIndex < installationPages.length; stepIndex++) {
        let stepID = stepIndex + 1;
        let isBuilded = stepIndex === 0;

        this.stepsData.push({
          id: stepID,
          isBuilded: isBuilded,
          isCompleted: false
        });
      }

      installationPages.forEach((element, elementIndex) => {
        element.style.display = elementIndex == 0 ? 'block' : 'none';

        let installationProgressItem = document.createElement('li');
        installationProgressItem.classList.add('installer-progress__item');
        if (elementIndex === 0) {
          installationProgressItem.classList.add('item_current');
        }

        this.progressItems.push(installationProgressItem);
        installationProgress.appendChild(installationProgressItem);
      });
    } else {
      installationPages.forEach((element) => {
        element.style.display = 'none';
      });

      let languagePageElement = document.querySelector('[role="language-page"]');
      if (languagePageElement !== null) {
        languagePageElement.style.display = 'block';

        let interactiveLocaleChoices = new Interactive('choices', {isDisclosed: true});
        let languageSelectContainerElement = document.querySelector('[role="language-select"]');

        fetch('/handler/locales?installation-mode=true', {method: 'GET'}).then((response) => {
          return response.ok ? response.json() : Promise.reject(response);
        }).then((data) => {
          return data.outputData.locales;
        }, (rejectionReason) => {
          this.showPopupNotification(rejectionReason, 0);
        }).then((locales) => {
          locales.forEach((locale, localeIndex) => {
            let localeTitle = locale.title;
            let localeIconURL = locale.iconURL;
            let localeName = locale.name;
            let localeISO639_2 = locale.iso639_2;
    
            let localeIconImageElement = document.createElement('img');
            localeIconImageElement.setAttribute('src', localeIconURL);
            localeIconImageElement.setAttribute('alt', localeTitle);
    
            let localeLabelElement = document.createElement('span');
            localeLabelElement.innerText = localeTitle;
    
            let localeTemplate = document.createElement('template');
            localeTemplate.innerHTML += localeIconImageElement.outerHTML;
            localeTemplate.innerHTML += localeLabelElement.outerHTML;
    
            interactiveLocaleChoices.target.addItem(localeTemplate.innerHTML, localeName);
          });

          interactiveLocaleChoices.assembly();

          languageSelectContainerElement.append(interactiveLocaleChoices.target.element);
        });
      }

      let installerStepDataElement = document.querySelector('[role="installer-step-data"]');
      if (installerStepDataElement != null) {
        installerStepDataElement.innerHTML = '';
      }

      let installerStepTitleElement = document.querySelector('[role="installer-step-title"]');
      if (installerStepTitleElement != null) {
        installerStepTitleElement.innerHTML = 'Language installer';
      }
    }
  }

  async fetchJSON(url, data) {
    return fetch(url, data).then(response => response.ok ? response.json() : Promise.reject(response));
  }

  showPopupNotification(message, statusCode = -1) {
    let interactiveElement = new Interactive('notification');
    interactiveElement.target.isPopup = true;
    interactiveElement.target.setStatusCode(statusCode);
    interactiveElement.target.setContent(message);
    interactiveElement.target.assembly();

    interactiveElement.target.show();
  }

  buildPanel() {
    let localeName = (this.searchParams.getParam('locale') != null) ? this.searchParams.getParam('locale') : 'en_US';
    let locale = new Locale(localeName, 'install');
    let localeData = null;

    locale.getData().then((data) => {
      localeData = data;
    }, (rejectionReason) => {
      this.showPopupNotification(rejectionReason, 0);
    }).then(() => {
      let buttonsPanel = document.querySelector('[role="installation-buttons-panel"]');
      buttonsPanel.innerHTML = '';

      for (let buttonName in this.buttons) {
        delete this.buttons[buttonName];
      }
      
      if (this.getStepIndex() === -1) {
        this.buttons.prevStepIndex = new Interactive('button');
        this.buttons.prevStepIndex.target.setLabel(localeData.BUTTON_APPLY_LABEL);
        this.buttons.prevStepIndex.target.setCallback((event) => {
          event.preventDefault();

          let languageSelectContainerElement = document.querySelector('[role="language-select"]');
          if (languageSelectContainerElement != null) {
            let listItemSelectedElement = languageSelectContainerElement.querySelector('.item_is-selected');
            let listItemSelectedValue = listItemSelectedElement.getAttribute('data-option-value');
            window.location = window.location + '?locale=' + listItemSelectedValue;
          }
        });

        this.buttons.prevStepIndex.assembly();
      }

      if (this.getStepIndex() > 0 && this.getStepIndex() < this.stepsCount - 1) {
        this.buttons.prevStepIndex = new Interactive('button');
        this.buttons.prevStepIndex.target.setLabel(localeData.BUTTON_BACK_LABEL);
        this.buttons.prevStepIndex.target.setCallback((event) => {
          event.preventDefault();
          this.prevStepIndex(localeData);

          this.buildPanel();
        });

        this.buttons.prevStepIndex.assembly();
      }
      
      if (this.getStepIndex() === 0) {
        this.buttons.nextStepIndex = new Interactive('button');
        this.buttons.nextStepIndex.target.setLabel(localeData.BUTTON_SUBMIT_AND_CONTINUE_LABEL);
        this.buttons.nextStepIndex.target.setCallback((event) => {
          event.preventDefault();
          this.nextStepIndex(localeData);

          fetch(`/handler/install?stepIndex=1&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-table-systems"]');

            if (!tableSystemsElement) {
              let dynamicDiv = document.createElement('div');
              dynamicDiv.setAttribute('role', 'cms-table-systems');
              dynamicDiv.innerHTML = resultHTML;

              let installationPages = document.querySelectorAll('[data-page-index]');
              installationPages[this.getStepIndex()].appendChild(dynamicDiv);
            }
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });

          this.buildPanel();
        });
        this.buttons.nextStepIndex.assembly();
      }

      if (this.getStepIndex() === 1) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_UPDATE_DATA_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          fetch(`/handler/install?stepIndex=1&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-table-systems"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-table-systems');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 2) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_UPDATE_DATA_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          fetch(`/handler/install?stepIndex=2&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-table-directories-exists"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-table-directories-exists');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 3) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_UPDATE_DATA_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          fetch(`/handler/install?stepIndex=3&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-table-directories-perms"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-table-directories-perms');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0)
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 4) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_UPDATE_DATA_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          fetch(`/handler/install?stepIndex=4&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-table-dms-exists"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-table-dms-exists');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 5) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_CREATE_CONFIGURATIONS_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          let formTarget = document.querySelector('[role="form-database"]');
          let formData = new FormData(formTarget);

          fetch(`/handler/install?stepIndex=5&locale=${localeName}&installation-mode=true&` + new URLSearchParams(formData).toString(), {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-dms-connect-test"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-dms-connect-test');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);

            this.buttons.nextStepIndex.target.enable();
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 6) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_GENERATE_TABLES_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          fetch(`/handler/install?stepIndex=6&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-dms-tables-generate"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-dms-tables-generate');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);

            this.buttons.nextStepIndex.target.enable();
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      /**
       * ШАГ МАСТЕРА-УСТАНОВЩИКА: №8
       * 
       * Цели шага:
       * - Выбор локализации сайта и административной панели
       * - Выбор временной зоны для расчета времени
       */
      if (this.getStepIndex() === 7) {
        // Если сборка страница шага еще не осуществлялась ранее,
        // то делаем запросы к внутреннему API для получения списков
        // локализаций системы для формирования выпадающих списков
        if (!this.stepsData[this.getStepIndex()].isBuilded) {
          Promise.all([
            this.fetchJSON(`/handler/locales?locale=${localeName}&installation-mode=true`, {method: 'GET'}),
            this.fetchJSON(`/handler/timezones?locale=${localeName}&installation-mode=true`, {method: 'GET'})
          ]).then(([localesData, timezonesData]) => {
            let locales = localesData.outputData.locales;
            let timezones = timezonesData.outputData.timezones;

            let interactiveLocalesChoices = new Interactive('choices');
            let interactiveLocalesAPChoices = new Interactive('choices');
            let interactiveDataSearcher = new Interactive('dataSearcher');

            locales.forEach((locale, localeIndex) => {
              let localeTitle = locale.title;
              let localeIconURL = locale.iconURL;
              let localeName = locale.name;
              let localeISO639_2 = locale.iso639_2;

              let localeIconImageElement = document.createElement('img');
              localeIconImageElement.setAttribute('src', localeIconURL);
              localeIconImageElement.setAttribute('alt', localeTitle);

              let localeLabelElement = document.createElement('span');
              localeLabelElement.innerText = localeTitle;

              let localeTemplate = document.createElement('template');
              localeTemplate.innerHTML += localeIconImageElement.outerHTML;
              localeTemplate.innerHTML += localeLabelElement.outerHTML;

              interactiveLocalesChoices.target.addItem(localeTemplate.innerHTML, localeName);
              interactiveLocalesAPChoices.target.addItem(localeTemplate.innerHTML, localeName);
            });

            timezones.forEach((timezone) => {
              interactiveDataSearcher.target.addItem(`${timezone.name} (${timezone.utc})`, timezone.name);
            });

            interactiveDataSearcher.target.inputValueElementData.name = 'setting_base_timezone';

            interactiveLocalesChoices.target.setName('setting_base_locale');
            interactiveLocalesAPChoices.target.setName('setting_admin_locale');

            interactiveLocalesChoices.assembly();
            interactiveLocalesAPChoices.assembly();
            interactiveDataSearcher.assembly();

            let interactiveLocalesContainerElement = document.querySelector('#E85485302311');
            let interactiveLocalesAPContainerElement = document.querySelector('#E85485302312');

            interactiveLocalesContainerElement.append(interactiveLocalesChoices.target.element);
            interactiveLocalesAPContainerElement.append(interactiveLocalesAPChoices.target.element);
            document.querySelector('#E85485302313').prepend(interactiveDataSearcher.target.element);
          }).catch((rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        }

        /** 
         * Интерактивный элемент "Кнопка"
         * Действие: применение данных
         * @type {Interactive}
         */
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel('Применить');
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          let formTarget = document.querySelector('[role="form-locale"]');
          /** @type {FormData} */
          let formData = new FormData(formTarget);
          
          let request = new Interactive('request', {
            method: 'POST',
            url: `/handler/install/set-locales-and-timezone?locale=${localeName}&installation-mode=true`
          });

          request.target.data = formData;
          request.target.send().then((data) => {
            if (data.statusCode === 1) {
              let resultHTML = data.outputData.html;
              let tableSystemsElement = document.querySelector('[role="cms-locale-and-timezone"]');
              if (tableSystemsElement) {
                tableSystemsElement.remove();
              }
              
              let dynamicDiv = document.createElement('div');
              dynamicDiv.setAttribute('role', 'cms-locale-and-timezone');
              dynamicDiv.innerHTML = resultHTML;
              let installationPages = document.querySelectorAll('[data-page-index]');
              installationPages[this.getStepIndex()].appendChild(dynamicDiv);
              this.buttons.nextStepIndex.target.enable();
            } else {
              this.showPopupNotification(rejectionReason, 0);
            }
          });
        });

        this.buttons.updateData.assembly();
      }

      /**
       * ШАГ МАСТЕРА-УСТАНОВЩИКА: №9
       * 
       * Цели шага:
       * - Назначение наименования сайту
       * - Назначение описания сайту
       * - Назначение ключевых слов сайту
       */
      if (this.getStepIndex() === 8) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_APPLY_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          /** @type {HTMLFormElement} */
          let formTarget = document.querySelector('[role="form-metadata"]');
          if (formTarget !== null) {
            /** @type {FormData} */
            let formData = new FormData(formTarget);
            
            // Применение данных из формы
            fetch(`/handler/install/set-metadata?locale=${localeName}&installation-mode=true`, {method: 'POST', body: formData}).then((response) => {
              return response.ok ? response.json() : Promise.reject(response);
            }).then((data) => {
              let resultHTML = data.outputData.html;
              let statusCode = data.statusCode;

              let tableSystemsElement = document.querySelector('[role="cms-metadata"]');

              if (tableSystemsElement) {
                tableSystemsElement.remove();
              }
              
              let dynamicDiv = document.createElement('div');
              dynamicDiv.setAttribute('role', 'cms-metadata');
              dynamicDiv.innerHTML = resultHTML;

              /** @type {NodeList} */
              let installationPages = document.querySelectorAll('[data-page-index]');
              installationPages[this.getStepIndex()].appendChild(dynamicDiv);

              if (statusCode === 1) {
                this.buttons.nextStepIndex.target.enable();
              }
            }, (rejectionReason) => {
              this.showPopupNotification(rejectionReason, 0);
            });
          }
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 9) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_CREATE_ACCOUNT_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          let formTarget = document.querySelector('[role="form-admin-create"]');
          /** @type {FormData} */
          let formData = new FormData(formTarget);
          
          fetch(`/handler/install/create-admin?locale=${localeName}&installation-mode=true`, {method: 'POST', body: formData}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-admin-create"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-admin-create');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);

            if (data.statusCode == 1) {
              this.buttons.nextStepIndex.target.enable();
            }
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() === 10) {
        this.buttons.updateData = new Interactive('button');
        this.buttons.updateData.target.setLabel(localeData.BUTTON_GENERATE_KEY_LABEL);
        this.buttons.updateData.target.setCallback((event) => {
          event.preventDefault();

          fetch(`/handler/install/generate-secret-key?locale=${localeName}&installation-mode=true`, {method: 'POST'}).then((response) => {
            return response.ok ? response.json() : Promise.reject(response);
          }).then((data) => {
            let resultHTML = data.outputData.html;

            let tableSystemsElement = document.querySelector('[role="cms-secret-key"]');

            if (tableSystemsElement) {
              tableSystemsElement.remove();
            }
            
            let dynamicDiv = document.createElement('div');
            dynamicDiv.setAttribute('role', 'cms-secret-key');
            dynamicDiv.innerHTML = resultHTML;

            let installationPages = document.querySelectorAll('[data-page-index]');
            installationPages[this.getStepIndex()].appendChild(dynamicDiv);

            this.buttons.nextStepIndex.target.enable();
          }, (rejectionReason) => {
            this.showPopupNotification(rejectionReason, 0);
          });
        });

        this.buttons.updateData.assembly();
      }

      if (this.getStepIndex() < this.getStepsCount() - 1) {
        if (this.getStepIndex() > 0) {
          this.buttons.nextStepIndex = new Interactive('button');
          this.buttons.nextStepIndex.target.setLabel(localeData.BUTTON_NEXT_LABEL);
          this.buttons.nextStepIndex.target.setCallback((event) => {
            event.preventDefault();
            this.stepsData[this.getStepIndex()].isCompleted = true;
            this.nextStepIndex(localeData);

            if (this.getStepIndex() === 2) {
              fetch(`/handler/install?stepIndex=2&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
                return response.ok ? response.json() : Promise.reject(response);
              }).then((data) => {
                let resultHTML = data.outputData.html;
      
                let tableSystemsElement = document.querySelector('[role="cms-table-directories-exists"]');
      
                if (tableSystemsElement) {
                  tableSystemsElement.remove();
                }
                
                let dynamicDiv = document.createElement('div');
                dynamicDiv.setAttribute('role', 'cms-table-directories-exists');
                dynamicDiv.innerHTML = resultHTML;
      
                let installationPages = document.querySelectorAll('[data-page-index]');
                installationPages[this.getStepIndex()].appendChild(dynamicDiv);
              }, (rejectionReason) => {
                this.showPopupNotification(rejectionReason, 0);
              });
            }

            if (this.getStepIndex() === 3) {
              fetch(`/handler/install?stepIndex=3&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
                return response.ok ? response.json() : Promise.reject(response);
              }).then((data) => {
                let resultHTML = data.outputData.html;
      
                let tableSystemsElement = document.querySelector('[role="cms-table-directories-perms"]');
      
                if (tableSystemsElement) {
                  tableSystemsElement.remove();
                }
                
                let dynamicDiv = document.createElement('div');
                dynamicDiv.setAttribute('role', 'cms-table-directories-perms');
                dynamicDiv.innerHTML = resultHTML;
      
                let installationPages = document.querySelectorAll('[data-page-index]');
                installationPages[this.getStepIndex()].appendChild(dynamicDiv);
              }, (rejectionReason) => {
                this.showPopupNotification(rejectionReason, 0);
              });
            }

            if (this.getStepIndex() === 4) {
              fetch(`/handler/install?stepIndex=4&locale=${localeName}&installation-mode=true`, {method: 'GET'}).then((response) => {
                return response.ok ? response.json() : Promise.reject(response);
              }).then((data) => {
                let resultHTML = data.outputData.html;
      
                let tableSystemsElement = document.querySelector('[role="cms-table-dms-exists"]');
      
                if (tableSystemsElement) {
                  tableSystemsElement.remove();
                }
                
                let dynamicDiv = document.createElement('div');
                dynamicDiv.setAttribute('role', 'cms-table-dms-exists');
                dynamicDiv.innerHTML = resultHTML;
      
                let installationPages = document.querySelectorAll('[data-page-index]');
                installationPages[this.getStepIndex()].appendChild(dynamicDiv);
              }, (rejectionReason) => {
                this.showPopupNotification(rejectionReason, 0);
              });
            }

            if (this.getStepIndex() === 11) {
              fetch(`/handler/install/finish?locale=${localeName}`, {method: 'POST'});
            }

            this.buildPanel();
          });

          if (this.getStepIndex() >= 5 && this.buttons.hasOwnProperty('nextStepIndex')) {
            if (!this.stepsData[this.getStepIndex()].isCompleted) {
              this.buttons.nextStepIndex.target.disable();
            }
          }

          this.buttons.nextStepIndex.assembly();
        }
      }

      for (let buttonName in this.buttons) {
        buttonsPanel.appendChild(this.buttons[buttonName].target.element);
      }

      this.stepsData[this.getStepIndex()].isBuilded = true;

      console.log(this.stepsData[this.getStepIndex()]);
      console.log(`Index install step: ${this.getStepIndex()}`);
    });
  }

  setStepIndex(index) {
    this.stepIndex = index;
  }

  getStepIndex() {
    return this.stepIndex;
  }

  setStepsCount(count) {
    this.stepsCount = count;
  }

  getStepsCount() {
    return this.stepsCount;
  }

  getStepTitle(localeData) {
    switch (this.getStepIndex()) {
      case 0: return localeData.INSTALLER_STAGE_ACQUAINTANCE_TITLE;
      case 1: return localeData.INSTALLER_STAGE_COMPATIBILITY_CHECK_TITLE;
      case 2: return localeData.INSTALLER_STAGE_CHECKING_INTEGRITY_TITLE;
      case 3: return localeData.INSTALLER_STAGE_CHECKING_ACCESS_RIGHTS_TITLE;
      case 4: return localeData.INSTALLER_STAGE_CHECKING_PDO_DRIVERS_TITLE;
      case 5: return localeData.INSTALLER_STAGE_GENERATING_LOCAL_CONFIGURATIONS_TITLE;
      case 6: return localeData.INSTALLER_STAGE_GENERATING_DATABASE_TABLES_TITLE;
      case 7: return localeData.INSTALLER_STAGE_CONFIGURING_LOCALIZATION_AND_TIME_TITLE;
      case 8: return localeData.INSTALLER_STAGE_WEBSITE_METADATA_TITLE;
      case 9: return localeData.INSTALLER_STAGE_CREATING_AN_ADMINISTRATOR_ACCOUNT_TITLE;
      case 10: return localeData.INSTALLER_STAGE_GENERATING_A_SECRET_KEY_TITLE;
      case 11: return localeData.INSTALLER_STAGE_FINISHING_TITLE;
      default: return '¯\_(ツ)_/¯';
    }
  }

  nextStepIndex(localeData) {
    let stepIndex = this.getStepIndex();
    let stepsCount = this.getStepsCount();

    if (stepIndex < stepsCount - 1) {
      if (typeof(this.progressItems[stepIndex]) != 'undefined') {
        this.progressItems[stepIndex].classList.add('item_completed');
      }

      if (typeof(this.progressItems[stepIndex + 1]) != 'undefined') {
        this.progressItems[stepIndex].classList.remove('item_current');
        this.progressItems[stepIndex + 1].classList.add('item_current');
      }

      this.setStepIndex(stepIndex + 1);
    }

    let installationPages = document.querySelectorAll('[data-page-index]');
    installationPages.forEach((element, elementIndex) => {
      element.style.display = (elementIndex == this.getStepIndex()) ? 'block' : 'none';
    });

    let stepNumberElement = document.querySelector('.title-container__step-number');
    stepNumberElement.innerHTML = this.getStepIndex() + 1;

    let stepTitleElement = document.querySelector('.main__title');
    stepTitleElement.innerHTML = this.getStepTitle(localeData);
  }

  prevStepIndex(localeData) {
    let stepIndex = this.getStepIndex();
    
    if (stepIndex > 0) {
      if (typeof(this.progressItems[stepIndex + 1]) != 'undefined') {
        this.progressItems[stepIndex].classList.remove('item_current');
        this.progressItems[stepIndex - 1].classList.add('item_current');
      }

      this.setStepIndex(stepIndex - 1);
    }

    let installationPages = document.querySelectorAll('[data-page-index]');
    installationPages.forEach((element, elementIndex) => {
      element.style.display = (elementIndex == this.getStepIndex()) ? 'block' : 'none';
    });

    let stepNumberElement = document.querySelector('.title-container__step-number');
    stepNumberElement.innerHTML = this.getStepIndex() + 1;

    let stepTitleElement = document.querySelector('.main__title');
    stepTitleElement.innerHTML = this.getStepTitle(localeData);
  }
}