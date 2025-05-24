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

abstract class AbstractSitemapUrl
{
    public function __construct()
    {
        $this->writer = new XMLWriter();
        $this->writer->openMemory();

        $this->dateFormatter = new sfDateFormat('en');
    }

    public function getUrl($baseUrl, $indent = true)
    {
        $this->writer->setIndent($indent);
        $this->writer->startElement('url');

        $this->writer->startElement('loc');
        $this->writer->text($baseUrl.$this->getLoc());
        $this->writer->endElement();

        if (null !== $lastmod = $this->getLastmod()) {
            $this->writer->startElement('lastmod');
            $this->writer->text($lastmod);
            $this->writer->endElement();
        }

        if (null !== $changefreq = $this->getChangefreq()) {
            $this->writer->startElement('changefreq');
            $this->writer->text($changefreq);
            $this->writer->endElement();
        }

        if (null !== $priority = $this->getPriority()) {
            $this->writer->startElement('priority');
            $this->writer->text($priority);
            $this->writer->endElement();
        }

        $this->writer->endElement();

        return $this->writer->outputMemory();
    }

    protected function getLoc()
    {
        return '/'.$this->slug;
    }

    protected function getPriority() {}

    protected function getLastmod()
    {
        if (empty($this->updated_at)) {
            return;
        }

        return date('Y-m-d', strtotime($this->updated_at));
    }

    protected function getChangefreq()
    {
        return 'monthly';
    }
}
