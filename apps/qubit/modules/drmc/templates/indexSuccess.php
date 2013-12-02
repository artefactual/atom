<div ng-app="momaApp">

  <!-- Menu -->
  <ul class="nav nav-pills">
    <li><a ng-class="{ active: $route.current.activetab == 'dashboard' }" ng-href="#/dashboard">Dashboard</a></li>
    <li class="dropdown"><a data-toggle="dropdown" class="dropdown-toggle" ng-class="{ active: $route.current.activetab == 'artwork-record' }" tabindex="-1">Artwork Record<b class="caret"></b></a>
        <ul class="dropdown-menu" role="menu" aria-labelledby="">
            <li>
                <a ng-class="{ active: $route.current.activetab == 'artwork-record' }" ng-href="#/artwork-record" class="" role="menuitem" tabindex="-1">Artwork Record 1</a>
            </li>
            <li>
                <a ng-class="{ active: $route.current.activetab == 'artwork-record2' }" ng-href="#/artwork-record2" class="" role="menuitem" tabindex="-1">Artwork Record 2</a>
            </li>
        </ul>
    </li>
    <li><a ng-class="{ active: $route.current.activetab == 'technology-record' }" ng-href="#/technology-record">Technology Record</a></li>
  </ul>

  <!-- View placeholder -->
  <div ng-view />

</div>
