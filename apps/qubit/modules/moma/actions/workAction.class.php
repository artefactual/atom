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

class MomaWorkAction extends sfAction
{
  public function execute($request)
  {
    $this->response->addJavaScript('//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js', 'last');
    $this->response->addJavaScript('//cdnjs.cloudflare.com/ajax/libs/angular.js/1.1.5/angular.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/lib/plumb.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/services/d3.v3.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/app.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/directives/plumb-graph.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/services/jsPlumb.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/controllers/home.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/controllers/test.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/controllers/test2.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/controllers/dashboard.js', 'last');
    $this->response->addJavaScript('/apps/qubit/modules/moma/frontend/app/scripts/lib/dashboard-scripts.js', 'last');
  }
}
