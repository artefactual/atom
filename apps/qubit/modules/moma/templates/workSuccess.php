<div ng-app="momaApp">

  <!-- Menu -->
  <ul class="nav nav-pills">
    <li><a ng-href="#/">Home</a></li>

    <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown">Tests
            <b class="caret"></b>
        </a>
        <ul class="dropdown-menu" role="menu" >
            <li role="presentation">
                <a role="menuitem" ng-href="#/test"tabindex="-1">Test 1 Column</a>
            </li>
            <li role="presentation">
                <a role="menuitem" ng-href="#/test2"tabindex="-1">Test 2 Columns</a>
            </li>
        </ul>
    </li>

    <li>
      <a ng-href="#/dashboard">Dashboard</a>
    </li>

    <li>
      <a ng-href="#/documentationObject">Doc Obj</a>
    </li>
  </ul>

  <!-- View placeholder -->
  <div ng-view />

</div>
