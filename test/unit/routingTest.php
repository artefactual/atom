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

require_once dirname(__FILE__).'/../bootstrap/unit.php';

$t = new lime_test(124, new lime_output_color());

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$t->diag('Loading database settings.');
sfConfig::add(QubitSetting::getSettingsArray());

$t->diag('Starting routing system.');
$routing = sfContext::getInstance()->getRouting();
$routingOptions = $routing->getOptions();
$routingOptions['context']['prefix'] = '';
$routing->initialize(sfContext::getInstance()->getEventDispatcher(), $routing->getCache(), $routingOptions);

$t->diag('Creeate QubitInformationObject "peanut-12345"');
if (null !== $io = QubitObject::getBySlug('peanut-12345')) {
    $io->delete();
}
$io = new QubitInformationObject();
$io->parentId = QubitInformationObject::ROOT_ID;
$io->slug = 'peanut-12345';
$io->save();

$t->diag('Create QubitActor "actor-12345"');
if (null !== $actor = QubitObject::getBySlug('actor-12345')) {
    $actor->delete();
}
$actor = new QubitActor();
$actor->parentId = QubitActor::ROOT_ID;
$actor->slug = 'actor-12345';
$actor->save();

$t->diag('Create QubitRepository "repository-12345"');
if (null !== $repository = QubitObject::getBySlug('repository-12345')) {
    $repository->delete();
}
$repository = new QubitRepository();
$repository->parentId = QubitRepository::ROOT_ID;
$repository->slug = 'repository-12345';
$repository->save();

$t->diag('Create QubitFunctionObject "function-12345"');
if (null !== $function = QubitObject::getBySlug('function-12345')) {
    $function->delete();
}
$function = new QubitFunctionObject();
$function->slug = 'function-12345';
$function->save();

$t->diag('Create QubitTaxonomy "taxonomy-12345"');
if (null !== $taxonomy = QubitObject::getBySlug('taxonomy-12345')) {
    $taxonomy->delete();
}
$taxonomy = new QubitTaxonomy();
$taxonomy->parentId = QubitTaxonomy::ROOT_ID;
$taxonomy->slug = 'taxonomy-12345';
$taxonomy->name = 'Taxonomy 12345';
$taxonomy->save();

$t->diag('Create QubitContactInformation');
$contactInformation = new QubitContactInformation();
$contactInformation->actor = $actor;
$contactInformation->save();

// Test generation of routes
$t->diag('Test suite intended to check behaviour of ->generate()');

$t->diag('Test /');
$uri = $routing->generate('homepage');
$t->is($uri, '/', '"->generate(\'homepage\')" returns "/"');
$info = $routing->parse('/');
$t->is($routing->getCurrentRouteName(), 'homepage', 'Url "/" is matched with route "homepage"');
$t->is($info['module'], 'staticpage', '... with module="staticpage"');
$t->is($info['action'], 'home', '... and action="home"');

/*
$t->diag('Test oai_harvester_harvest_all_sets');
$uri = $routing->generate(null, array('module' => 'qtOaiPlugin', 'action' => 'harvesterHarvest', 'id' => '12345', 'type' => 'type'));
$t->is($uri, '/oai/harvest/type/12345');
$info = $routing->parse('/oai/harvest/type/12345');
$t->is($routing->getCurrentRouteName(), 'oai_harvester_harvest_all_sets');
$t->is($info['module'], 'qtOaiPlugin');
$t->is($info['action'], 'harvesterHarvest');

$t->diag('Test oai_harvester_delete');
$uri = $routing->generate(null, array('module' => 'qtOaiPlugin', 'action' => 'harvesterDelete', 'repositoryId' => '12345'));
$t->is($uri, '/oai/deleteRepository/12345');
$info = $routing->parse('/oai/deleteRepository/12345');
$t->is($routing->getCurrentRouteName(), 'oai_harvester_delete');
$t->is($info['module'], 'qtOaiPlugin');
$t->is($info['action'], 'harvesterDelete');

$t->diag('Test oai_requests');
$uri = $routing->generate(null, array('module' => 'qtOaiPlugin', 'action' => 'oai'));
$t->is($uri, '/oai/request');
$info = $routing->parse('/oai/request');
$t->is($routing->getCurrentRouteName(), 'oai_requests');
$t->is($info['module'], 'qtOaiPlugin');
$t->is($info['action'], 'oai');
*/

$t->diag('Test /peanut-12345/addDigitalObject (route informationobject/action)');
$uri = $routing->generate(null, [$io, 'action' => 'addDigitalObject']);
$t->is($uri, '/peanut-12345/addDigitalObject');
$info = $routing->parse('/peanut-12345/addDigitalObject');
$t->is($routing->getCurrentRouteName(), 'informationobject/action');
$t->is($info['module'], 'informationobject');
$t->is($info['action'], 'addDigitalObject');

