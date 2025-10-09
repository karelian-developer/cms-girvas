/**
 * CMS GIRVAS (https://www.cms-girvas.ru/)
 * 
 * @link        https://gitflic.ru/project/garbalo/cms-girvas Путь до репозитория системы
 * @copyright   Copyright (c) 2022 - 2024, Andrey Shestakov & Garbalo (https://www.garbalo.com/)
 * @license     https://gitflic.ru/project/garbalo/cms-girvas/LICENSE.md
 */

'use strict';

import {PageProfile as PageDefaultProfile} from './page/profile.class.js';
import {PageEntry as PageDefaultEntry} from './page/entry.class.js';
import {PageAnalytics as PageAdminAnalytics} from './page/admin/analytics.class.js';
import {PageEntry as PageAdminEntry} from './page/admin/entry.class.js';
import {PageEntries as PageAdminEntries} from './page/admin/entries.class.js';
import {PageEntriesCategory as PageAdminEntriesCategory} from './page/admin/entriesCategory.class.js';
import {PageEntriesCategories as PageAdminEntriesCategories} from './page/admin/entriesCategories.class.js';
import {PageEntriesComments as PageAdminEntriesComments} from './page/admin/entriesComments.class.js';
import {PageEntriesSample as PageAdminEntriesSample} from './page/admin/entriesSample.class.js';
import {PageEntriesSamples as PageAdminEntriesSamples} from './page/admin/entriesSamples.class.js';
import {PagePages as PageAdminPages} from './page/admin/pages.class.js';
import {PagePageStatic as PageAdminPageStatic} from './page/admin/pageStatic.class.js';
import {PageMedia as PageAdminMedia} from './page/admin/media.class.js';
import {PageModule as PageAdminModule} from './page/admin/module.class.js';
import {PageModules as PageAdminModules} from './page/admin/modules.class.js';
import {PageSettings as PageAdminSettings} from './page/admin/settings.class.js';
import {PageTheme as PageAdminTheme} from './page/admin/theme.class.js';
import {PageThemes as PageAdminThemes} from './page/admin/themes.class.js';
import {PageUser as PageAdminUser} from './page/admin/user.class.js';
import {PageUsers as PageAdminUsers} from './page/admin/users.class.js';
import {PageUsersGroup as PageAdminUsersGroup} from './page/admin/usersGroup.class.js';
import {PageUsersGroups as PageAdminUsersGroups} from './page/admin/usersGroups.class.js';
import {PageFeed as PageAdminFeed} from './page/admin/feed.class.js';
import {PageFeeds as PageAdminFeeds} from './page/admin/feeds.class.js';
import {PageGlobal as PageAdminGlobal} from './page/admin/global.class.js';
import {PageGlobal as PageDefaultGlobal} from './page/global.class.js';
import {Interactive} from '../interactive.class.js';
import {URLParser} from "../urlParser.class.js";

export class Page {
  constructor(core, pageCategory, pageName, params = {}) {
    this.target = null;
    this.core = core;

    let searchParams = new URLParser();

    if (pageCategory == 'default') {
      switch (pageName) {
        case 'entry': this.target = new PageDefaultEntry(this, params); break;
        case 'global': this.target = new PageDefaultGlobal(this, params); break;
        case 'profile': this.target = new PageDefaultProfile(this, params); break;
      }
    }

    if (pageCategory == 'admin') {
      switch (pageName) {
        case 'analytics': this.target = new PageAdminAnalytics(this, params); break;
        case 'entry': this.target = new PageAdminEntry(this, params); break;
        case 'entries': this.target = new PageAdminEntries(this, params); break;
        case 'entriesCategory': this.target = new PageAdminEntriesCategory(this, params); break;
        case 'entriesCategories': this.target = new PageAdminEntriesCategories(this, params); break;
        case 'entriesComments': this.target = new PageAdminEntriesComments(this, params); break;
        case 'entriesSample': this.target = new PageAdminEntriesSample(this, params); break;
        case 'entriesSamples': this.target = new PageAdminEntriesSamples(this, params); break;
        case 'pages': this.target = new PageAdminPages(this, params); break;
        case 'pageStatic': this.target = new PageAdminPageStatic(this, params); break;
        case 'media': this.target = new PageAdminMedia(this, params); break;
        case 'module': this.target = new PageAdminModule(this, params); break;
        case 'modules': this.target = new PageAdminModules(this, params); break;
        case 'settings': this.target = new PageAdminSettings(this, params); break;
        case 'template': this.target = new PageAdminTheme(this, params); break;
        case 'templates': this.target = (searchParams.getPathPart(4) != null) ? new PageAdminTheme(this, params) : new PageAdminThemes(this, params); break;
        case 'user': this.target = new PageAdminUser(this, params); break;
        case 'users': this.target = new PageAdminUsers(this, params); break;
        case 'usersGroup': this.target = new PageAdminUsersGroup(this, params); break;
        case 'usersGroups': this.target = new PageAdminUsersGroups(this, params); break;
        case 'feed': this.target = new PageAdminFeed(this, params); break;
        case 'feeds': this.target = new PageAdminFeeds(this, params); break;
        case 'global': this.target = new PageAdminGlobal(this, params); break;
      }
    }

    if (this.target != null) {
      this.init();
    }
  }

  showPopupNotification(message, code = -1) {
    let interactiveNotification = new Interactive('notification');
    interactiveNotification.target.isPopup = true;
    interactiveNotification.target.setStatusCode(code);
    interactiveNotification.target.setContent(message);
    interactiveNotification.target.assembly();

    interactiveNotification.target.show();
  }

  init() {
    this.target.init();

    window.CMSCore.debugLog(1, 'CMSCore', `Page "${this.target.constructor.name} inited!"`, true);
  }
}