<div ng-app="momaApp">

  <!-- Menu -->
  <ul class="nav nav-pills">
    <li><a ng-class="{ active: $route.current.activetab == 'dashboard' }" ng-href="#/dashboard">Dashboard</a></li>
    <li><a ng-class="{ active: $route.current.activetab == 'artwork-record' }" ng-href="#/artwork-record" tabindex="-1">Artwork Record</a></li>
    <li><a ng-class="{ active: $route.current.activetab == 'technology-record' }" ng-href="#/technology-record">Technology Record</a></li>
  </ul>

  <!-- View placeholder -->
  <div ng-view />

</div>
