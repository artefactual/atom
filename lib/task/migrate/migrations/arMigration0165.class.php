<?php

/*
 * This file is part of the Access to Memory (AtoM) software.
 *
 * Access to Memory (AtoM) is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Access to Memory (AtoM) is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Access to Memory (AtoM).  If not, see <http://www.gnu.org/licenses/>.
 */

/*
 * Add menu and settings for GDPR Privacy notification banner and static page.
 *
 * @package    AccesstoMemory
 * @subpackage migration
 */
class arMigration0165
{
    public const VERSION = 165;
    public const MIN_MILESTONE = 2;

    // Add Privacy static page links.
    public function up($configuration)
    {
        if (null === $quickLinksMenu = QubitMenu::getByName('quickLinks')) {
            $quickLinksMenu = new QubitMenu();
            $quickLinksMenu->parentId = QubitMenu::ROOT_ID;
            $quickLinksMenu->name = 'quickLinks';
            $quickLinksMenu->label = 'Quick links';
            $quickLinksMenu->culture = 'en';
            $quickLinksMenu->save();
        }

        if (null === QubitMenu::getByName('privacy')) {
            $menu = new QubitMenu();
            $menu->parentId = $quickLinksMenu->id;
            $menu->name = 'privacy';
            $menu->path = 'staticpage/index?slug=privacy';
            $menu->sourceCulture = 'en';
            $menu->label = 'Privacy Policy';
            $menu->save();
        }

        // Add Privacy banner settings.
        if (null === QubitSetting::getByName('privacy_notification_enabled')) {
            $setting = new QubitSetting();
            $setting->name = 'privacy_notification_enabled';
            $setting->value = 0;
            $setting->editable = 1;
            $setting->source_culture = 'en';
            $setting->save();
        }

        if (null === QubitSetting::getByName('privacy_notification')) {
            $privacy_statement = 'This website uses cookies to enhance your ability to browse and load content. [More Info.](/privacy)';
            $setting = new QubitSetting();
            $setting->name = 'privacy_notification';
            $setting->value = $privacy_statement;
            $setting->editable = 1;
            $setting->source_culture = 'en';
            $setting->setValue($privacy_statement, ['culture' => 'en']);
            $setting->save();
        }

        // Add Privacy policy static page if it does not exist.
        $criteria = new Criteria();
        $criteria->addJoin(QubitStaticPage::ID, QubitSlug::OBJECT_ID);
        $criteria->add(QubitSlug::SLUG, 'privacy');
        $privacyPage = QubitStaticPage::getOne($criteria);

        if (null === $privacyPage) {
            $privacyPage = new QubitStaticPage();
            $privacyPage->title = 'Privacy Policy';
            $privacyPage->slug = 'privacy';
            $privacyPage->sourceCulture = 'en';
            $privacyPage->culture = 'en';
            $privacyPage->content = "##Website visitors##\n\nThis Access to Memory (AtoM) site is designed to allow users to browse and search for the holdings of archives, libraries and museums. Public users will not be asked to log in and will not be asked for any personally identifying information.\n\nHowever, like many modern websites, AtoM collects cookies in order to enable browsing and loading of certain types of content. A cookie is a string of information that a website stores on a visitor’s computer, and that the visitor’s browser provides to the website each time the visitor returns. Visitors to AtoM sites who do not wish to have cookies placed on their computers should set their browsers to refuse cookies. However, certain features may not function properly without the aid of cookies.\n\nAtoM supports integration with Google Analytics (https://www.google.com/analytics/) for the purposes of gathering statistics on page views, site usage, user location, and other data on site visits. All data collected by Google Analytics are stored and processed by Google, according to the Google Ads Data Processing Terms. (https://privacy.google.com/businesses/processorterms/)\n\nNone of the information gathered through the use of cookies or Google Analytics is used for any purpose other than the ones described above.\n\n##Logged-in users##\n\nUsers who log in have user accounts with usernames and passwords. These data are collected solely for the purpose of enabling users to log in to the software and are not disclosed to third parties. All AtoM user passwords are stored in encrypted form to enhance data security.";
            $privacyPage->save();
        }

        return true;
    }
}
