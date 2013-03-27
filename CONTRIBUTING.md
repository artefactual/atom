# How to contribute

Thank you for your interest in contributing code to the Access to Memory (AtoM) project! Third-party patches and community development help keep the AtoM project vibrant and responsive to our users' needs. We hope to simplify the contribution process as much as possible, and have included some simple guidelines here to help you get started.

Included here are some guidelines for contribution, which you can also find on our [wiki](https://www.qubit-toolkit.org/mediawiki/index.php?title=Contribute_code)

## Contributor's agreement

In order to accept any patches or code commits, contributors must first sign a Contributor's Agreement, available here as a [PDF](http://qubit-toolkit.org/accesstomemory-contributor-agreement.pdf).

### Why do I have to sign a contributor's agreement?

One of the key challenges for open source software is to support a collaborative development environment while protecting the rights of contributors and users over the long-term. This is especially true for the Qubit Toolkit which is used to create and receive contributions from multiple application distributions.

Unifying Qubit copyrights through contributor agreements is the best way to protect the availability and sustainability of Qubit over the long-term as free and open-source software. In all cases, contributors who sign the Contributor's Agreement retain full rights to use their original contributions for any other purpose outside of Qubit, while enabling Artefactual Systems, any successor Foundation which may eventually take over responsibility for Qubit, and the wider community of Qubit distribution users (e.g. ICA-AtoM) to benefit from their collaboration and contributions in this open source project.

Artefactual Systems has made the decision and has a proven track record of making our intellectual property available to the community at large. By standardizing contributions on these agreements the Qubit intellectual property position does not become too complicated. This ensures our resources are devoted to making our project the best they can be, rather than fighting legal battles over contributions.

The Qubit Contributor's Agreement is based almost verbatim on the Apache Foundation's individual contributor's license, available [here] (http://www.apache.org/licenses/icla.txt).

See some other similar examples from - [iRODS](https://www.irods.org/index.php/iRODS_Contributor%E2%80%99s_Agreement) and [Ubuntu](http://www.canonical.com/sites/default/files/active/images/Canonical-HA-CLA-ANY-I.pdf) (PDF).

## Send us the code

### Via patch

If you are new to Git, you may want to read their recommendations for how to commit to a project. See: http://git-scm.com/book/ch5-2.html

See espection the section on the "Public Large Project," which explains how to use git format-patch to send properly formatted patches to our developers. The process, in brief, is:

1. Clone or fork the [AtoM GitHub repository] (https://github.com/artefactual/atom)
1. Create a new local branch, ex: `git checkout -b nicefeature`
1. Write the code!
4. Creat a patch (or patches). ex: `git format-patch -M origin/master`
5. Paste the patch contents to a text file, and attach it to an email. Or, you can use: `git send-email`

Send your patches to our public developer's forum, at: qubit-dev@googlegroups.com

We prefer multiple small patches to a large patch that fixes multiple issues.

In recognition of your contribution, your name will be added to our [list of contributors](https://www.qubit-toolkit.org/wiki/Contributors)

Fame and Glory!

Please, when writing your commit messages, follow [these instructions](http://git-scm.com/book/en/Distributed-Git-Contributing-to-a-Project#Commit-Guidelines)

### Via pull request

We also accept GitHub pull requests. Please see the GitHub help page on [Using Pull Requests](https://help.github.com/articles/using-pull-requests) for more information.

Here are a few blog posts from around the web that offer more help and overviews on using pull requests:

* [SpringSouce Blog I](http://blog.springsource.org/2011/07/18/social-coding-pull-requests-what-to-do-when-things-get-complicated/)
* [SpringSource Blog II](http://blog.springsource.com/2010/12/21/social-coding-in-spring-projects/)
* [Otaku, Cedric's Blog](http://beust.com/weblog/2010/09/15/a-quick-guide-to-pull-requests/)

## Add license information to your patch

If you are making a bug fix or enhancement to an existing file, simply add your name as one of the authors in the file header:

<pre>
/**
 * Extended methods for information object model
 *
 * @package AccesstoMemory
 * @subpackage model
 * @author Peter Van Garderen <peter@artefactual.com>
 * @author David Juhasz <david@artefactual.com>
 * @author YourNameHere <youremail@address>
 */
</pre>

If you're contributing a new file, you need to add the following license header at the very top of the file. Copy both sections, in full, exactly as it is written here, filling in the information where indicated in brackets:

<pre>
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

</pre>

<pre>
 /**
 * [description of what your new file does]
 *
 * @package AccesstoMemory
 * @subpackage [name of AtoM module or component to which your file contributes]
 * @author YourNameHere <youremail@address>
 */
</pre>

Thanks for contributing!

## Additional resources

 * [Qubit Toolkit Developer's Forum](https://groups.google.com/forum/#!forum/qubit-dev)
 * [ICA-AtoM User's Forum](https://groups.google.com/forum/#!forum/ica-atom-users)
 * [AtoM Project Page and Issue Tracker](https://projects.artefactual.com/projects/atom)
 * [General GitHub documentation](http://help.github.com/)
 * [Artefactual Systems](http://www.artefactual.com/)
 * Qubit Toolkit [Administrator's Manual](https://www.qubit-toolkit.org/wiki/Administrator_manual)

You can always find AtoM project team members in our open IRC chat:
Channel: #openarchives (irc://#openarchives@irc.oftc.net)

The server address is irc.oftc.net, port 6667

Happy coding!
