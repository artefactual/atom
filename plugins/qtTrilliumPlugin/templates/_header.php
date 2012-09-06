<div id="header">

  <div>

    <div id="first-level">

      <div>

        <?php if ($sf_user->isAuthenticated()): ?>
          <ul>
            <?php echo get_component('menu', 'mainMenu', array('sf_cache_key' => $sf_user->getCulture().$sf_user->getUserID())) ?>
          </ul>
        <?php endif; ?>

        <div id="update-check">
          <?php echo get_component('default', 'updateCheck') ?>
        </div>

        <ul id="options-menu">

          <?php echo get_component_slot('header') ?>

          <li class="menu">
            <?php echo get_component('i18n', 'changeLanguageList') ?>
          </li>

          <li class="menu" style="display: none;">
            <a class="menu" href="#"><?php echo __('Help') ?></a>
            <ul>
              <li><?php echo link_to(__('About'), array('module' => 'staticpage', 'slug' => 'about')) ?></li>
              <li><?php echo link_to(__('?'), array('module' => 'staticpage', 'slug' => 'help')) ?></li>
             </ul>
          </li>

          <li class="menu">
            <?php echo get_component('menu', 'quickLinks') ?>
          </li>

          <?php if ($sf_user->isAuthenticated()): ?>
            <li class="menu user">
              <a class="menu" href="#"><?php echo $sf_user->user->__toString() ?></a>
                <ul>
                  <li><?php echo link_to(__('Profile'), array($sf_user->user, 'module' => 'user')) ?></li>
                  <li><?php echo link_to(__('Log out'), array('module' => 'user', 'action' => 'logout')) ?></li>
                </ul>
            </li>
          <?php else: ?>
            <li><?php echo link_to(__('Log in'), array('module' => 'user', 'action' => 'login')) ?></li>
          <?php endif; ?>

        </ul>

      </div>

    </div> <!-- /#first-level -->

    <div id="second-level">
      <div>

        <div id="logo-and-name">
          <table>
            <tr>
              <td>
                <?php if (sfConfig::get('app_toggleLogo')): ?>
                  <?php echo link_to(image_tag('logo', array('alt' => __('Home'))), '@homepage', array('id' => 'logo', 'rel' => 'home', 'title' => __('Home'))) ?>
                <?php endif; ?>
              </td><td width="99%">
                <?php if (sfConfig::get('app_toggleTitle')): ?>
                  <h1>
                    <?php echo link_to(sfConfig::get('app_siteTitle'), '@homepage', array('rel' => 'home', 'title' => __('Home'))) ?>
                  </h1>
                <?php endif; ?>
              </td>
            </tr>
          </table>
        </div>

        <?php echo get_component('search', 'box') ?>

      </div>
    </div> <!-- /#second-level -->

  </div>

</div>
