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

require_once(dirname(__FILE__).'/../../lib/helper/QubitHelper.php');

$t = new lime_test(19, new lime_output_color);

/**
 * qubit_auto_link_text()
 */

$t->diag('qubit_auto_link_text()');

$t->is(
  qubit_auto_link_text('Visit our "website":http://www.accesstomemory.org.'),
  'Visit our <a href="http://www.accesstomemory.org">website</a>.',
  'qubit_auto_link_text() converts Redmine links');

$t->is(
  qubit_auto_link_text('Email me "here":mailto:bob@bob.com'),
  'Email me <a href="mailto:bob@bob.com">here</a>',
  'qubit_auto_link_text() converts Redmine mailto links');

$t->is(
  qubit_auto_link_text('Foobar1 http://www.foobar1.com Foobar2 ftp://ftp.foobar2.com and special link "here":http://here.com.'),
  'Foobar1 <a href="http://www.foobar1.com">http://www.foobar1.com</a> Foobar2 <a href="ftp://ftp.foobar2.com">ftp://ftp.foobar2.com</a> and special link <a href="http://here.com">here</a>.',
  'qubit_auto_link_text() converts Redmine and URLs when they both appear in the same string');

$t->is(
  qubit_auto_link_text('&quot;Foobar&quot;:http://foobar.com/12345.'),
  '<a href="http://foobar.com/12345">Foobar</a>.',
  'qubit_auto_link_text() converts Redmine link with HTML entities (&quot;)');

$email_raw = 'fabien.potencier@symfony-project.com';
$email_result = '<a href="mailto:'.$email_raw.'">'.$email_raw.'</a>';
$link_raw = 'http://www.google.com';
$link_result = '<a href="'.$link_raw.'">'.$link_raw.'</a>';
$link2_raw = 'www.google.com';
$link2_result = '<a href="http://'.$link2_raw.'">'.$link2_raw.'</a>';

$t->is(qubit_auto_link_text('hello '.$email_raw, 'email_addresses'), 'hello '.$email_result, 'qubit_auto_link_text() converts emails to links');
$t->is(qubit_auto_link_text('Go to '.$link_raw, 'urls'), 'Go to '.$link_result, 'qubit_auto_link_text() converts absolute URLs to links');
$t->is(qubit_auto_link_text('Go to '.$link_raw, 'email_addresses'), 'Go to '.$link_raw, 'qubit_auto_link_text() takes a second parameter');
$t->is(qubit_auto_link_text('Go to '.$link_raw.' and say hello to '.$email_raw), 'Go to '.$link_result.' and say hello to '.$email_result, 'qubit_auto_link_text() converts emails and URLs if no second argument is given');
$t->is(qubit_auto_link_text('<p>Link '.$link_raw.'</p>'), '<p>Link '.$link_result.'</p>', 'qubit_auto_link_text() converts URLs to links');
$t->is(qubit_auto_link_text('<p>'.$link_raw.' Link</p>'), '<p>'.$link_result.' Link</p>', 'qubit_auto_link_text() converts URLs to links');
$t->is(qubit_auto_link_text('Go to '.$link2_raw, 'urls'), 'Go to '.$link2_result, 'qubit_auto_link_text() converts URLs to links even if link does not start with http://');
$t->is(qubit_auto_link_text('Go to '.$link2_raw, 'email_addresses'), 'Go to '.$link2_raw, 'qubit_auto_link_text() converts URLs to links');
$t->is(qubit_auto_link_text('<p>Link '.$link2_raw.'</p>'), '<p>Link '.$link2_result.'</p>', 'qubit_auto_link_text() converts URLs to links');
$t->is(qubit_auto_link_text('<p>'.$link2_raw.' Link</p>'), '<p>'.$link2_result.' Link</p>', 'qubit_auto_link_text() converts URLs to links');
$t->is(qubit_auto_link_text('<p>http://www.google.com/?q=symfony Link</p>'), '<p><a href="http://www.google.com/?q=symfony">http://www.google.com/?q=symfony</a> Link</p>', 'qubit_auto_link_text() converts URLs to links');
$t->is(qubit_auto_link_text('<p>http://twitter.com/#!/fabpot</p>'),'<p><a href="http://twitter.com/#!/fabpot">http://twitter.com/#!/fabpot</a></p>',"qubit_auto_link_text() converts URLs with complex fragments to links");
$t->is(qubit_auto_link_text('<p>http://twitter.com/#!/fabpot is Fabien Potencier on Twitter</p>'),'<p><a href="http://twitter.com/#!/fabpot">http://twitter.com/#!/fabpot</a> is Fabien Potencier on Twitter</p>', "qubit_auto_link_text() converts URLs with complex fragments and trailing text to links");
$t->is(qubit_auto_link_text('hello '.$email_result, 'email_addresses'), 'hello '.$email_result, "qubit_auto_link_text() does not double-link emails");
$t->is(qubit_auto_link_text('<p>Link '.$link_result.'</p>'), '<p>Link '.$link_result.'</p>', "qubit_auto_link_text() does not double-link emails");
