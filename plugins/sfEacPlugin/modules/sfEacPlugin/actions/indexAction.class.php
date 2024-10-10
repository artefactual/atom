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

/**
 * EAC representation of an Actor.
 *
 * @author     Jack Bates <jack@nottheoilrig.com>
 */
class sfEacPluginIndexAction extends ActorIndexAction
{
    public function responseFilterContent(sfEvent $event, $content)
    {
        require_once sfConfig::get('sf_root_dir').'/vendor/FluentDOM/FluentDOM.php';

        return FluentDOM($content)
            ->namespaces(['eac' => 'urn:isbn:1-931666-33-4'])
            ->find('//eac:languageDeclaration[not(*)]')
            ->remove();
    }

    public function execute($request)
    {
        sfConfig::set('sf_escaping_strategy', false);

        parent::execute($request);

        $this->eac = new sfEacPlugin($this->resource);

        $this->dispatcher->connect('response.filter_content', [$this, 'responseFilterContent']);
    }
}
