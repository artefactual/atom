<div ng-app="momaApp">

  <!-- Menu -->
  <ul class="nav nav-pills">

    <li ng-class="{ active: $state.includes('dashboard') }">
      <a ui-sref="dashboard">Dashboard</a>
    </li>

    <li class="dropdown">
      <a data-toggle="dropdown" class="dropdown-toggle">Browse<b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li ng-class="{ active: $state.includes('aips.browser') }">
          <a ui-sref="aips.browser">AIPs</a>
        </li>
        <li ng-class="{ active: $state.includes('works.browser') }">
          <a ui-sref="works.browser">Works</a>
        </li>
      </ul>
    </li>

    <li class="dropdown">
      <a data-toggle="dropdown" class="dropdown-toggle">Prototypes<b class="caret"></b></a>
      <ul class="dropdown-menu">
        <li><a ui-sref="artwork-record">Artwork record (1)</a></li>
        <li><a ui-sref="artwork-record-2">Artwork record (2)</a></li>
        <li><a ui-sref="technology-record">Technology record</a></li>
      </ul>
    </li>

  </ul>

  <!-- View placeholder -->
  <ui-view/>

</div>
