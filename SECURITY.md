# Security Policy

This document outlines security procedures and general policies for the Access
to Memory (AtoM) project. For more general information on AtoM, see:

* https://www.accesstomemory.org.

**Contents**

* [Reporting a security vulnerability](#reporting-a-security-vulnerability)
* [Disclosure policy](#disclosure-policy)
* [Supported versions](#supported-versions)
* [AtoM and security overview](#atom-and-security-overview)
* [Reporting general bugs](#reporting-general-bugs)

## Reporting a security vulnerability

The AtoM development team takes security seriously and will investigate all
reported vulnerabilities. Thank you for your interest in helping!

If you would like to report a vulnerability or have a security concern
regarding AtoM, **please do not file a public issue in our GitHub
repository.** Instead, please email a report to:

* [security@artefactual.com](mailto:security@artefactual.com)

We will be better able to evaluate and respond to your report if it includes
all the details needed for us to reproduce the issue locally. Please include
the following information in your email:

* The version of AtoM you are using
* Basic information about your installation environment, including PHP, MySQL,
  Elasticsearch, and operating system versions
* Steps to reproduce the issue The resulting error or vulnerability
* If there are any error logs related to the issue, please include the
  relevant parts as well

Your report will be acknowledged within 2 business days, and we’ll follow up
with a more detailed response indicating the next steps we intend to take
within 1 week.

If you haven’t received a reply to your submission after 5 business days of
the original report, there are a couple steps you can take:

* Email the AtoM Program Manager directly: dan@artefactual.com 
* Join the [AtoM user forum](https://groups.google.com/forum/#!forum/ica-atom-users)

Please note that the AtoM user forum is a public forum not suitable for
discussing security vulnerabilities without potentially impacting other users.
**When escalating in the User forum, please do not discuss your issue**.
Simply say that you are trying to get a hold of someone from the AtoM
development team, and we will follow up with you off-list.

Any information you share with the AtoM development team as a part of this
process will be kept confidential within the team. If we determine that the
vulnerability is located upstream in one of the libraries or dependencies that
AtoM uses, we may need to share some information about the report with the
dependency’s core team - in this case, we will notify you before proceeding.

If the vulnerability is first reported by you, we will credit you with the
discovery in the public disclosure, unless you tell us you would prefer to
remain anonymous. 

## Disclosure policy 

When the AtoM development team receives a security bug report, we will assign
it to a primary handler. This person will coordinate the fix and release
process, involving the following steps:

* Confirm the problem and determine the affected versions;
* Audit code to find any similar potential problems;
* Prepare fixes for all releases still under maintenance. These fixes will be
  released as fast as possible.

Once new releases and/or security patches have been prepared, tested, and made
publicly available, we will also make a post in the AtoM user forum advising
users of the issue, and encouraging them to upgrade (or apply the supplied
patch) as soon as possible. Any internal tickets created in our issue tracker
related to the issue will be made public after disclosure, and referenced in
the release notes for the new version(s). 

## Supported versions

In the case of a confirmed security issue, we will add the fix to the most
recent stable branch, and the development branch (prefaced by `qa/` in our
branch naming conventions. For more information on this, see 
[this section](https://wiki.accesstomemory.org/Resources/Code_repository#Branch_organization)
of our wiki). If the severity of the issue is high, we may in some cases also
backport the fix to the previous stable branch as well (e.g. `stable.2.5.x`,
etc), so that community users running a legacy version have the option of
adding the fix as a patch to their local installations. We will attempt to
ensure that fixes, and/or a confirmed workaround that resolves the security
issue, are available prior to disclosing any security issues publicly.

## AtoM and security overview

AtoM’s security guidelines are available here: 

* https://www.accesstomemory.org/docs/latest/#security

AtoM also provides additional security measures via the administrative
settings in the user interface - see:

* https://www.accesstomemory.org/docs/latest/user-manual/administer/settings/#security-panel

To provide greater redundancy in the case of something going wrong, users may
be interested in learning more about AtoM’s 2-site deployment model and
replication script. For more information, see:

* https://www.slideshare.net/accesstomemory/2-sitereplication-with-atom
* https://github.com/artefactual-labs/atom-replication

## Reporting general bugs

If you have discovered an issue in AtoM that is **not related to a security
vulnerability**, we welcome your reports. Please see our CONTRIBUTING.md file
for more information.