$t->diag('Test /peanut-12345/multiFileUpload (route informationobject/action)');
$uri = $routing->generate(null, [$io, 'action' => 'multiFileUpload']);
$t->is($uri, '/peanut-12345/multiFileUpload');
$info = $routing->parse('/peanut-12345/multiFileUpload');
$t->is($routing->getCurrentRouteName(), 'informationobject/action');
$t->is($info['module'], 'informationobject');
$t->is($info['action'], 'multiFileUpload');

$t->diag('Test sword/action/slug route');
$uri = $routing->generate(null, [$io, 'module' => 'qtSwordPlugin', 'action' => 'deposit']);
$t->is($uri, '/sword/deposit/peanut-12345');
$info = $routing->parse('/sword/deposit/peanut-12345');
$t->is($routing->getCurrentRouteName(), 'sword/action/slug');
$t->is($info['module'], 'qtSwordPlugin');
$t->is($info['action'], 'deposit');

$t->diag('Test sword route');
$uri = $routing->generate(null, ['module' => 'qtSwordPlugin', 'action' => 'servicedocument']);
$t->is($uri, '/sword/servicedocument');
$info = $routing->parse('/sword/servicedocument');
$t->is($routing->getCurrentRouteName(), 'sword');
$t->is($info['module'], 'qtSwordPlugin');
$t->is($info['action'], 'servicedocument');

$t->diag('Test route id/default');
$uri = $routing->generate(null, ['module' => 'informationobject', 'action' => 'foobar', 'id' => '12345']);
$t->is($uri, '/informationobject/foobar/id/12345');
$info = $routing->parse('/informationobject/foobar/id/12345');
$t->is($routing->getCurrentRouteName(), 'id/default');
$t->is($info['module'], 'informationobject');
$t->is($info['action'], 'foobar');
$t->is($info['id'], '12345');

$t->diag('Test route id/default passing contactinformation');
$uri = $routing->generate(null, [$contactInformation, 'module' => 'contactinformation']);
$t->is($uri, '/contactinformation/index/id/'.$contactInformation->id);
$info = $routing->parse('/contactinformation/index/id/'.$contactInformation->id);
$t->is($routing->getCurrentRouteName(), 'id/default');
$t->is($info['module'], 'contactinformation');
$t->is($info['action'], 'index');
$t->is($info['id'], $contactInformation->id);

$t->diag('Test route slug/default');
$uri = $routing->generate(null, [$io, 'module' => 'foo', 'action' => 'bar']);
$t->is($uri, '/peanut-12345/foo/bar');
$info = $routing->parse('/peanut-12345/foo/bar');
$t->is($routing->getCurrentRouteName(), 'slug/default');
$t->is($info['module'], 'foo');
$t->is($info['action'], 'bar');

$t->diag('Test route default_index');
$uri = $routing->generate(null, ['module' => 'search']);
$t->is($uri, '/search');
$info = $routing->parse('/search');
$t->is($routing->getCurrentRouteName(), 'default_index');
$t->is($info['module'], 'search');
$t->is($info['action'], 'index');

$t->diag('Test route default');
$uri = $routing->generate(null, ['module' => 'foo', 'action' => 'bar']);
$t->is($uri, '/foo/bar');
$info = $routing->parse('/foo/bar');
$t->is($routing->getCurrentRouteName(), 'default');
$t->is($info['module'], 'foo');
$t->is($info['action'], 'bar');

// #
// QubitMetadataResource
//

$ioTemplates = ['dc' => 'sfDcPlugin', 'isad' => 'sfIsadPlugin', 'mods' => 'sfModsPlugin', 'rad' => 'sfRadPlugin', 'dacs' => 'arDacsPlugin'];

$defaultIoTemplateCode = sfConfig::get('app_default_template_informationobject');
$defaultIoTemplateModule = $ioTemplates[$defaultIoTemplateCode];

foreach ($ioTemplates as $code => $module) {
    $t->diag('Uri /peanut-12345;'.$code);
    $uri = $routing->generate(null, [$io, 'template' => $code]);
    $t->is($uri, '/peanut-12345;'.$code);
    $info = $routing->parse('/peanut-12345;'.$code);
    $t->is($routing->getCurrentRouteName(), 'slug;template');
    $t->is($info['module'], $module);
    $t->is($info['action'], 'index');
}

foreach (QubitTaxonomy::getTaxonomyTerms(QubitTaxonomy::INFORMATION_OBJECT_TEMPLATE_ID) as $term) {
    // Update object
    $io->displayStandardId = $term->id;
    $io->save();
    $t->diag('/peanut-12345 (displayStandardId points to '.$term->code.')');
    $info = $routing->parse('/peanut-12345');
    $t->is($routing->getCurrentRouteName(), 'slug');
    $t->is($info['module'], $ioTemplates[$term->code], $ioTemplates[$term->code]);
    $t->is($info['action'], 'index');
}

