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

class SettingsMenuComponent extends sfComponent
{
    public function execute($request)
    {
        $title = $this->context->i18n->__(ucfirst(implode(' ', array_map('strtolower', preg_split('/(?=[A-Z])/', $this->context->getActionName())))));
        $this->response->setTitle("{$title} setting - {$this->response->getTitle()}");

        $i18n = $this->context->i18n;
        $this->nodes = [
            [
                'label' => $i18n->__('Clipboard'),
                'action' => 'clipboard',
            ],
            [
                'label' => $i18n->__('CSV Validator'),
                'action' => 'csvValidator',
            ],
            [
                'label' => $i18n->__('Default page elements'),
                'action' => 'pageElements',
            ],
            [
                'label' => $i18n->__('Default template'),
                'action' => 'template',
            ],
            [
                'label' => $i18n->__('Diacritics'),
                'action' => 'diacritics',
            ],
            [
                'label' => $i18n->__('Digital object derivatives'),
                'action' => 'digitalObjectDerivatives',
            ],
            [
                'label' => $i18n->__('DIP upload'),
                'action' => 'dipUpload',
            ],
            [
                'label' => $i18n->__('Finding Aid'),
                'action' => 'findingAid',
            ],
            [
                'label' => $i18n->__('Global'),
                'action' => 'global',
            ],
            [
                'label' => $i18n->__('I18n languages'),
                'action' => 'language',
            ],
            [
                'label' => $i18n->__('Identifiers'),
                'action' => 'identifier',
            ],
            [
                'label' => $i18n->__('Inventory'),
                'action' => 'inventory',
            ],
            // Only show LDAP authentication settings if LDAP authentication's used
            [
                'label' => $i18n->__('LDAP Authentication'),
                'action' => 'ldap',
                'hide' => !($this->context->user instanceof ldapUser),
            ],
            [
                'label' => $i18n->__('Markdown'),
                'action' => 'markdown',
            ],
            [
                'label' => $i18n->__('OAI repository'),
                'action' => 'oai',
                'hide' => !$this->context->getConfiguration()->isPluginEnabled('arOaiPlugin'),
            ],
            [
                'label' => $i18n->__('Permissions'),
                'action' => 'permissions',
            ],
            [
                'label' => $i18n->__('Privacy Notification'),
                'action' => 'privacyNotification',
            ],
            [
                'label' => $i18n->__('Security'),
                'action' => 'security',
            ],
            [
                'label' => $i18n->__('Site information'),
                'action' => 'siteInformation',
            ],
            [
                'label' => $i18n->__('Storage service'),
                'module' => 'arStorageServiceSettings',
                'action' => 'settings',
                'hide' => !$this->context->getConfiguration()->isPluginEnabled(
                    'arStorageServicePlugin'
                ),
            ],
            [
                'label' => $i18n->__('Treeview'),
                'action' => 'treeview',
            ],
            [
                'label' => $i18n->__('Uploads'),
                'action' => 'uploads',
            ],
            [
                'label' => $i18n->__('User interface labels'),
                'action' => 'interfaceLabel',
            ],
        ];

        foreach ($this->nodes as $i => &$node) {
            // Remove hidden nodes
            if (!empty($node['hide']) && true === $node['hide']) {
                unset($this->nodes[$i]);
            }

            // Active bool
            $node['active'] = $this->context->getActionName() === $node['action'];
        }

        // Sort alphabetically
        usort($this->nodes, function ($el1, $el2) {
            return strnatcmp($el1['label'], $el2['label']);
        });
    }
}
