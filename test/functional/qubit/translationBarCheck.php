<?php

//
// test/translate-bar-check.php — verifies the QubitTranslateBarAccess patch.
//
// Drives an in-process test browser (sfBrowser, same harness as the
// project's functional tests) and asserts on the #l10n-client footer bar:
//
//   1. anonymous visitor (control)                                -> NO bar
//   2. admin-group user, blanket "all" grant only                 -> NO bar
//   3. same user + explicit translate grant (en), culture en      -> bar present
//   4. same user, UI culture switched to fr (en-only grant)       -> NO bar
//
// Authentication note: this fork authenticates exclusively via CAS
// (casUser::authenticate -> phpCAS::forceAuthentication), which cannot
// run against a real CAS server in the test container. casUser::authenticate
// ultimately calls myUser::signIn() with the resolved QubitUser, so this
// script signs the test user in through that same entry point instead of
// POSTing to /user/login.
//
// Usage (from the repo root, inside the test container):
//   php -d xdebug.mode=off test/translate-bar-check.php
//
// Exits 0 if all checks pass, 1 otherwise.

chdir('/atom/src');

error_reporting(E_ALL & ~E_DEPRECATED);

require '/atom/src/config/ProjectConfiguration.class.php';

$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

$failures = 0;

/**
 * Load the homepage (renders the shared footer) and check for the bar.
 *
 * @param string        $label      Description of the scenario for the report line
 * @param sfBrowserBase $browser    In-process test browser
 * @param bool          $expectBar  Whether #l10n-client should be present
 * @param bool          $expectAuth Whether the browser user should be authenticated
 *
 * @return int 0 on success, 1 on failure
 */
function assertBar(string $label, sfBrowserBase $browser, bool $expectBar, bool $expectAuth): int
{
    $browser->get('/');

    $body = (string) $browser->getResponse()->getContent();
    $hasBar = false !== strpos($body, 'id="l10n-client"');
    $authenticated = (bool) $browser->getUser()->isAuthenticated();

    $ok = ($hasBar === $expectBar) && ($authenticated === $expectAuth);
    printf(
        "%s  %-55s auth=%-3s bar=%-7s (expected bar=%s, auth=%s)\n",
        $ok ? 'PASS' : 'FAIL',
        $label,
        $authenticated ? 'yes' : 'no',
        $hasBar ? 'present' : 'absent',
        $expectBar ? 'present' : 'absent',
        $expectAuth ? 'yes' : 'no'
    );

    return $ok ? 0 : 1;
}

$browser = new sfBrowser();

$failures += assertBar('1. anonymous visitor (control)', $browser, false, false);

// --- Test user: member of administrator group (100) -> blanket "all" grant ---
// Clean up any leftovers from a previous run
$email = 'bar-tester@example.com';
$oldCriteria = new Criteria();
$oldCriteria->add(QubitUser::EMAIL, $email);
$old = QubitUser::getOne($oldCriteria);
if (null !== $old) {
    $old->delete();
}

$tester = new QubitUser();
$tester->username = $email;
$tester->email = $email;
$tester->setPassword('test1234');
$tester->save();

$userGroup = new QubitAclUserGroup();
$userGroup->setUserId($tester->id);
$userGroup->setGroupId(QubitAclGroup::ADMINISTRATOR_ID);
$userGroup->save();

// Sign in. The fork authenticates via CAS (casUser::authenticate), which
// cannot run against a real CAS server in the test container, so we seed the
// browser's test session file directly with the same data myUser::signIn()
// would leave after a successful login. NOTE: we cannot use
// $browser->getUser()->signIn() here — that would sign in the bootstrap
// sfContext's user, whose storage uses a different session id than the
// browser's, and its shutdown() clobbers the browser's .session file.
// The browser's session id is fixed for its whole lifetime (set in the
// sfBrowserBase constructor and restored on every doCall).
$sessionFile = sfConfig::get('sf_app_cache_dir').'/test/sessions/'.$_SERVER['session_id'].'.session';
if (!is_dir(dirname($sessionFile))) {
    mkdir(dirname($sessionFile), 0777, true);
}
file_put_contents($sessionFile, serialize([
    'symfony/user/sfUser/authenticated' => true,
    'symfony/user/sfUser/credentials' => [],
    'symfony/user/sfUser/lastRequest' => time(),
    'symfony/user/sfUser/attributes' => [
        // Nested under the holder's default namespace
        // (sfUser::ATTRIBUTE_NAMESPACE), matching how sfUser::shutdown()
        // serializes attributeHolder namespaces.
        'symfony/user/sfUser/attributes' => [
            'user_id' => $tester->id,
            'user_slug' => $tester->slug,
            'user_name' => $tester->username,
        ],
    ],
]));

$failures += assertBar('2. admin-group user, blanket "all" grant only', $browser, false, true);

// --- Add an explicit translate grant (en), like the user-edit checkbox ---
$permission = new QubitAclPermission();
$permission->setUserId($tester->id);
$permission->setObjectId(null);
$permission->setAction('translate');
$permission->setGrantDeny(1); // 1 = Allow (QubitAcl maps grant_deny 1 -> Zend GRANT)
$permission->setConditional('in_array(%p[language], %k[languages])');
$permission->setConstants(['languages' => ['en']]);
$permission->save();

$failures += assertBar('3. same user + explicit translate grant (en)', $browser, true, true);

// --- Switch UI culture to fr: the en-only conditional should now fail ---
$browser->get('/?sf_culture=fr');

$body = (string) $browser->getResponse()->getContent();
$hasBar = false !== strpos($body, 'id="l10n-client"');
$culture = $browser->getUser()->getCulture();
$ok = !$hasBar && 'fr' === $culture;
printf(
    "%s  %-55s culture=%s bar=%-7s (expected bar=absent, culture=fr)\n",
    $ok ? 'PASS' : 'FAIL',
    '4. same user, UI culture fr (en-only grant)',
    $culture,
    $hasBar ? 'present' : 'absent'
);
$failures += $ok ? 0 : 1;

// --- Cleanup ---
$permission->delete();
$userGroup->delete();
$tester->delete();

echo $failures
    ? "\nRESULT: {$failures} check(s) FAILED\n"
    : "\nRESULT: all checks passed\n";

exit($failures ? 1 : 0);