$io->displayStandardId = null;
$io->save();
$t->diag('/peanut-12345 (displayStandardId is NULL, default application template is '.$defaultIoTemplateCode.')');
$info = $routing->parse('/peanut-12345');
$t->is($routing->getCurrentRouteName(), 'slug');
$t->is($info['module'], $defaultIoTemplateModule, $defaultIoTemplateModule);
$t->is($info['action'], 'index');

$t->diag('/peanut-12345/edit');
$uri = $routing->generate(null, [$io, 'module' => 'informationobject', 'action' => 'edit']);
$t->is($uri, '/peanut-12345/edit');
$info = $routing->parse('/peanut-12345/edit');
$t->is($routing->getCurrentRouteName(), 'edit');
$t->is($info['module'], $defaultIoTemplateModule); // We know now that displayStandardId is NULL
$t->is($info['action'], 'edit');

$t->diag('/peanut-12345/copy');
$uri = $routing->generate(null, [$io, 'module' => 'informationobject', 'action' => 'copy']);
$t->is($uri, '/peanut-12345/copy');
$info = $routing->parse('/peanut-12345/copy');
$t->is($routing->getCurrentRouteName(), 'copy');
$t->is($info['module'], $defaultIoTemplateModule); // We know now that displayStandardId is NULL
$t->is($info['action'], 'edit'); // Reusing edit template

$t->diag('/informationobject/add');
$uri = $routing->generate(null, ['module' => 'informationobject', 'action' => 'add']);
$t->is($uri, '/informationobject/add');
$info = $routing->parse('/informationobject/add');
$t->is($routing->getCurrentRouteName(), 'add');
$t->is($info['module'], $defaultIoTemplateModule); // We know now that displayStandardId is NULL
$t->is($info['action'], 'edit'); // Reusing edit template

$t->diag('/repository/add');
$uri = $routing->generate(null, ['module' => 'repository', 'action' => 'add']);
$t->is($uri, '/repository/add');
$info = $routing->parse('/repository/add');
$t->is($routing->getCurrentRouteName(), 'add');
$t->is($info['module'], 'sfIsdiahPlugin', 'sfIsdiahPlugin');
$t->is($info['action'], 'edit');

$t->diag('/actor/add');
$uri = $routing->generate(null, ['module' => 'actor', 'action' => 'add']);
$t->is($uri, '/actor/add');
$info = $routing->parse('/actor/add');
$t->is($routing->getCurrentRouteName(), 'add');
$t->is($info['module'], 'sfIsaarPlugin', 'sfIsaarPlugin');
$t->is($info['action'], 'edit');

$t->diag('/function/add');
$uri = $routing->generate(null, ['module' => 'function', 'action' => 'add']);
$t->is($uri, '/function/add');
$info = $routing->parse('/function/add');
$t->is($routing->getCurrentRouteName(), 'add');
$t->is($info['module'], 'sfIsdfPlugin', 'sfIsdfPlugin');
$t->is($info['action'], 'edit');

$t->diag('/foo/bar/peanut-12345 and /foo/bar with ->generate()');
$uri = $routing->generate(null, [$io, 'module' => 'foo', 'action' => 'bar']);
$t->is($uri, '/peanut-12345/foo/bar');
$uri = $routing->generate(null, ['module' => 'foo', 'action' => 'bar']);
$t->is($uri, '/foo/bar');

$t->diag('/actor-12345');
$uri = $routing->generate(null, [$actor]);
$t->is($uri, '/actor-12345');
$info = $routing->parse('/actor-12345');
$t->is($routing->getCurrentRouteName(), 'slug');
$t->is($info['module'], 'sfIsaarPlugin', 'sfIsaarPlugin');
$t->is($info['action'], 'index');

$t->diag('/repository-12345');
$uri = $routing->generate(null, [$repository]);
$t->is($uri, '/repository-12345');
$info = $routing->parse('/repository-12345');
$t->is($routing->getCurrentRouteName(), 'slug');
$t->is($info['module'], 'sfIsdiahPlugin', 'sfIsdiahPlugin');
$t->is($info['action'], 'index');

$t->diag('/function-12345');
$uri = $routing->generate(null, [$function]);
$t->is($uri, '/function-12345');
$info = $routing->parse('/function-12345');
$t->is($routing->getCurrentRouteName(), 'slug');
$t->is($info['module'], 'sfIsdfPlugin', 'sfIsdfPlugin');
$t->is($info['action'], 'index');

$t->diag('/peanut-12345 (slug passed as parameter instead of object)');
$uri = $routing->generate(null, ['module' => 'informationobject', 'slug' => 'peanut-12345']);
$t->is($uri, '/peanut-12345');

$t->diag('/peanut-12345/edit (slug passed as parameter instead of object)');
$uri = $routing->generate(null, ['module' => 'informationobject', 'action' => 'edit', 'slug' => 'peanut-12345']);
$t->is($uri, '/peanut-12345/edit');
