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

/**
 * Extend functionality of propel generated "BaseDigitalObject" class
 *
 * @package    AccesstoMemory
 * @subpackage model
 * @author     David Juhasz <david@artefactual.com>
 */
class QubitDigitalObject extends BaseDigitalObject
{
  const

    // Directory for generic icons
    GENERIC_ICON_DIR = 'generic-icons',

    // Mime-type for thumbnails (including reference image)
    THUMB_MIME_TYPE = 'image/jpeg',

    THUMB_EXTENSION = 'jpg';

  // Variables for save actions
  public
    $assets = array(),
    $indexOnSave = true, // Flag for updating search index on save or delete
    $createDerivatives = true;

  /*
   * The following mime-type array is taken from the Gallery 2 project
   * http://gallery.menalto.com
   */
  public static
    $qubitMimeTypes = array(

      /* This data was lifted from Apache's mime.types listing. */
      '123' => 'application/vnd.lotus-1-2-3',
      '3dml' => 'text/vnd.in3d.3dml',
      '3ds' => 'image/x-3ds',
      '3g2' => 'video/3gpp2',
      '3gp' => 'video/3gpp',
      '7z' => 'application/x-7z-compressed',
      'aab' => 'application/x-authorware-bin',
      'aac' => 'audio/x-aac',
      'aam' => 'application/x-authorware-map',
      'aas' => 'application/x-authorware-seg',
      'abw' => 'application/x-abiword',
      'ac' => 'application/pkix-attr-cert',
      'acc' => 'application/vnd.americandynamics.acc',
      'ace' => 'application/x-ace-compressed',
      'acu' => 'application/vnd.acucobol',
      'acutc' => 'application/vnd.acucorp',
      'adp' => 'audio/adpcm',
      'aep' => 'application/vnd.audiograph',
      'afm' => 'application/x-font-type1',
      'afp' => 'application/vnd.ibm.modcap',
      'ahead' => 'application/vnd.ahead.space',
      'ai' => 'application/postscript',
      'aif' => 'audio/x-aiff',
      'aifc' => 'audio/x-aiff',
      'aiff' => 'audio/x-aiff',
      'air' => 'application/vnd.adobe.air-application-installer-package+zip',
      'ait' => 'application/vnd.dvb.ait',
      'ami' => 'application/vnd.amiga.ami',
      'apk' => 'application/vnd.android.package-archive',
      'appcache' => 'text/cache-manifest',
      'application' => 'application/x-ms-application',
      'apr' => 'application/vnd.lotus-approach',
      'arc' => 'application/x-freearc',
      'asc' => 'application/pgp-signature',
      'asf' => 'video/x-ms-asf',
      'asm' => 'text/x-asm',
      'aso' => 'application/vnd.accpac.simply.aso',
      'asx' => 'video/x-ms-asf',
      'atc' => 'application/vnd.acucorp',
      'atom' => 'application/atom+xml',
      'atomcat' => 'application/atomcat+xml',
      'atomsvc' => 'application/atomsvc+xml',
      'atx' => 'application/vnd.antix.game-component',
      'au' => 'audio/basic',
      'avi' => 'video/x-msvideo',
      'aw' => 'application/applixware',
      'azf' => 'application/vnd.airzip.filesecure.azf',
      'azs' => 'application/vnd.airzip.filesecure.azs',
      'azw' => 'application/vnd.amazon.ebook',
      'bat' => 'application/x-msdownload',
      'bcpio' => 'application/x-bcpio',
      'bdf' => 'application/x-font-bdf',
      'bdm' => 'application/vnd.syncml.dm+wbxml',
      'bed' => 'application/vnd.realvnc.bed',
      'bh2' => 'application/vnd.fujitsu.oasysprs',
      'bin' => 'application/octet-stream',
      'blb' => 'application/x-blorb',
      'blorb' => 'application/x-blorb',
      'bmi' => 'application/vnd.bmi',
      'bmp' => 'image/bmp',
      'book' => 'application/vnd.framemaker',
      'box' => 'application/vnd.previewsystems.box',
      'boz' => 'application/x-bzip2',
      'bpk' => 'application/octet-stream',
      'btif' => 'image/prs.btif',
      'bz' => 'application/x-bzip',
      'bz2' => 'application/x-bzip2',
      'c' => 'text/x-c',
      'c11amc' => 'application/vnd.cluetrust.cartomobile-config',
      'c11amz' => 'application/vnd.cluetrust.cartomobile-config-pkg',
      'c4d' => 'application/vnd.clonk.c4group',
      'c4f' => 'application/vnd.clonk.c4group',
      'c4g' => 'application/vnd.clonk.c4group',
      'c4p' => 'application/vnd.clonk.c4group',
      'c4u' => 'application/vnd.clonk.c4group',
      'cab' => 'application/vnd.ms-cab-compressed',
      'caf' => 'audio/x-caf',
      'cap' => 'application/vnd.tcpdump.pcap',
      'car' => 'application/vnd.curl.car',
      'cat' => 'application/vnd.ms-pki.seccat',
      'cb7' => 'application/x-cbr',
      'cba' => 'application/x-cbr',
      'cbr' => 'application/x-cbr',
      'cbt' => 'application/x-cbr',
      'cbz' => 'application/x-cbr',
      'cc' => 'text/x-c',
      'cct' => 'application/x-director',
      'ccxml' => 'application/ccxml+xml',
      'cdbcmsg' => 'application/vnd.contact.cmsg',
      'cdf' => 'application/x-netcdf',
      'cdkey' => 'application/vnd.mediastation.cdkey',
      'cdmia' => 'application/cdmi-capability',
      'cdmic' => 'application/cdmi-container',
      'cdmid' => 'application/cdmi-domain',
      'cdmio' => 'application/cdmi-object',
      'cdmiq' => 'application/cdmi-queue',
      'cdx' => 'chemical/x-cdx',
      'cdxml' => 'application/vnd.chemdraw+xml',
      'cdy' => 'application/vnd.cinderella',
      'cer' => 'application/pkix-cert',
      'cfs' => 'application/x-cfs-compressed',
      'cgm' => 'image/cgm',
      'chat' => 'application/x-chat',
      'chm' => 'application/vnd.ms-htmlhelp',
      'chrt' => 'application/vnd.kde.kchart',
      'cif' => 'chemical/x-cif',
      'cii' => 'application/vnd.anser-web-certificate-issue-initiation',
      'cil' => 'application/vnd.ms-artgalry',
      'cla' => 'application/vnd.claymore',
      'class' => 'application/java-vm',
      'clkk' => 'application/vnd.crick.clicker.keyboard',
      'clkp' => 'application/vnd.crick.clicker.palette',
      'clkt' => 'application/vnd.crick.clicker.template',
      'clkw' => 'application/vnd.crick.clicker.wordbank',
      'clkx' => 'application/vnd.crick.clicker',
      'clp' => 'application/x-msclip',
      'cmc' => 'application/vnd.cosmocaller',
      'cmdf' => 'chemical/x-cmdf',
      'cml' => 'chemical/x-cml',
      'cmp' => 'application/vnd.yellowriver-custom-menu',
      'cmx' => 'image/x-cmx',
      'cod' => 'application/vnd.rim.cod',
      'com' => 'application/x-msdownload',
      'conf' => 'text/plain',
      'cpio' => 'application/x-cpio',
      'cpp' => 'text/x-c',
      'cpt' => 'application/mac-compactpro',
      'crd' => 'application/x-mscardfile',
      'crl' => 'application/pkix-crl',
      'crt' => 'application/x-x509-ca-cert',
      'cryptonote' => 'application/vnd.rig.cryptonote',
      'csh' => 'application/x-csh',
      'csml' => 'chemical/x-csml',
      'csp' => 'application/vnd.commonspace',
      'css' => 'text/css',
      'cst' => 'application/x-director',
      'csv' => 'text/csv',
      'cu' => 'application/cu-seeme',
      'curl' => 'text/vnd.curl',
      'cww' => 'application/prs.cww',
      'cxt' => 'application/x-director',
      'cxx' => 'text/x-c',
      'dae' => 'model/vnd.collada+xml',
      'daf' => 'application/vnd.mobius.daf',
      'dart' => 'application/vnd.dart',
      'dataless' => 'application/vnd.fdsn.seed',
      'davmount' => 'application/davmount+xml',
      'dbk' => 'application/docbook+xml',
      'dcr' => 'application/x-director',
      'dcurl' => 'text/vnd.curl.dcurl',
      'dd2' => 'application/vnd.oma.dd2+xml',
      'ddd' => 'application/vnd.fujixerox.ddd',
      'deb' => 'application/x-debian-package',
      'def' => 'text/plain',
      'deploy' => 'application/octet-stream',
      'der' => 'application/x-x509-ca-cert',
      'dfac' => 'application/vnd.dreamfactory',
      'dgc' => 'application/x-dgc-compressed',
      'dic' => 'text/x-c',
      'dir' => 'application/x-director',
      'dis' => 'application/vnd.mobius.dis',
      'dist' => 'application/octet-stream',
      'distz' => 'application/octet-stream',
      'djv' => 'image/vnd.djvu',
      'djvu' => 'image/vnd.djvu',
      'dll' => 'application/x-msdownload',
      'dmg' => 'application/x-apple-diskimage',
      'dmp' => 'application/vnd.tcpdump.pcap',
      'dms' => 'application/octet-stream',
      'dna' => 'application/vnd.dna',
      'doc' => 'application/msword',
      'docm' => 'application/vnd.ms-word.document.macroenabled.12',
      'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
      'dot' => 'application/msword',
      'dotm' => 'application/vnd.ms-word.template.macroenabled.12',
      'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
      'dp' => 'application/vnd.osgi.dp',
      'dpg' => 'application/vnd.dpgraph',
      'dra' => 'audio/vnd.dra',
      'dsc' => 'text/prs.lines.tag',
      'dssc' => 'application/dssc+der',
      'dtb' => 'application/x-dtbook+xml',
      'dtd' => 'application/xml-dtd',
      'dts' => 'audio/vnd.dts',
      'dtshd' => 'audio/vnd.dts.hd',
      'dump' => 'application/octet-stream',
      'dvb' => 'video/vnd.dvb.file',
      'dvi' => 'application/x-dvi',
      'dwf' => 'model/vnd.dwf',
      'dwg' => 'image/vnd.dwg',
      'dxf' => 'image/vnd.dxf',
      'dxp' => 'application/vnd.spotfire.dxp',
      'dxr' => 'application/x-director',
      'ecelp4800' => 'audio/vnd.nuera.ecelp4800',
      'ecelp7470' => 'audio/vnd.nuera.ecelp7470',
      'ecelp9600' => 'audio/vnd.nuera.ecelp9600',
      'ecma' => 'application/ecmascript',
      'edm' => 'application/vnd.novadigm.edm',
      'edx' => 'application/vnd.novadigm.edx',
      'efif' => 'application/vnd.picsel',
      'ei6' => 'application/vnd.pg.osasli',
      'elc' => 'application/octet-stream',
      'emf' => 'application/x-msmetafile',
      'eml' => 'message/rfc822',
      'emma' => 'application/emma+xml',
      'emz' => 'application/x-msmetafile',
      'eol' => 'audio/vnd.digital-winds',
      'eot' => 'application/vnd.ms-fontobject',
      'eps' => 'application/postscript',
      'epub' => 'application/epub+zip',
      'es3' => 'application/vnd.eszigno3+xml',
      'esa' => 'application/vnd.osgi.subsystem',
      'esf' => 'application/vnd.epson.esf',
      'et3' => 'application/vnd.eszigno3+xml',
      'etx' => 'text/x-setext',
      'eva' => 'application/x-eva',
      'evy' => 'application/x-envoy',
      'exe' => 'application/x-msdownload',
      'exi' => 'application/exi',
      'ext' => 'application/vnd.novadigm.ext',
      'ez' => 'application/andrew-inset',
      'ez2' => 'application/vnd.ezpix-album',
      'ez3' => 'application/vnd.ezpix-package',
      'f' => 'text/x-fortran',
      'f4v' => 'video/x-f4v',
      'f77' => 'text/x-fortran',
      'f90' => 'text/x-fortran',
      'fbs' => 'image/vnd.fastbidsheet',
      'fcdt' => 'application/vnd.adobe.formscentral.fcdt',
      'fcs' => 'application/vnd.isac.fcs',
      'fdf' => 'application/vnd.fdf',
      'fe_launch' => 'application/vnd.denovo.fcselayout-link',
      'fg5' => 'application/vnd.fujitsu.oasysgp',
      'fgd' => 'application/x-director',
      'fh' => 'image/x-freehand',
      'fh4' => 'image/x-freehand',
      'fh5' => 'image/x-freehand',
      'fh7' => 'image/x-freehand',
      'fhc' => 'image/x-freehand',
      'fig' => 'application/x-xfig',
      'flac' => 'audio/x-flac',
      'fli' => 'video/x-fli',
      'flo' => 'application/vnd.micrografx.flo',
      'flv' => 'video/x-flv',
      'flw' => 'application/vnd.kde.kivio',
      'flx' => 'text/vnd.fmi.flexstor',
      'fly' => 'text/vnd.fly',
      'fm' => 'application/vnd.framemaker',
      'fnc' => 'application/vnd.frogans.fnc',
      'for' => 'text/x-fortran',
      'fpx' => 'image/vnd.fpx',
      'frame' => 'application/vnd.framemaker',
      'fsc' => 'application/vnd.fsc.weblaunch',
      'fst' => 'image/vnd.fst',
      'ftc' => 'application/vnd.fluxtime.clip',
      'fti' => 'application/vnd.anser-web-funds-transfer-initiation',
      'fvt' => 'video/vnd.fvt',
      'fxp' => 'application/vnd.adobe.fxp',
      'fxpl' => 'application/vnd.adobe.fxp',
      'fzs' => 'application/vnd.fuzzysheet',
      'g2w' => 'application/vnd.geoplan',
      'g3' => 'image/g3fax',
      'g3w' => 'application/vnd.geospace',
      'gac' => 'application/vnd.groove-account',
      'gam' => 'application/x-tads',
      'gbr' => 'application/rpki-ghostbusters',
      'gca' => 'application/x-gca-compressed',
      'gdl' => 'model/vnd.gdl',
      'geo' => 'application/vnd.dynageo',
      'gex' => 'application/vnd.geometry-explorer',
      'ggb' => 'application/vnd.geogebra.file',
      'ggt' => 'application/vnd.geogebra.tool',
      'ghf' => 'application/vnd.groove-help',
      'gif' => 'image/gif',
      'gim' => 'application/vnd.groove-identity-message',
      'gml' => 'application/gml+xml',
      'gmx' => 'application/vnd.gmx',
      'gnumeric' => 'application/x-gnumeric',
      'gph' => 'application/vnd.flographit',
      'gpx' => 'application/gpx+xml',
      'gqf' => 'application/vnd.grafeq',
      'gqs' => 'application/vnd.grafeq',
      'gram' => 'application/srgs',
      'gramps' => 'application/x-gramps-xml',
      'gre' => 'application/vnd.geometry-explorer',
      'grv' => 'application/vnd.groove-injector',
      'grxml' => 'application/srgs+xml',
      'gsf' => 'application/x-font-ghostscript',
      'gtar' => 'application/x-gtar',
      'gtm' => 'application/vnd.groove-tool-message',
      'gtw' => 'model/vnd.gtw',
      'gv' => 'text/vnd.graphviz',
      'gxf' => 'application/gxf',
      'gxt' => 'application/vnd.geonext',
      'h' => 'text/x-c',
      'h261' => 'video/h261',
      'h263' => 'video/h263',
      'h264' => 'video/h264',
      'hal' => 'application/vnd.hal+xml',
      'hbci' => 'application/vnd.hbci',
      'hdf' => 'application/x-hdf',
      'hh' => 'text/x-c',
      'hlp' => 'application/winhlp',
      'hpgl' => 'application/vnd.hp-hpgl',
      'hpid' => 'application/vnd.hp-hpid',
      'hps' => 'application/vnd.hp-hps',
      'hqx' => 'application/mac-binhex40',
      'htke' => 'application/vnd.kenameaapp',
      'htm' => 'text/html',
      'html' => 'text/html',
      'hvd' => 'application/vnd.yamaha.hv-dic',
      'hvp' => 'application/vnd.yamaha.hv-voice',
      'hvs' => 'application/vnd.yamaha.hv-script',
      'i2g' => 'application/vnd.intergeo',
      'icc' => 'application/vnd.iccprofile',
      'ice' => 'x-conference/x-cooltalk',
      'icm' => 'application/vnd.iccprofile',
      'ico' => 'image/x-icon',
      'ics' => 'text/calendar',
      'ief' => 'image/ief',
      'ifb' => 'text/calendar',
      'ifm' => 'application/vnd.shana.informed.formdata',
      'iges' => 'model/iges',
      'igl' => 'application/vnd.igloader',
      'igm' => 'application/vnd.insors.igm',
      'igs' => 'model/iges',
      'igx' => 'application/vnd.micrografx.igx',
      'iif' => 'application/vnd.shana.informed.interchange',
      'imp' => 'application/vnd.accpac.simply.imp',
      'ims' => 'application/vnd.ms-ims',
      'in' => 'text/plain',
      'ink' => 'application/inkml+xml',
      'inkml' => 'application/inkml+xml',
      'install' => 'application/x-install-instructions',
      'iota' => 'application/vnd.astraea-software.iota',
      'ipfix' => 'application/ipfix',
      'ipk' => 'application/vnd.shana.informed.package',
      'irm' => 'application/vnd.ibm.rights-management',
      'irp' => 'application/vnd.irepository.package+xml',
      'iso' => 'application/x-iso9660-image',
      'itp' => 'application/vnd.shana.informed.formtemplate',
      'ivp' => 'application/vnd.immervision-ivp',
      'ivu' => 'application/vnd.immervision-ivu',
      'jad' => 'text/vnd.sun.j2me.app-descriptor',
      'jam' => 'application/vnd.jam',
      'jar' => 'application/java-archive',
      'java' => 'text/x-java-source',
      'jisp' => 'application/vnd.jisp',
      'jlt' => 'application/vnd.hp-jlyt',
      'jnlp' => 'application/x-java-jnlp-file',
      'joda' => 'application/vnd.joost.joda-archive',
      'jpe' => 'image/jpeg',
      'jpeg' => 'image/jpeg',
      'jpg' => 'image/jpeg',
      'jpgv' => 'video/jpeg',
      'js' => 'application/javascript',
      'json' => 'application/json',
      'jsonml' => 'application/jsonml+json',
      'kar' => 'audio/midi',
      'karbon' => 'application/vnd.kde.karbon',
      'kfo' => 'application/vnd.kde.kformula',
      'kia' => 'application/vnd.kidspiration',
      'kml' => 'application/vnd.google-earth.kml+xml',
      'kmz' => 'application/vnd.google-earth.kmz',
      'kne' => 'application/vnd.kinar',
      'knp' => 'application/vnd.kinar',
      'kon' => 'application/vnd.kde.kontour',
      'kpr' => 'application/vnd.kde.kpresenter',
      'kpt' => 'application/vnd.kde.kpresenter',
      'kpxx' => 'application/vnd.ds-keypoint',
      'ksp' => 'application/vnd.kde.kspread',
      'ktr' => 'application/vnd.kahootz',
      'ktx' => 'image/ktx',
      'ktz' => 'application/vnd.kahootz',
      'kwd' => 'application/vnd.kde.kword',
      'kwt' => 'application/vnd.kde.kword',
      'lasxml' => 'application/vnd.las.las+xml',
      'latex' => 'application/x-latex',
      'lbd' => 'application/vnd.llamagraphics.life-balance.desktop',
      'lbe' => 'application/vnd.llamagraphics.life-balance.exchange+xml',
      'les' => 'application/vnd.hhe.lesson-player',
      'lha' => 'application/x-lzh-compressed',
      'link66' => 'application/vnd.route66.link66+xml',
      'list' => 'text/plain',
      'list3820' => 'application/vnd.ibm.modcap',
      'listafp' => 'application/vnd.ibm.modcap',
      'lnk' => 'application/x-ms-shortcut',
      'log' => 'text/plain',
      'lostxml' => 'application/lost+xml',
      'lrf' => 'application/octet-stream',
      'lrm' => 'application/vnd.ms-lrm',
      'ltf' => 'application/vnd.frogans.ltf',
      'lvp' => 'audio/vnd.lucent.voice',
      'lwp' => 'application/vnd.lotus-wordpro',
      'lzh' => 'application/x-lzh-compressed',
      'm13' => 'application/x-msmediaview',
      'm14' => 'application/x-msmediaview',
      'm1v' => 'video/mpeg',
      'm21' => 'application/mp21',
      'm2a' => 'audio/mpeg',
      'm2v' => 'video/mpeg',
      'm3a' => 'audio/mpeg',
      'm3u' => 'audio/x-mpegurl',
      'm3u8' => 'application/vnd.apple.mpegurl',
      'm4u' => 'video/vnd.mpegurl',
      'm4v' => 'video/x-m4v',
      'ma' => 'application/mathematica',
      'mads' => 'application/mads+xml',
      'mag' => 'application/vnd.ecowin.chart',
      'maker' => 'application/vnd.framemaker',
      'man' => 'text/troff',
      'mar' => 'application/octet-stream',
      'mathml' => 'application/mathml+xml',
      'mb' => 'application/mathematica',
      'mbk' => 'application/vnd.mobius.mbk',
      'mbox' => 'application/mbox',
      'mc1' => 'application/vnd.medcalcdata',
      'mcd' => 'application/vnd.mcd',
      'mcurl' => 'text/vnd.curl.mcurl',
      'mdb' => 'application/x-msaccess',
      'mdi' => 'image/vnd.ms-modi',
      'me' => 'text/troff',
      'mesh' => 'model/mesh',
      'meta4' => 'application/metalink4+xml',
      'metalink' => 'application/metalink+xml',
      'mets' => 'application/mets+xml',
      'mfm' => 'application/vnd.mfmp',
      'mft' => 'application/rpki-manifest',
      'mgp' => 'application/vnd.osgeo.mapguide.package',
      'mgz' => 'application/vnd.proteus.magazine',
      'mid' => 'audio/midi',
      'midi' => 'audio/midi',
      'mie' => 'application/x-mie',
      'mif' => 'application/vnd.mif',
      'mime' => 'message/rfc822',
      'mk3d' => 'video/x-matroska',
      'mka' => 'audio/x-matroska',
      'mks' => 'video/x-matroska',
      'mkv' => 'video/x-matroska',
      'mlp' => 'application/vnd.dolby.mlp',
      'mmd' => 'application/vnd.chipnuts.karaoke-mmd',
      'mmf' => 'application/vnd.smaf',
      'mmr' => 'image/vnd.fujixerox.edmics-mmr',
      'mng' => 'video/x-mng',
      'mny' => 'application/x-msmoney',
      'mobi' => 'application/x-mobipocket-ebook',
      'mods' => 'application/mods+xml',
      'mov' => 'video/quicktime',
      'movie' => 'video/x-sgi-movie',
      'mp2' => 'audio/mpeg',
      'mp21' => 'application/mp21',
      'mp2a' => 'audio/mpeg',
      'mp3' => 'audio/mpeg',
      'mp4' => 'video/mp4',
      'mp4a' => 'audio/mp4',
      'mp4s' => 'application/mp4',
      'mp4v' => 'video/mp4',
      'mpc' => 'application/vnd.mophun.certificate',
      'mpe' => 'video/mpeg',
      'mpeg' => 'video/mpeg',
      'mpg' => 'video/mpeg',
      'mpg4' => 'video/mp4',
      'mpga' => 'audio/mpeg',
      'mpkg' => 'application/vnd.apple.installer+xml',
      'mpm' => 'application/vnd.blueice.multipass',
      'mpn' => 'application/vnd.mophun.application',
      'mpp' => 'application/vnd.ms-project',
      'mpt' => 'application/vnd.ms-project',
      'mpy' => 'application/vnd.ibm.minipay',
      'mqy' => 'application/vnd.mobius.mqy',
      'mrc' => 'application/marc',
      'mrcx' => 'application/marcxml+xml',
      'ms' => 'text/troff',
      'mscml' => 'application/mediaservercontrol+xml',
      'mseed' => 'application/vnd.fdsn.mseed',
      'mseq' => 'application/vnd.mseq',
      'msf' => 'application/vnd.epson.msf',
      'msh' => 'model/mesh',
      'msi' => 'application/x-msdownload',
      'msl' => 'application/vnd.mobius.msl',
      'msty' => 'application/vnd.muvee.style',
      'mts' => 'model/vnd.mts',
      'mus' => 'application/vnd.musician',
      'musicxml' => 'application/vnd.recordare.musicxml+xml',
      'mvb' => 'application/x-msmediaview',
      'mwf' => 'application/vnd.mfer',
      'mxf' => 'application/mxf',
      'mxl' => 'application/vnd.recordare.musicxml',
      'mxml' => 'application/xv+xml',
      'mxs' => 'application/vnd.triscape.mxs',
      'mxu' => 'video/vnd.mpegurl',
      'n-gage' => 'application/vnd.nokia.n-gage.symbian.install',
      'n3' => 'text/n3',
      'nb' => 'application/mathematica',
      'nbp' => 'application/vnd.wolfram.player',
      'nc' => 'application/x-netcdf',
      'ncx' => 'application/x-dtbncx+xml',
      'nfo' => 'text/x-nfo',
      'ngdat' => 'application/vnd.nokia.n-gage.data',
      'nitf' => 'application/vnd.nitf',
      'nlu' => 'application/vnd.neurolanguage.nlu',
      'nml' => 'application/vnd.enliven',
      'nnd' => 'application/vnd.noblenet-directory',
      'nns' => 'application/vnd.noblenet-sealer',
      'nnw' => 'application/vnd.noblenet-web',
      'npx' => 'image/vnd.net-fpx',
      'nsc' => 'application/x-conference',
      'nsf' => 'application/vnd.lotus-notes',
      'ntf' => 'application/vnd.nitf',
      'nzb' => 'application/x-nzb',
      'oa2' => 'application/vnd.fujitsu.oasys2',
      'oa3' => 'application/vnd.fujitsu.oasys3',
      'oas' => 'application/vnd.fujitsu.oasys',
      'obd' => 'application/x-msbinder',
      'obj' => 'application/x-tgif',
      'oda' => 'application/oda',
      'odb' => 'application/vnd.oasis.opendocument.database',
      'odc' => 'application/vnd.oasis.opendocument.chart',
      'odf' => 'application/vnd.oasis.opendocument.formula',
      'odft' => 'application/vnd.oasis.opendocument.formula-template',
      'odg' => 'application/vnd.oasis.opendocument.graphics',
      'odi' => 'application/vnd.oasis.opendocument.image',
      'odm' => 'application/vnd.oasis.opendocument.text-master',
      'odp' => 'application/vnd.oasis.opendocument.presentation',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
      'odt' => 'application/vnd.oasis.opendocument.text',
      'oga' => 'audio/ogg',
      'ogg' => 'audio/ogg',
      'ogv' => 'video/ogg',
      'ogx' => 'application/ogg',
      'omdoc' => 'application/omdoc+xml',
      'onepkg' => 'application/onenote',
      'onetmp' => 'application/onenote',
      'onetoc' => 'application/onenote',
      'onetoc2' => 'application/onenote',
      'opf' => 'application/oebps-package+xml',
      'opml' => 'text/x-opml',
      'oprc' => 'application/vnd.palm',
      'org' => 'application/vnd.lotus-organizer',
      'osf' => 'application/vnd.yamaha.openscoreformat',
      'osfpvg' => 'application/vnd.yamaha.openscoreformat.osfpvg+xml',
      'otc' => 'application/vnd.oasis.opendocument.chart-template',
      'otf' => 'application/x-font-otf',
      'otg' => 'application/vnd.oasis.opendocument.graphics-template',
      'oth' => 'application/vnd.oasis.opendocument.text-web',
      'oti' => 'application/vnd.oasis.opendocument.image-template',
      'otp' => 'application/vnd.oasis.opendocument.presentation-template',
      'ots' => 'application/vnd.oasis.opendocument.spreadsheet-template',
      'ott' => 'application/vnd.oasis.opendocument.text-template',
      'oxps' => 'application/oxps',
      'oxt' => 'application/vnd.openofficeorg.extension',
      'p' => 'text/x-pascal',
      'p10' => 'application/pkcs10',
      'p12' => 'application/x-pkcs12',
      'p7b' => 'application/x-pkcs7-certificates',
      'p7c' => 'application/pkcs7-mime',
      'p7m' => 'application/pkcs7-mime',
      'p7r' => 'application/x-pkcs7-certreqresp',
      'p7s' => 'application/pkcs7-signature',
      'p8' => 'application/pkcs8',
      'pas' => 'text/x-pascal',
      'paw' => 'application/vnd.pawaafile',
      'pbd' => 'application/vnd.powerbuilder6',
      'pbm' => 'image/x-portable-bitmap',
      'pcap' => 'application/vnd.tcpdump.pcap',
      'pcf' => 'application/x-font-pcf',
      'pcl' => 'application/vnd.hp-pcl',
      'pclxl' => 'application/vnd.hp-pclxl',
      'pct' => 'image/x-pict',
      'pcurl' => 'application/vnd.curl.pcurl',
      'pcx' => 'image/x-pcx',
      'pdb' => 'application/vnd.palm',
      'pdf' => 'application/pdf',
      'pfa' => 'application/x-font-type1',
      'pfb' => 'application/x-font-type1',
      'pfm' => 'application/x-font-type1',
      'pfr' => 'application/font-tdpfr',
      'pfx' => 'application/x-pkcs12',
      'pgm' => 'image/x-portable-graymap',
      'pgn' => 'application/x-chess-pgn',
      'pgp' => 'application/pgp-encrypted',
      'pic' => 'image/x-pict',
      'pkg' => 'application/octet-stream',
      'pki' => 'application/pkixcmp',
      'pkipath' => 'application/pkix-pkipath',
      'plb' => 'application/vnd.3gpp.pic-bw-large',
      'plc' => 'application/vnd.mobius.plc',
      'plf' => 'application/vnd.pocketlearn',
      'pls' => 'application/pls+xml',
      'pml' => 'application/vnd.ctc-posml',
      'png' => 'image/png',
      'pnm' => 'image/x-portable-anymap',
      'portpkg' => 'application/vnd.macports.portpkg',
      'pot' => 'application/vnd.ms-powerpoint',
      'potm' => 'application/vnd.ms-powerpoint.template.macroenabled.12',
      'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
      'ppam' => 'application/vnd.ms-powerpoint.addin.macroenabled.12',
      'ppd' => 'application/vnd.cups-ppd',
      'ppm' => 'image/x-portable-pixmap',
      'pps' => 'application/vnd.ms-powerpoint',
      'ppsm' => 'application/vnd.ms-powerpoint.slideshow.macroenabled.12',
      'ppsx' => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
      'ppt' => 'application/vnd.ms-powerpoint',
      'pptm' => 'application/vnd.ms-powerpoint.presentation.macroenabled.12',
      'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
      'pqa' => 'application/vnd.palm',
      'prc' => 'application/x-mobipocket-ebook',
      'pre' => 'application/vnd.lotus-freelance',
      'prf' => 'application/pics-rules',
      'ps' => 'application/postscript',
      'psb' => 'application/vnd.3gpp.pic-bw-small',
      'psd' => 'image/vnd.adobe.photoshop',
      'psf' => 'application/x-font-linux-psf',
      'pskcxml' => 'application/pskc+xml',
      'ptid' => 'application/vnd.pvi.ptid1',
      'pub' => 'application/x-mspublisher',
      'pvb' => 'application/vnd.3gpp.pic-bw-var',
      'pwn' => 'application/vnd.3m.post-it-notes',
      'pya' => 'audio/vnd.ms-playready.media.pya',
      'pyv' => 'video/vnd.ms-playready.media.pyv',
      'qam' => 'application/vnd.epson.quickanime',
      'qbo' => 'application/vnd.intu.qbo',
      'qfx' => 'application/vnd.intu.qfx',
      'qps' => 'application/vnd.publishare-delta-tree',
      'qt' => 'video/quicktime',
      'qwd' => 'application/vnd.quark.quarkxpress',
      'qwt' => 'application/vnd.quark.quarkxpress',
      'qxb' => 'application/vnd.quark.quarkxpress',
      'qxd' => 'application/vnd.quark.quarkxpress',
      'qxl' => 'application/vnd.quark.quarkxpress',
      'qxt' => 'application/vnd.quark.quarkxpress',
      'ra' => 'audio/x-pn-realaudio',
      'ram' => 'audio/x-pn-realaudio',
      'rar' => 'application/x-rar-compressed',
      'ras' => 'image/x-cmu-raster',
      'rcprofile' => 'application/vnd.ipunplugged.rcprofile',
      'rdf' => 'application/rdf+xml',
      'rdz' => 'application/vnd.data-vision.rdz',
      'rep' => 'application/vnd.businessobjects',
      'res' => 'application/x-dtbresource+xml',
      'rgb' => 'image/x-rgb',
      'rif' => 'application/reginfo+xml',
      'rip' => 'audio/vnd.rip',
      'ris' => 'application/x-research-info-systems',
      'rl' => 'application/resource-lists+xml',
      'rlc' => 'image/vnd.fujixerox.edmics-rlc',
      'rld' => 'application/resource-lists-diff+xml',
      'rm' => 'application/vnd.rn-realmedia',
      'rmi' => 'audio/midi',
      'rmp' => 'audio/x-pn-realaudio-plugin',
      'rms' => 'application/vnd.jcp.javame.midlet-rms',
      'rmvb' => 'application/vnd.rn-realmedia-vbr',
      'rnc' => 'application/relax-ng-compact-syntax',
      'roa' => 'application/rpki-roa',
      'roff' => 'text/troff',
      'rp9' => 'application/vnd.cloanto.rp9',
      'rpss' => 'application/vnd.nokia.radio-presets',
      'rpst' => 'application/vnd.nokia.radio-preset',
      'rq' => 'application/sparql-query',
      'rs' => 'application/rls-services+xml',
      'rsd' => 'application/rsd+xml',
      'rss' => 'application/rss+xml',
      'rtf' => 'application/rtf',
      'rtx' => 'text/richtext',
      's' => 'text/x-asm',
      's3m' => 'audio/s3m',
      'saf' => 'application/vnd.yamaha.smaf-audio',
      'sbml' => 'application/sbml+xml',
      'sc' => 'application/vnd.ibm.secure-container',
      'scd' => 'application/x-msschedule',
      'scm' => 'application/vnd.lotus-screencam',
      'scq' => 'application/scvp-cv-request',
      'scs' => 'application/scvp-cv-response',
      'scurl' => 'text/vnd.curl.scurl',
      'sda' => 'application/vnd.stardivision.draw',
      'sdc' => 'application/vnd.stardivision.calc',
      'sdd' => 'application/vnd.stardivision.impress',
      'sdkd' => 'application/vnd.solent.sdkm+xml',
      'sdkm' => 'application/vnd.solent.sdkm+xml',
      'sdp' => 'application/sdp',
      'sdw' => 'application/vnd.stardivision.writer',
      'see' => 'application/vnd.seemail',
      'seed' => 'application/vnd.fdsn.seed',
      'sema' => 'application/vnd.sema',
      'semd' => 'application/vnd.semd',
      'semf' => 'application/vnd.semf',
      'ser' => 'application/java-serialized-object',
      'setpay' => 'application/set-payment-initiation',
      'setreg' => 'application/set-registration-initiation',
      'sfd-hdstx' => 'application/vnd.hydrostatix.sof-data',
      'sfs' => 'application/vnd.spotfire.sfs',
      'sfv' => 'text/x-sfv',
      'sgi' => 'image/sgi',
      'sgl' => 'application/vnd.stardivision.writer-global',
      'sgm' => 'text/sgml',
      'sgml' => 'text/sgml',
      'sh' => 'application/x-sh',
      'shar' => 'application/x-shar',
      'shf' => 'application/shf+xml',
      'sid' => 'image/x-mrsid-image',
      'sig' => 'application/pgp-signature',
      'sil' => 'audio/silk',
      'silo' => 'model/mesh',
      'sis' => 'application/vnd.symbian.install',
      'sisx' => 'application/vnd.symbian.install',
      'sit' => 'application/x-stuffit',
      'sitx' => 'application/x-stuffitx',
      'skd' => 'application/vnd.koan',
      'skm' => 'application/vnd.koan',
      'skp' => 'application/vnd.koan',
      'skt' => 'application/vnd.koan',
      'sldm' => 'application/vnd.ms-powerpoint.slide.macroenabled.12',
      'sldx' => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
      'slt' => 'application/vnd.epson.salt',
      'sm' => 'application/vnd.stepmania.stepchart',
      'smf' => 'application/vnd.stardivision.math',
      'smi' => 'application/smil+xml',
      'smil' => 'application/smil+xml',
      'smv' => 'video/x-smv',
      'smzip' => 'application/vnd.stepmania.package',
      'snd' => 'audio/basic',
      'snf' => 'application/x-font-snf',
      'so' => 'application/octet-stream',
      'spc' => 'application/x-pkcs7-certificates',
      'spf' => 'application/vnd.yamaha.smaf-phrase',
      'spl' => 'application/x-futuresplash',
      'spot' => 'text/vnd.in3d.spot',
      'spp' => 'application/scvp-vp-response',
      'spq' => 'application/scvp-vp-request',
      'spx' => 'audio/ogg',
      'sql' => 'application/x-sql',
      'src' => 'application/x-wais-source',
      'srt' => 'application/x-subrip',
      'sru' => 'application/sru+xml',
      'srx' => 'application/sparql-results+xml',
      'ssdl' => 'application/ssdl+xml',
      'sse' => 'application/vnd.kodak-descriptor',
      'ssf' => 'application/vnd.epson.ssf',
      'ssml' => 'application/ssml+xml',
      'st' => 'application/vnd.sailingtracker.track',
      'stc' => 'application/vnd.sun.xml.calc.template',
      'std' => 'application/vnd.sun.xml.draw.template',
      'stf' => 'application/vnd.wt.stf',
      'sti' => 'application/vnd.sun.xml.impress.template',
      'stk' => 'application/hyperstudio',
      'stl' => 'application/vnd.ms-pki.stl',
      'str' => 'application/vnd.pg.format',
      'stw' => 'application/vnd.sun.xml.writer.template',
      'sub' => 'text/vnd.dvb.subtitle',
      'sus' => 'application/vnd.sus-calendar',
      'susp' => 'application/vnd.sus-calendar',
      'sv4cpio' => 'application/x-sv4cpio',
      'sv4crc' => 'application/x-sv4crc',
      'svc' => 'application/vnd.dvb.service',
      'svd' => 'application/vnd.svd',
      'svg' => 'image/svg+xml',
      'svgz' => 'image/svg+xml',
      'swa' => 'application/x-director',
      'swf' => 'application/x-shockwave-flash',
      'swi' => 'application/vnd.aristanetworks.swi',
      'sxc' => 'application/vnd.sun.xml.calc',
      'sxd' => 'application/vnd.sun.xml.draw',
      'sxg' => 'application/vnd.sun.xml.writer.global',
      'sxi' => 'application/vnd.sun.xml.impress',
      'sxm' => 'application/vnd.sun.xml.math',
      'sxw' => 'application/vnd.sun.xml.writer',
      't' => 'text/troff',
      't3' => 'application/x-t3vm-image',
      'taglet' => 'application/vnd.mynfc',
      'tao' => 'application/vnd.tao.intent-module-archive',
      'tar' => 'application/x-tar',
      'tcap' => 'application/vnd.3gpp2.tcap',
      'tcl' => 'application/x-tcl',
      'teacher' => 'application/vnd.smart.teacher',
      'tei' => 'application/tei+xml',
      'teicorpus' => 'application/tei+xml',
      'tex' => 'application/x-tex',
      'texi' => 'application/x-texinfo',
      'texinfo' => 'application/x-texinfo',
      'text' => 'text/plain',
      'tfi' => 'application/thraud+xml',
      'tfm' => 'application/x-tex-tfm',
      'tga' => 'image/x-tga',
      'thmx' => 'application/vnd.ms-officetheme',
      'tif' => 'image/tiff',
      'tiff' => 'image/tiff',
      'tmo' => 'application/vnd.tmobile-livetv',
      'torrent' => 'application/x-bittorrent',
      'tpl' => 'application/vnd.groove-tool-template',
      'tpt' => 'application/vnd.trid.tpt',
      'tr' => 'text/troff',
      'tra' => 'application/vnd.trueapp',
      'trm' => 'application/x-msterminal',
      'tsd' => 'application/timestamped-data',
      'tsv' => 'text/tab-separated-values',
      'ttc' => 'application/x-font-ttf',
      'ttf' => 'application/x-font-ttf',
      'ttl' => 'text/turtle',
      'twd' => 'application/vnd.simtech-mindmapper',
      'twds' => 'application/vnd.simtech-mindmapper',
      'txd' => 'application/vnd.genomatix.tuxedo',
      'txf' => 'application/vnd.mobius.txf',
      'txt' => 'text/plain',
      'u32' => 'application/x-authorware-bin',
      'udeb' => 'application/x-debian-package',
      'ufd' => 'application/vnd.ufdl',
      'ufdl' => 'application/vnd.ufdl',
      'ulx' => 'application/x-glulx',
      'umj' => 'application/vnd.umajin',
      'unityweb' => 'application/vnd.unity',
      'uoml' => 'application/vnd.uoml+xml',
      'uri' => 'text/uri-list',
      'uris' => 'text/uri-list',
      'urls' => 'text/uri-list',
      'ustar' => 'application/x-ustar',
      'utz' => 'application/vnd.uiq.theme',
      'uu' => 'text/x-uuencode',
      'uva' => 'audio/vnd.dece.audio',
      'uvd' => 'application/vnd.dece.data',
      'uvf' => 'application/vnd.dece.data',
      'uvg' => 'image/vnd.dece.graphic',
      'uvh' => 'video/vnd.dece.hd',
      'uvi' => 'image/vnd.dece.graphic',
      'uvm' => 'video/vnd.dece.mobile',
      'uvp' => 'video/vnd.dece.pd',
      'uvs' => 'video/vnd.dece.sd',
      'uvt' => 'application/vnd.dece.ttml+xml',
      'uvu' => 'video/vnd.uvvu.mp4',
      'uvv' => 'video/vnd.dece.video',
      'uvva' => 'audio/vnd.dece.audio',
      'uvvd' => 'application/vnd.dece.data',
      'uvvf' => 'application/vnd.dece.data',
      'uvvg' => 'image/vnd.dece.graphic',
      'uvvh' => 'video/vnd.dece.hd',
      'uvvi' => 'image/vnd.dece.graphic',
      'uvvm' => 'video/vnd.dece.mobile',
      'uvvp' => 'video/vnd.dece.pd',
      'uvvs' => 'video/vnd.dece.sd',
      'uvvt' => 'application/vnd.dece.ttml+xml',
      'uvvu' => 'video/vnd.uvvu.mp4',
      'uvvv' => 'video/vnd.dece.video',
      'uvvx' => 'application/vnd.dece.unspecified',
      'uvvz' => 'application/vnd.dece.zip',
      'uvx' => 'application/vnd.dece.unspecified',
      'uvz' => 'application/vnd.dece.zip',
      'vcard' => 'text/vcard',
      'vcd' => 'application/x-cdlink',
      'vcf' => 'text/x-vcard',
      'vcg' => 'application/vnd.groove-vcard',
      'vcs' => 'text/x-vcalendar',
      'vcx' => 'application/vnd.vcx',
      'vis' => 'application/vnd.visionary',
      'viv' => 'video/vnd.vivo',
      'vob' => 'video/x-ms-vob',
      'vor' => 'application/vnd.stardivision.writer',
      'vox' => 'application/x-authorware-bin',
      'vrml' => 'model/vrml',
      'vsd' => 'application/vnd.visio',
      'vsf' => 'application/vnd.vsf',
      'vss' => 'application/vnd.visio',
      'vst' => 'application/vnd.visio',
      'vsw' => 'application/vnd.visio',
      'vtu' => 'model/vnd.vtu',
      'vxml' => 'application/voicexml+xml',
      'w3d' => 'application/x-director',
      'wad' => 'application/x-doom',
      'wav' => 'audio/x-wav',
      'wax' => 'audio/x-ms-wax',
      'wbmp' => 'image/vnd.wap.wbmp',
      'wbs' => 'application/vnd.criticaltools.wbs+xml',
      'wbxml' => 'application/vnd.wap.wbxml',
      'wcm' => 'application/vnd.ms-works',
      'wdb' => 'application/vnd.ms-works',
      'wdp' => 'image/vnd.ms-photo',
      'weba' => 'audio/webm',
      'webm' => 'video/webm',
      'webp' => 'image/webp',
      'wg' => 'application/vnd.pmi.widget',
      'wgt' => 'application/widget',
      'wks' => 'application/vnd.ms-works',
      'wm' => 'video/x-ms-wm',
      'wma' => 'audio/x-ms-wma',
      'wmd' => 'application/x-ms-wmd',
      'wmf' => 'application/x-msmetafile',
      'wml' => 'text/vnd.wap.wml',
      'wmlc' => 'application/vnd.wap.wmlc',
      'wmls' => 'text/vnd.wap.wmlscript',
      'wmlsc' => 'application/vnd.wap.wmlscriptc',
      'wmv' => 'video/x-ms-wmv',
      'wmx' => 'video/x-ms-wmx',
      'wmz' => 'application/x-msmetafile',
      'woff' => 'application/x-font-woff',
      'wpd' => 'application/vnd.wordperfect',
      'wpl' => 'application/vnd.ms-wpl',
      'wps' => 'application/vnd.ms-works',
      'wqd' => 'application/vnd.wqd',
      'wri' => 'application/x-mswrite',
      'wrl' => 'model/vrml',
      'wsdl' => 'application/wsdl+xml',
      'wspolicy' => 'application/wspolicy+xml',
      'wtb' => 'application/vnd.webturbo',
      'wvx' => 'video/x-ms-wvx',
      'x32' => 'application/x-authorware-bin',
      'x3d' => 'model/x3d+xml',
      'x3db' => 'model/x3d+binary',
      'x3dbz' => 'model/x3d+binary',
      'x3dv' => 'model/x3d+vrml',
      'x3dvz' => 'model/x3d+vrml',
      'x3dz' => 'model/x3d+xml',
      'xaml' => 'application/xaml+xml',
      'xap' => 'application/x-silverlight-app',
      'xar' => 'application/vnd.xara',
      'xbap' => 'application/x-ms-xbap',
      'xbd' => 'application/vnd.fujixerox.docuworks.binder',
      'xbm' => 'image/x-xbitmap',
      'xdf' => 'application/xcap-diff+xml',
      'xdm' => 'application/vnd.syncml.dm+xml',
      'xdp' => 'application/vnd.adobe.xdp+xml',
      'xdssc' => 'application/dssc+xml',
      'xdw' => 'application/vnd.fujixerox.docuworks',
      'xenc' => 'application/xenc+xml',
      'xer' => 'application/patch-ops-error+xml',
      'xfdf' => 'application/vnd.adobe.xfdf',
      'xfdl' => 'application/vnd.xfdl',
      'xht' => 'application/xhtml+xml',
      'xhtml' => 'application/xhtml+xml',
      'xhvml' => 'application/xv+xml',
      'xif' => 'image/vnd.xiff',
      'xla' => 'application/vnd.ms-excel',
      'xlam' => 'application/vnd.ms-excel.addin.macroenabled.12',
      'xlc' => 'application/vnd.ms-excel',
      'xlf' => 'application/x-xliff+xml',
      'xlm' => 'application/vnd.ms-excel',
      'xls' => 'application/vnd.ms-excel',
      'xlsb' => 'application/vnd.ms-excel.sheet.binary.macroenabled.12',
      'xlsm' => 'application/vnd.ms-excel.sheet.macroenabled.12',
      'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'xlt' => 'application/vnd.ms-excel',
      'xltm' => 'application/vnd.ms-excel.template.macroenabled.12',
      'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
      'xlw' => 'application/vnd.ms-excel',
      'xm' => 'audio/xm',
      'xml' => 'application/xml',
      'xo' => 'application/vnd.olpc-sugar',
      'xop' => 'application/xop+xml',
      'xpi' => 'application/x-xpinstall',
      'xpl' => 'application/xproc+xml',
      'xpm' => 'image/x-xpixmap',
      'xpr' => 'application/vnd.is-xpr',
      'xps' => 'application/vnd.ms-xpsdocument',
      'xpw' => 'application/vnd.intercon.formnet',
      'xpx' => 'application/vnd.intercon.formnet',
      'xsl' => 'application/xml',
      'xslt' => 'application/xslt+xml',
      'xsm' => 'application/vnd.syncml+xml',
      'xspf' => 'application/xspf+xml',
      'xul' => 'application/vnd.mozilla.xul+xml',
      'xvm' => 'application/xv+xml',
      'xvml' => 'application/xv+xml',
      'xwd' => 'image/x-xwindowdump',
      'xyz' => 'chemical/x-xyz',
      'xz' => 'application/x-xz',
      'yang' => 'application/yang',
      'yin' => 'application/yin+xml',
      'z1' => 'application/x-zmachine',
      'z2' => 'application/x-zmachine',
      'z3' => 'application/x-zmachine',
      'z4' => 'application/x-zmachine',
      'z5' => 'application/x-zmachine',
      'z6' => 'application/x-zmachine',
      'z7' => 'application/x-zmachine',
      'z8' => 'application/x-zmachine',
      'zaz' => 'application/vnd.zzazz.deck+xml',
      'zip' => 'application/zip',
      'zir' => 'application/vnd.zul',
      'zirz' => 'application/vnd.zul',
      'zmm' => 'application/vnd.handheld-entertainment+xml',

      /* JPEG 2000: From RFC 3745: http://www.faqs.org/rfcs/rfc3745.html */
      'jp2' => 'image/jp2',
      'jpg2' => 'image/jp2',
      'jpf' => 'image/jpx',
      'jpx' => 'image/jpx',
      'mj2' => 'video/mj2',
      'mjp2' => 'video/mj2',
      'jpm' => 'image/jpm',
      'jpgm' => 'image/jpgm',

      /* Other */
      'pcd' => 'image/x-photo-cd',
      'jpgcmyk' => 'image/jpeg-cmyk',
      'tifcmyk' => 'image/tiff-cmyk',
      'tgz' => 'application/x-compressed');

  // Temporary path for local copy of an external object (see importFromUri
  // method)
  protected
    $localPath;

  // List of web compatible image formats (supported in most major browsers)
  protected static
    $webCompatibleImageFormats = array(
      'image/jpeg',
      'image/jpg',
      'image/jpe',
      'image/gif',
      'image/png'),

    // Qubit generic icon list
    $qubitGenericThumbs = array(
      'application/vnd.ms-excel'      => 'excel.png',
      'application/msword'            => 'word.png',
      'application/vnd.ms-powerpoint' => 'powerpoint.png',
      'audio/*'                       => 'audio.png',
      'video/*'                       => 'video.png',
      'application/pdf'               => 'pdf.png',
      // text & rich text
      'text/plain'                    => 'text.png',
      'application/rtf'               => 'text.png',
      'text/richtext'                 => 'text.png',
      // archives: zip, rar, tar
      'application/x-tar'             => 'archive.png',
      'application/zip'               => 'archive.png',
      'application/x-rar-compressed'  => 'archive.png',
      // images
      'image/jpeg'                    => 'image.png',
      'image/jpg'                     => 'image.png',
      'image/jpe'                     => 'image.png',
      'image/gif'                     => 'image.png',
      'image/png'                     => 'image.png'),

    $qubitGenericReference = array(
      '*/*' => 'blank.png');

  public function __toString()
  {
    return (string) $this->name;
  }

  public function __get($name)
  {
    $args = func_get_args();

    $options = array();
    if (1 < count($args))
    {
      $options = $args[1];
    }

    switch ($name)
    {
      case 'thumbnail':

        if (!isset($this->values['thumbnail']))
        {
          $criteria = new Criteria;
          $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('id'));
          $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::THUMBNAIL_ID);

          $this->values['thumbnail'] = QubitDigitalObject::get($criteria)->offsetGet(0);
        }

        return $this->values['thumbnail'];

      case 'reference':

        if (!isset($this->values['reference']))
        {
          $criteria = new Criteria;
          $criteria->add(QubitDigitalObject::PARENT_ID, $this->__get('id'));
          $criteria->add(QubitDigitalObject::USAGE_ID, QubitTerm::REFERENCE_ID);

          $this->values['reference'] = QubitDigitalObject::get($criteria)->offsetGet(0);
        }

        return $this->values['reference'];
    }

    return call_user_func_array(array($this, 'BaseDigitalObject::__get'), $args);
  }

  protected function insert($connection = null)
  {
    if (!isset($this->slug))
    {
      $this->slug = QubitSlug::slugify($this->__get('name', array('sourceCulture' => true)));
    }

    return parent::insert($connection);
  }

  public function save($connection = null)
  {
    // TODO: $cleanInformationObject = $this->informationObject->clean;
    $cleanInformationObjectId = $this->__get('informationObjectId', array('clean' => true));

    // Write assets to storage device
    if (0 < count($this->assets))
    {
      foreach ($this->assets as $asset)
      {
        if (null == $this->getChecksum() || $asset->getChecksum() != $this->getChecksum())
        {
          $this->writeToFileSystem($asset);
        }

        // TODO: allow setting multiple assets for different usage types
        // (e.g. a master, thumbnail and reference image)
        break;
      }
    }

    parent::save($connection);

    // Create child objects (derivatives)
    if (0 < count($this->assets) && $this->createDerivatives)
    {
      if (sfConfig::get('app_explode_multipage_files') && $this->getPageCount() > 1)
      {
        // If DO is a compound object, then create child objects and set to
        // display as compound object (with pager)
        $this->createCompoundChildren($connection);

        // Set parent digital object to be displayed as compound
        $this->setDisplayAsCompoundObject(1);

        // We don't need reference image because a compound will be displayed instead of it
        // But thumbnails are necessary for image flow
        $this->createThumbnail($connection);

        // Extract text and attach to parent digital object
        $this->extractText($connection);
      }
      else
      {
        // If DO is a single object, create various representations based on
        // intended usage
        $this->createRepresentations($this->usageId, $connection);
      }
    }

    // Add watermark to reference image
    if (QubitTerm::REFERENCE_ID == $this->usageId
        && $this->isImage()
        && is_readable($waterMarkPathName = sfConfig::get('sf_web_dir').'/watermark.png')
        && is_file($waterMarkPathName))
    {
      $filePathName = $this->getAbsolutePath();
      $command = 'composite -dissolve 15 -tile '.$waterMarkPathName.' '.escapeshellarg($filePathName).' '.escapeshellarg($filePathName);
      exec($command);
    }

    // Update search index for related info object
    if ($this->indexOnSave)
    {
      if ($this->informationObjectId != $cleanInformationObjectId && null !== QubitInformationObject::getById($cleanInformationObjectId))
      {
        QubitSearch::getInstance()->update(QubitInformationObject::getById($cleanInformationObjectId));
      }

      if (isset($this->informationObject))
      {
        QubitSearch::getInstance()->update($this->informationObject);
      }
    }

    return $this;
  }

  /**
   * Override base delete method to unlink related digital assets (thumbnail
   * and file)
   *
   * @param  sfConnection  A database connection
   */
  public function delete($connection = null)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, $this->id);

    $children = QubitDigitalObject::get($criteria);

    // Delete children
    foreach ($children as $child)
    {
      foreach (QubitRelation::getBySubjectOrObjectId($this->id) as $item)
      {
        $item->delete();
      }

      $child->delete();
    }

    if ($this->usageId !== QubitTerm::OFFLINE_ID)
    {
      // Delete digital asset
      if (file_exists($this->getAbsolutePath()))
      {
        unlink($this->getAbsolutePath());
      }

      // Prune asset directory, if empty
      self::pruneEmptyDirs(sfConfig::get('sf_web_dir').$this->path);
    }

    foreach (QubitRelation::getBySubjectOrObjectId($this->id) as $item)
    {
      $item->delete();
    }

    // Update search index before deleting self
    if (!empty($this->informationObjectId))
    {
      $this->deleteFromAssociatedInformationObject();
      QubitSearch::getInstance()->update($this->getInformationObject());
    }

    // Delete self
    parent::delete($connection);
  }

  /**
   * If we have an associated information object, ensure this digital object is cleared
   * from its digitalObjects property.
   */
  private function deleteFromAssociatedInformationObject()
  {
    if ((null !== $io = $this->getInformationObject()) && isset($io->refFkValues['digitalObjects']))
    {
      unset($io->refFkValues['digitalObjects']);
    }
  }

  /**
   * The nested set is disabled for QubitDigitalObject
   */
  protected function updateNestedSet($connection = null)
  {
  }

  /**
   * The nested set is disabled for QubitDigitalObject
   */
  protected function deleteFromNestedSet($connection = null)
  {
  }

  /**
   * Get descendants based on parent id instead via lft/rgt,
   * since the nested set is disabled.
   */
  public function addDescendantsCriteria(Criteria $criteria)
  {
    return $criteria->add(QubitDigitalObject::PARENT_ID, $this->id);
  }

  /**
   * Create a digital object representation of an asset
   *
   * @param mixed parent object (digital object or information object)
   * @param QubitAsset asset to represent
   * @param array options array of optional paramaters
   * @return QubitDigitalObject
   */
  public function writeToFileSystem($asset, $options = array())
  {
    // Fail if filename is empty
    if (0 == strlen($asset->getName()))
    {
      throw new sfException('Not a valid filename');
    }

    // Fail if "thumbnail" is not an image
    if (QubitTerm::THUMBNAIL_ID == $this->usageId && !QubitDigitalObject::isImageFile($asset->getName()))
    {
      throw new sfException('Thumbnail must be valid image type (jpeg, png, gif)');
    }

    // Get clean file name (no bad chars)
    $cleanFileName = self::sanitizeFilename($asset->getName());

    // If file has not extension, try to get it from asset mime type
    if (0 == strlen(pathinfo($cleanFileName, PATHINFO_EXTENSION)) && null !== ($assetMimeType = $asset->mimeType) && 0 < strlen(($newFileExtension = array_search($assetMimeType, self::$qubitMimeTypes))))
    {
      $cleanFileName .= '.'.$newFileExtension;
    }

    // Upload paths for this information object / digital object
    $infoObjectPath = $this->getAssetPath($asset->getChecksum());
    $filePath       = sfConfig::get('sf_web_dir').$infoObjectPath.'/';
    $relativePath   = $infoObjectPath.'/';
    $filePathName   = $filePath.$cleanFileName;

    // make the target directory if necessary
    // NB: this will always return false if the path exists
    if (!file_exists($filePath))
    {
      mkdir($filePath, 0755, true);
    }

    // Write file
    // If the asset contents are not included but referred, move or copy
    if (null !== $assetPath = $asset->getPath())
    {
      if (false === @copy($assetPath, $filePathName))
      {
        throw new sfException('File write to '.$filePathName.' failed. See setting directory and file permissions documentation.');
      }
    }
    // If the asset contents are included (HTTP upload)
    else if (false === file_put_contents($filePathName, $asset->getContents()))
    {
      throw new sfException('File write to '.$filePathName.' failed. See setting directory and file permissions documentation.');
    }

    // Test asset checksum against generated checksum from file
    $this->generateChecksumFromFile($filePathName);
    if ($this->getChecksum() != $asset->getChecksum())
    {
      unlink($filePathName);
      rmdir($infoObjectPath);

      throw new sfException('Checksum values did not validate: '. $filePathName);
    }

    // set file permissions
    if (!chmod($filePathName, 0644))
    {
      throw new sfException('Failed to set permissions on '.$filePathName);
    }

    // Iterate through new directories and set permissions (mkdir() won't do this properly)
    $pathToDir = sfConfig::get('sf_web_dir');
    foreach (explode('/', $infoObjectPath) as $dir)
    {
      $pathToDir .= '/'.$dir;
      @chmod($pathToDir, 0755);
    }

    // Save digital object in database
    $this->setName($cleanFileName);
    $this->setPath($relativePath);
    $this->setByteSize(filesize($filePathName));
    $this->setMimeAndMediaType();

    return $this;
  }

  /**
   * Download external file via sfWebBrowser and return its temporary location
   *
   * @param string URI
   * @param array options optional arguments
   *
   * @return string contents
   */
  private function downloadExternalObject($uri, $options = array())
  {
    // Initialize web browser
    $timeout = sfConfig::get("app_download_timeout");
    $browser = new sfWebBrowser(array(), 'sfCurlAdapter', array('Timeout' => $timeout));

    // Set retries from optional argument
    $retries = (isset($options['downloadRetries']) && 0 < $options['downloadRetries']) ? $options['downloadRetries'] : 0;

    // Attempt to download the digital object, with possible retries
    for ($i=0; $i <= $retries; $i++)
    {
      try
      {
        $browser->get($uri);
      }
      catch (Exception $e)
      {
        // If request times out
        if ($e->getCode() === CURLE_OPERATION_TIMEDOUT)
        {
          // Try again, up to $retries
          continue;
        }

        throw $e;
      }

      // If response code is an error (4xx or 5xx)
      if ($browser->responseIsError())
      {
        // Try again, up to $retries
        continue;
      }

      if (false !== $contents = $browser->getResponseText())
      {
        return $contents;
      }
    }

    // Throw exception on failure
    throw new sfException(sprintf('Error downloading "%s" (attempts: %s).', $uri, $i));
  }

  /**
   * Get filename from URI path
   *
   * @param string URI
   * @return mixed null if error, string otherwise
   */
  private function getFilenameFromUri($uri)
  {
    // Parse URL into components and get file/base name
    $uriComponents = parse_url($uri);
    $filename = basename($uriComponents['path']);

    if (1 > strlen($filename))
    {
      throw new sfException(sprintf('Couldn\'t parse filename from uri %s', $uri));
    }

    return $filename;
  }

  /**
   * Populate a digital object from a resource pointed to by a URI
   * This is for, eg. importing encoded digital objects from XML
   *
   * @param string $uri remote digital object URI
   * @param array $options Optional arguments
   *
   * @return QubitDigitalObject this object
   */
  public function importFromURI($uri, $options = array())
  {
    $filename = $this->getFilenameFromUri($uri);

    // Set general properties that don't require downloading the asset
    $this->usageId = QubitTerm::EXTERNAL_URI_ID;
    $this->name = $filename;
    $this->path = $uri;
    $this->setMimeAndMediaType();

    // If not creating derivatives right now, don't download the resource
    if (!$this->createDerivatives)
    {
      return $self;
    }

    // Download the remote resource bitstream
    $contents = $this->downloadExternalObject($uri, $options);

    // Save downloaded bitstream to a temp file
    if (false === $this->localPath = Qubit::saveTemporaryFile($filename, $contents))
    {
      throw new sfException(sprintf('Error writing downloaded file to "%s".', $this->localPath));
    }

    // Attach downloaded file to digital object
    $asset = new QubitAsset($this->localPath);
    $this->assets[] = $asset;

    // Set properties derived from file contents
    $this->checksum = $asset->getChecksum();
    $this->checksumType = $asset->getChecksumAlgorithm();
    $this->byteSize = strlen($contents);

    return $self;
  }

  /**
   * Populate a digital object from a base64-encoded character stream.
   * This is for, eg. importing encoded digital objects from XML
   *
   * @param string  $encodedString  base64-encoded string
   * @return boolean  success or failure
   */
  public function importFromBase64($encodedString, $filename, $options = array())
  {
    $fileContents = base64_decode($encodedString);

    if (0 < strlen($fileContents))
    {
      $asset = new QubitAsset($filename, $fileContents);
    }
    else
    {
      throw new sfException('Could not read the file contents');
    }

    $this->assets[] = $asset;
  }

  /**
   * Remove undesirable characters from a filename
   *
   * @param string $filename incoming file name
   * @return string sanitized filename
   */
  protected static function sanitizeFilename($filename)
  {
    return preg_replace('/[^a-z0-9_\.-]/i', '_', $filename);
  }

  /**
   * Get count of digital objects by media-type
   */
  public static function getCount($mediaTypeId)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, null, Criteria::ISNULL);

    $criteria->add(QubitDigitalObject::MEDIA_TYPE_ID, $mediaTypeId);
    $criteria->addJoin(QubitDigitalObject::INFORMATION_OBJECT_ID, QubitInformationObject::ID);
    $criteria = QubitAcl::addFilterDraftsCriteria($criteria);

    return BasePeer::doCount($criteria)->fetchColumn(0);
  }

  /**
   * Get path to asset, relative to sf_web_dir
   *
   * @return string  path to asset
   */
  public function getFullPath()
  {
    if (QubitTerm::EXTERNAL_URI_ID != $this->usageId)
    {
      return $this->getPath().$this->getName();
    }
    else
    {
      // For remote resources 'path' contains the complete URL and concatenating
      // 'name' is not desirable
      return $this->getPath();
    }
  }

  /**
   * Get public URL to asset; if asset path is a public URL,
   * returns that path instead of constructing on the current server.
   *
   * @return string URL to asset
   */
  public function getPublicPath()
  {
    if ($this->usageId == QubitTerm::EXTERNAL_URI_ID)
    {
      return $this->getPath();
    }
    else
    {
      return public_path($this->getFullPath(), true);
    }
  }


  /**
   * Get absolute path to asset
   *
   * @return string absolute path to asset
   */
  public function getAbsolutePath()
  {
    return sfConfig::get('sf_web_dir').$this->path.$this->name;
  }

  /**
   * Test that image will display in major web browsers
   *
   * @return boolean
   */
  public function isWebCompatibleImageFormat()
  {
    return in_array($this->mimeType, self::$webCompatibleImageFormats);
  }

  /**
   * Set Mime-type and Filetype all at once
   *
   */
  public function setMimeAndMediaType($mimeType = null)
  {
    if (null !== $mimeType)
    {
      $this->setMimeType($mimeType);
    }
    else
    {
      $this->setMimeType(QubitDigitalObject::deriveMimeType($this->getName()));
    }

    $this->setDefaultMediaType();
  }

  /**
   * Set default mediaTypeId based on digital asset's mime-type.  Media types
   * id's are defined in the QubitTerms db
   *
   * @return mixed  integer if mediatype mapped, null if no valid mapping
   */
  public function setDefaultMediaType()
  {
    // Make sure we have a valid mime-type (with a forward-slash).
    if (!strlen($this->mimeType) || !strpos($this->mimeType, '/'))
    {
      return null;
    }

    $mimePieces = explode('/', $this->mimeType);

    switch($mimePieces[0])
    {
      case 'audio':
        $mediaTypeId = QubitTerm::AUDIO_ID;
        break;
      case 'image':
        $mediaTypeId = QubitTerm::IMAGE_ID;
        break;
      case 'text':
        $mediaTypeId = QubitTerm::TEXT_ID;
        break;
      case 'video':
        $mediaTypeId = QubitTerm::VIDEO_ID;
        break;
      case 'application':
        switch ($mimePieces[1])
        {
          case 'pdf':
            $mediaTypeId = QubitTerm::TEXT_ID;
            break;
          default:
            $mediaTypeId = QubitTerm::OTHER_ID;
        }
        break;
      default:
        $mediaTypeId = QubitTerm::OTHER_ID;
    }

    $this->mediaTypeId = $mediaTypeId;
  }

  /**
   * Get this object's top ancestor, or self if it is the top of the branch
   *
   * return QubitInformationObject  Closest InformationObject ancestor
   */
  public function getTopAncestorOrSelf()
  {
    // Get the ancestor at array index "0"
    return $this->getAncestors()->andSelf()->offsetGet(0);
  }

  /**
   * Find *first* child of current digital object that matches $usageid.
   *
   * @param integer  Constant value from QubitTerm (THUMBNAIL_ID, REFERENCE_ID)
   */
  public function getChildByUsageId($usageId)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PARENT_ID, $this->id);
    $criteria->add(QubitDigitalObject::USAGE_ID, $usageId);

    $result = QubitDigitalObject::getOne($criteria);

    return $result;
  }

  /**
   * Find QubitDigitalObject by PATH and FILE
   *
   * @param string  a string expected to match on the PATH column
   * @param string  a string expected to match on the FILE column
   */
  public static function getByPathFile($path, $name)
  {
    $criteria = new Criteria;
    $criteria->add(QubitDigitalObject::PATH, $path);
    $criteria->add(QubitDigitalObject::NAME, $name);

    $result = QubitDigitalObject::getOne($criteria);

    return $result;
  }

  /**
   * Get a representation for the given $usageId.  Currently only searches
   * direct children of current digital object.
   *
   * @param integer $usageId
   * @return mixed DigitalObject on success
   *
   * @todo look for matching usage id up and down object tree?
   */
  public function getRepresentationByUsage($usageId)
  {
    if ($usageId == $this->getUsageId())
    {
      return $this;
    }
    else
    {
      return $this->getChildByUsageId($usageId);
    }
  }

  /**
   * Return a compound representation for this digital object - generating the
   * rep if necessary
   *
   * @return QubitDigitalObject compound image representation
   */
  public function getCompoundRepresentation()
  {
    if (null === $compoundRep = $this->getRepresentationByUsage(QubitTerm::COMPOUND_ID))
    {
      // Generate a compound representation if one doesn't exist already
      $compoundRep = self::createImageDerivative(QubitTerm::COMPOUND_ID);
    }

    return $compoundRep;
  }

  /**
   * Determine if this digital object is an image, based on mimetype
   *
   * @return boolean
   */
  public function isImage()
  {
    return self::isImageFile($this->getName());
  }

  /**
   * Return true if this is a compound digital object
   *
   * @return boolean
   */
  public function isCompoundObject()
  {
    $isCompoundObjectProp = QubitProperty::getOneByObjectIdAndName($this->id, 'is_compound_object');

    return (null !== $isCompoundObjectProp && '1' == $isCompoundObjectProp->getValue(array('sourceCulture' => true)));
  }

  /**
   * Derive file path for a digital object asset
   *
   * All digital object paths are keyed by information object id that is the
   * nearest ancestor of the current digital object. Because we may not know
   * the id of the current digital object yet (i.e. it hasn't been saved to the
   * database yet), we pass the parent digital object or information object.
   *
   * The directory structure is based on the checksum of the master digital object.
   *
   * @return string  asset file path
   */
  public function getAssetPath($checksum)
  {
    if (isset($this->informationObject))
    {
      $infoObject = $this->informationObject;
    }
    else if (isset($this->parent))
    {
      $infoObject = $this->parent->informationObject;
    }

    if (!isset($infoObject))
    {
      throw new sfException('Couldn\'t find related information object for digital object');
    }

    if ($this->usageId == QubitTerm::MASTER_ID || $this->parent->usageId == QubitTerm::EXTERNAL_URI_ID)
    {
      $id = (string) $infoObject->id;

      // determine path for current repository
      $repoDir = '';
      if (null !== ($repo = $infoObject->getRepository(array('inherit' => true))))
      {
        $repoDir = $repo->slug;
      }
      else
      {
        $repoDir = 'null';
      }

      return '/'.QubitSetting::getByName('upload_dir')->__toString().'/r/'.$repoDir.'/'.$checksum[0].'/'.$checksum[1].'/'.$checksum[2].'/'.$checksum;
    }
    else
    {
      if (!isset($this->parent))
      {
        throw new sfException('Got an orphaned derivative.');
      }

      return rtrim($this->parent->getPath(), '/');
    }
  }

  /**
   * Get path to the appropriate generic icon for $mimeType
   *
   * @param string $mimeType
   * @return string
   */
  public static function getGenericIconPath($mimeType, $usageType)
  {
    $genericIconDir  = self::GENERIC_ICON_DIR;
    $matchedMimeType = null;

    switch ($usageType)
    {
      case QubitTerm::REFERENCE_ID:
      case QubitTerm::MASTER_ID:
        $genericIconList = QubitDigitalObject::$qubitGenericReference;
        break;
      default:
        $genericIconList = QubitDigitalObject::$qubitGenericThumbs;
    }

    if ('unknown' == $mimeType)
    {
      // Use "blank" icon for unknown file types
      return $genericIconPath = $genericIconDir.'/blank.png';
    }

    // Check the list for a generic icon matching this mime-type
    $mimeParts = explode('/', $mimeType);
    foreach ($genericIconList as $mimePattern => $icon)
    {
      $pattern = explode('/', $mimePattern);

      if (($mimeParts[0] == $pattern[0] || '*' == $pattern[0]) && ($mimeParts[1] == $pattern[1] || '*' == $pattern[1]))
      {
        $matchedMimeType = $mimePattern;
        break;
      }
    }

    if (null !== $matchedMimeType)
    {
      $genericIconPath = $genericIconDir.'/'.$genericIconList[$matchedMimeType];
    }
    else
    {
      // Use "blank" icon for unknown file types
      $genericIconPath = $genericIconDir.'/blank.png';
    }

    return $genericIconPath;
  }

  /**
   * Get path to the appropriate generic icon for specified
   * media type id. This method is similar to getGenericIconPath().
   *
   * @param int $mimeTypeId
   * @return string
   */
  public static function getGenericIconPathByMediaTypeId($mimeTypeId)
  {
    $mediaTypeFilename = 'blank.png';
    switch ($mimeTypeId)
    {
      case QubitTerm::AUDIO_ID:
        $mediaTypeFilename = 'audio.png';
        break;

      case QubitTerm::IMAGE_ID:
        $mediaTypeFilename = 'image.png';
        break;

      case QubitTerm::TEXT_ID:
        $mediaTypeFilename = 'text.png';
        break;

      case QubitTerm::VIDEO_ID:
        $mediaTypeFilename = 'video.png';
        break;
    }

    return self::GENERIC_ICON_DIR . '/' . $mediaTypeFilename;
  }

  /**
   * Get a generic representation for the current digital object.
   *
   * @param string $mimeType
   * @return QubitDigitalObject
   */
  public static function getGenericRepresentation($mimeType, $usageType)
  {
    $representation = new QubitDigitalObject;
    $genericIconPath = QubitDigitalObject::getGenericIconPath($mimeType, $usageType);

    $representation->setPath(dirname($genericIconPath).'/');
    $representation->setName(basename($genericIconPath));

    return $representation;
  }

  /**
   * Derive a file's mime-type from it's filename extension.  The extension may
   * lie, but this should be "good enough" for the majority of cases.
   *
   * @param string   name of the file
   * @return string  mime-type of file (or "unknown" if no match)
   */
  public static function deriveMimeType($filename)
  {
    $mimeType     = 'unknown';
    $mimeTypeList = QubitDigitalObject::$qubitMimeTypes; // point to "master" mime-type array

    $filePieces = explode('.', basename($filename));
    array_splice($filePieces, 0, 1); // cut off "name" part of filename, leave extension(s)
    $rfilePieces = array_reverse($filePieces);  // Reverse the extension list

    // Go through extensions backwards, return value based on first hit
    // (assume last extension is most significant)
    foreach ($rfilePieces as $key => $ext)
    {
      $ext = strtolower($ext);  // Convert uppercase extensions to lowercase

      // Try to match this extension to a mime-type
      if (array_key_exists($ext, $mimeTypeList))
      {
        $mimeType = $mimeTypeList[$ext];
        break;
      }
    }

    return $mimeType;
  }

  /**
   * Create various representations for this digital object
   *
   * @param integer $usageId intended use of asset
   * @return QubitDigitalObject this object
   */
  public function createRepresentations($usageId, $connection = null)
  {
    switch ($this->mediaTypeId)
    {
      case QubitTerm::IMAGE_ID:
        // Scale images and create derivatives
        if ($this->canThumbnail())
        {
          if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
          {
            $this->createReferenceImage($connection);
            $this->createThumbnail($connection);
          }
          else if ($usageId == QubitTerm::REFERENCE_ID)
          {
            $this->createReferenceImage($connection);
          }
          else if ($usageId == QubitTerm::THUMBNAIL_ID)
          {
            $this->createThumbnail($connection);
          }
        }

        break;

      case QubitTerm::TEXT_ID:
        if ($this->canThumbnail())
        {
          if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
          {
            // Thumbnail PDFs (may add other formats in future)
            $this->createReferenceImage($connection);
            $this->createThumbnail($connection);

            // Extract text
            $this->extractText($connection);
          }
          else if ($usageId == QubitTerm::REFERENCE_ID)
          {
            $this->createReferenceImage($connection);
          }
          else if ($usageId == QubitTerm::THUMBNAIL_ID)
          {
            $this->createThumbnail($connection);
          }
        }

        break;

      case QubitTerm::VIDEO_ID:
        if ($usageId == QubitTerm::EXTERNAL_URI_ID || $usageId == QubitTerm::MASTER_ID)
        {
          $this->createVideoDerivative(QubitTerm::REFERENCE_ID, $connection);
          $this->createVideoDerivative(QubitTerm::THUMBNAIL_ID, $connection);
        }
        else if ($usageId == QubitTerm::REFERENCE_ID || $usageId == QubitTerm::THUMBNAIL_ID)
        {
          $this->createVideoDerivative($usageId, $connection);
        }

        break;

      case QubitTerm::AUDIO_ID:
        if (in_array($usageId, array(
          QubitTerm::EXTERNAL_URI_ID,
          QubitTerm::MASTER_ID,
          QubitTerm::REFERENCE_ID
        )))
        {
          $this->createAudioDerivative(QubitTerm::REFERENCE_ID, $connection);
        }

        break;
    }

    return $this;
  }

  /**
   * Set 'page_count' property for this asset
   *
   * NOTE: requires the ImageMagick library
   *
   * @return QubitDigitalObject this object
   */
  public function setPageCount($connection = null)
  {
    if ($this->canThumbnail() && self::hasImageMagick())
    {
      $filename = (QubitTerm::EXTERNAL_URI_ID == $this->usageId) ? $this->getLocalPath() : $this->getAbsolutePath();

      $extension = pathinfo($filename, PATHINFO_EXTENSION);

      // If processing a PDF, attempt to use pdfinfo as it's faster
      if (strtolower($extension) == 'pdf' && sfImageMagickAdapter::pdfinfoToolAvailable())
      {
        $pages = sfImageMagickAdapter::getPdfinfoPageCount($filename);
      }
      else
      {
        $command = 'identify '. $filename;
        exec($command, $output, $status);
        $pages = count($output);
      }

      if ($status == 0)
      {
        // Add "number of pages" property
        $pageCount = new QubitProperty;
        $pageCount->setObjectId($this->id);
        $pageCount->setName('page_count');
        $pageCount->setScope('digital_object');
        $pageCount->setValue($pages, array('sourceCulture' => true));
        $pageCount->save($connection);
      }
    }

    return $this;
  }

  /**
   * Get the number of pages in asset (multi-page file)
   *
   * @return integer number of pages
   */
  public function getPageCount()
  {
    if (null === $pageCount = QubitProperty::getOneByObjectIdAndName($this->id, 'page_count'))
    {
      $this->setPageCount();
      $pageCount = QubitProperty::getOneByObjectIdAndName($this->id, 'page_count');
    }

    if ($pageCount)
    {
      return (integer) $pageCount->getValue();
    }
  }

  public function getPage($index)
  {
    $criteria = new Criteria;
    $criteria->add(QubitInformationObject::PARENT_ID, $this->informationObject->id);
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);
    $criteria->setLimit(1);
    $criteria->setOffset($index);

    return QubitDigitalObject::getOne($criteria);
  }

  /**
   * Explode multi-page asset into multiple image files
   *
   * @return unknown
   */
  public function explodeMultiPageAsset()
  {
    $pageCount = $this->getPageCount();

    if ($pageCount > 1 && $this->canThumbnail())
    {
      if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
      {
        $path = $this->localPath;
      }
      else
      {
        $path = $this->getAbsolutePath();
      }

      $filenameMinusExtension = preg_replace('/\.[a-zA-Z]{2,3}$/', '', $path);

      $command = 'convert -quality 100 ';
      $command .= $path;
      $command .= ' '.$filenameMinusExtension.'_%02d.'.self::THUMB_EXTENSION;
      exec($command, $output, $status);

      if ($status == 1)
      {
        throw new sfException('Encountered error'.(is_array($output) && count($output) > 0 ? ': '.implode('\n'.$output) : ' ').' while running convert (ImageMagick).');
      }

      // Build an array of the exploded file names
      for ($i = 0; $i < $pageCount; $i++)
      {
        $fileList[] = $filenameMinusExtension.sprintf('_%02d.', $i).self::THUMB_EXTENSION;
      }
    }

    return $fileList;
  }

  /**
   * Create an info and digital object tree for multi-page assets
   *
   * For digital objects that describe a multi-page digital asset (e.g. a
   * multi-page tif image), create a derived asset for each page, create a child
   * information object and linked child digital object and move the derived
   * asset to the appropriate directory for the new (child) info object
   *
   * NOTE: Requires the Imagemagick library for creating derivative assets
   *
   * @return QubitDigitalObject this object
   */
  public function createCompoundChildren($connection = null)
  {
    // Bail out if the imagemagick library is not installed
    if (false === self::hasImageMagick())
    {
      return $this;
    }

    $pages = $this->explodeMultiPageAsset();

    foreach ($pages as $i => $filepath)
    {
      // Create a new information object
      $newInfoObject = new QubitInformationObject;
      $newInfoObject->parentId = $this->getInformationObject()->id;
      $newInfoObject->setTitle($this->getInformationObject()->getTitle().' ('.($i + 1).')');
      $newInfoObject->setPublicationStatus(sfConfig::get('app_defaultPubStatus', QubitTerm::PUBLICATION_STATUS_DRAFT_ID));
      $newInfoObject->save($connection);

      // Create and link a new digital object
      $newDigiObject = new QubitDigitalObject;
      $newDigiObject->parentId = $this->id;
      $newDigiObject->setInformationObjectId($newInfoObject->id);
      $newDigiObject->save($connection);

      // Derive new file path based on newInfoObject
      // Note: due to the limitations of this code, the compound
      // objects' indvidiual page asset paths will be the same
      // as their parent.
      $assetPath = $newDigiObject->getAssetPath($this->checksum);
      $createPath = '';
      foreach (explode('/', $assetPath) as $d)
      {
        $createPath .= '/'.$d;
        if (!is_dir(sfConfig::get('sf_web_dir').$createPath))
        {
          mkdir(sfConfig::get('sf_web_dir').$createPath, 0755);
        }
        chmod(sfConfig::get('sf_web_dir').$createPath, 0755);
      }

      // Derive new name for file based on original file name + newDigitalObject
      // id
      $filename = basename($filepath);
      $newFilepath = sfConfig::get('sf_web_dir').$assetPath.'/'.$filename;

      // Move asset to new name and path
      rename($filepath, $newFilepath);
      chmod($newFilepath, 0644);

      // Save new file information
      $newDigiObject->setPath("$assetPath/");
      $newDigiObject->setName($filename);
      $newDigiObject->setByteSize(filesize($newFilepath));
      $newDigiObject->usageId = QubitTerm::MASTER_ID;
      $newDigiObject->setMimeType(QubitDigitalObject::deriveMimeType($filename));
      $newDigiObject->mediaTypeId = $this->mediaTypeId;
      $newDigiObject->setPageCount();
      $newDigiObject->setSequence($i + 1);
      $newDigiObject->save($connection);

      // And finally create reference and thumb images for child asssets
      $newDigiObject->createRepresentations($newDigiObject->getUsageId(), $connection);
    }

    return $this;
  }

  /**
   * Test various php settings that affect file upload size and report the
   * most limiting one.
   *
   * @return integer max upload file size in bytes
   */
  public static function getMaxUploadSize()
  {
    $settings = array();
    $settings[] = self::returnBytes(ini_get('post_max_size'));
    $settings[] = self::returnBytes(ini_get('upload_max_filesize'));
    $settings[] = self::returnBytes(ini_get('memory_limit'));

    foreach ($settings as $index => $value)
    {
      if ($value == 0)
      {
        unset($settings[$index]);
      }
    }

    if (0 == count($settings))
    {
      // Unlimited
      return -1;
    }
    else
    {
      return min($settings);
    }
  }

  /**
   * Transform the php.ini notation for numbers (like '2M') to number of bytes
   *
   * Taken from http://ca2.php.net/manual/en/function.ini-get.php
   *
   * @param string $value A string denoting byte size by multiple (e.g. 2M)
   * @return integer size in bytes
   */
  protected static function returnBytes($val)
  {
    $val = trim($val);
    $last = strtolower(substr($val, -1));
    switch($last) {
      // The 'G' modifier is available since PHP 5.1.0
      case 'g':
        $val *= 1024;
      case 'm':
        $val *= 1024;
      case 'k':
        $val *= 1024;
    }

    return $val;
  }

  /*
   * -----------------------------------------------------------------------
   * IMAGE MANIPULATION METHODS
   * -----------------------------------------------------------------------
   */

  /**
   * Create a thumbnail derivative for the current digital object
   *
   * @return QubitDigitalObject
   */
  public function createThumbnail($connection = null)
  {
    // Create a thumbnail
    $derivative = $this->createImageDerivative(QubitTerm::THUMBNAIL_ID, $connection);

    return $derivative;
  }

  /**
   * Create a reference derivative for the current digital object
   *
   * @return QubitDigitalObject  The new derived reference digital object
   */
  public function createReferenceImage($connection = null)
  {
    // Create derivative
    $derivative = $this->createImageDerivative(QubitTerm::REFERENCE_ID, $connection);

    return $derivative;
  }

  private function getLocalPath()
  {
    if (null === $this->localPath && QubitTerm::EXTERNAL_URI_ID == $this->usageId)
    {
      $filename = $this->getFilenameFromUri($this->path);
      $contents = $this->downloadExternalObject($this->path);
      $this->localPath = Qubit::saveTemporaryFile($filename, $contents);
    }

    return $this->localPath;
  }

  /**
   * Create an derivative of an image (a smaller image ;)
   *
   * @param integer  $usageId  usage type id
   * @return QubitDigitalObject derivative object
   */
  public function createImageDerivative($usageId, $connection = null)
  {
    // Get max dimensions
    $maxDimensions = self::getImageMaxDimensions($usageId);

    // Build new filename and path
    if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
    {
      $originalFullPath = $this->getLocalPath();
    }
    else
    {
      $originalFullPath = $this->getAbsolutePath();
    }

    $extension = '.'.self::THUMB_EXTENSION;
    list($originalNameNoExtension) = explode('.', $this->getName());
    $derivativeName = $originalNameNoExtension.'_'.$usageId.$extension;

    // Resize
    $resizedImage = QubitDigitalObject::resizeImage($originalFullPath, $maxDimensions[0], $maxDimensions[1]);

    if (0 < strlen($resizedImage))
    {
      $derivative = new QubitDigitalObject;
      $derivative->parentId = $this->id;
      $derivative->usageId = $usageId;
      $derivative->createDerivatives = false;
      $derivative->indexOnSave = false;
      $derivative->assets[] = new QubitAsset($derivativeName, $resizedImage);
      $derivative->save($connection);

      return $derivative;
    }
  }

  /**
   * Resize this digital object (image)
   *
   * @param integer $maxwidth  Max width of resized image
   * @param integer $maxheight Max height of resized image
   *
   * @return boolean success or failure
   */
  public function resize($maxwidth, $maxheight=null)
  {
    // Only operate on digital objects that are images
    if ($this->isImage())
    {
      $filename = $this->getAbsolutePath();
      return QubitDigitalObject::resizeImage($filename, $maxwidth, $maxheight);
    }

    return false;
  }

  /**
   * Resize current digital object according to a specific usage type
   *
   * @param integer $usageId
   * @return boolean success or failure
   */
  public function resizeByUsageId($usageId)
  {
    if ($usageId == QubitTerm::REFERENCE_ID)
    {
      $maxwidth = (sfConfig::get('app_reference_image_maxwidth')) ? sfConfig::get('app_reference_image_maxwidth') : 480;
      $maxheight = null;
    }
    else if ($usageId == QubitTerm::THUMBNAIL_ID)
    {
      $maxwidth = 100;
      $maxheight = 100;
    }
    else
    {
      return false;
    }

    return $this->resize($maxwidth, $maxheight);
  }

  /**
   * Allow multiple ways of getting the max dimensions for image by usage
   *
   * @param integer $usageId  the usage type
   * @return array $maxwidth, $maxheight
   *
   * @todo Add THUMBNAIL_MAX_DIMENSION to Qubit Settings
   */
  public static function getImageMaxDimensions($usageId)
  {
    $maxwidth = $maxheight = null;

    switch ($usageId)
    {
      case QubitTerm::REFERENCE_ID:
        // Backwards compatiblity - if maxwidth Qubit setting doesn't exist
        if (!$maxwidth = sfConfig::get('app_reference_image_maxwidth'))
        {
          $maxwidth = 480;
        }
        $maxheight = $maxwidth;
        break;
      case QubitTerm::THUMBNAIL_ID:
        $maxwidth = 270;
        $maxheight = 1024;
        break;
      case QubitTerm::COMPOUND_ID:
        if (!$maxwidth = sfConfig::get('app_reference_image_maxwidth'))
        {
          $maxwidth = 480;
        }
        $maxheight = $maxwidth; // Full maxwidth dimensions (480 default)
        $maxwidth = floor($maxwidth / 2) - 10; // 1/2 size - gutter (230 default)
        break;
    }

    return array($maxwidth, $maxheight);
  }

  /**
   * Resize an image using the sfThubmnail Plugin.
   *
   * @param string $originalImageName
   * @param integer $width
   * @param integer $height
   *
   * @return string (thumbnail's bitstream)
   */
  public static function resizeImage($originalImageName, $width=null, $height=null)
  {
    $mimeType = QubitDigitalObject::deriveMimeType($originalImageName);

    // Get thumbnail adapter
    if (!$adapter = self::getThumbnailAdapter())
    {
      return false;
    }

    // Check that this file can be thumbnailed, or return false
    if (self::canThumbnailMimeType($mimeType) == false)
    {
      return false;
    }

    $page = 1;
    // I avoid sfConfig as it's not always available (CLI context)
    if ($mimeType === 'application/pdf' &&
      null !== $setting = QubitSetting::getByName('digital_object_derivatives_pdf_page_number'))
    {
      if (0 !== $p = intval($setting->getValue(array('sourceCulture' => true))))
      {
        $page = $p;
      }
    }

    // Create a thumbnail
    try
    {
      $newImage = new sfThumbnail($width, $height, true, false, 75, $adapter, array('extract' => $page));
      $newImage->loadFile($originalImageName);
    }
    catch (Exception $e)
    {
      return false;
    }

    return $newImage->toString('image/jpeg');
  }

  /**
   * Get a valid adapter for the sfThumbnail library (either GD or ImageMagick)
   * Cache the adapter value because is very expensive to calculate it
   *
   * @return mixed  name of adapter on success, false on failure
   */
  public static function getThumbnailAdapter()
  {
    $adapter = false;

    $context = sfContext::getInstance();
    if ($context->has('thumbnailAdapter'))
    {
      return $context->get('thumbnailAdapter');
    }

    if (QubitDigitalObject::hasImageMagick())
    {
      $adapter = 'sfImageMagickAdapter';
    }
    else if (QubitDigitalObject::hasGdExtension())
    {
      $adapter = 'sfGDAdapter';
    }

    $context->set('thumbnailAdapter', $adapter);

    return $adapter;
  }

  /**
   * Test if ImageMagick library is installed
   *
   * @return boolean  true if ImageMagick is found
   */
  public static function hasImageMagick()
  {
    $command = 'convert -version';
    exec($command, $output, $status);

    return 0 < count($output) && false !== strpos($output[0], 'ImageMagick');
  }

  /**
   * Test if GD Extension for PHP is installed
   *
   * @return boolean true if GD extension found
   */
  public static function hasGdExtension()
  {
    return extension_loaded('gd');
  }

  /**
   * Wrapper for canThumbnailMimeType() for use on instantiated objects
   *
   * @return boolean
   * @see canThumbnailMimeType
   */
  public function canThumbnail()
  {
    return self::canThumbnailMimeType($this->mimeType);
  }

  /**
   * Test if current digital object can be thumbnailed
   *
   * @param string    The current thumbnailing adapter
   * @return boolean  true if thumbnail is possible
   */
  public static function canThumbnailMimeType($mimeType)
  {
    if (!$adapter = self::getThumbnailAdapter())
    {
      return false;
    }

    $canThumbnail = false;

    // For Images, we can create thumbs with either GD or ImageMagick
    if (substr($mimeType, 0, 5) == 'image' && strlen($adapter))
    {
      $canThumbnail = true;
    }

    // For PDFs we can only create thumbs with ImageMagick
    else if ($mimeType == 'application/pdf' && $adapter == 'sfImageMagickAdapter')
    {
      $canThumbnail = true;
    }

    return $canThumbnail;
  }

  /**
   * Return true if derived mimeType is "image/*"
   *
   * @param string $filename
   * @return boolean
   */
  public static function isImageFile($filename)
  {
    $mimeType = self::deriveMimeType($filename);
    if (strtolower(substr($mimeType, 0, 5)) == 'image')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  public static function isAudioFile($filename)
  {
    $mimeType = self::deriveMimeType($filename);
    if (strtolower(substr($mimeType, 0, 5)) == 'audio')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /*
   * -----------------------------------------------------------------------
   * VIDEO
   * -----------------------------------------------------------------------
   */
  public function createAudioDerivative($usageId, $connection = null)
  {
    if (QubitTerm::REFERENCE_ID != $usageId)
    {
      return false;
    }

    if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
    {
      $originalFullPath = $this->getLocalPath();

      list($originalNameNoExtension) = explode('.', $this->getName());
      $derivativeName = $originalNameNoExtension.'_'.$usageId.'.mp3';

      $pathParts = pathinfo($this->getLocalPath());

      $derivativeFullPath = $pathParts['dirname'].'/'.$derivativeName;

      self::convertAudioToMp3($originalFullPath, $derivativeFullPath);

      if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
      {
        $derivative = new QubitDigitalObject;
        $derivative->parentId = $this->id;
        $derivative->usageId = $usageId;
        $derivative->assets[] = new QubitAsset($derivativeName, file_get_contents($derivativeFullPath));
        $derivative->createDerivatives = false;
        $derivative->indexOnSave = false;
        $derivative->save($connection);
      }
    }
    else
    {
      $originalFullPath = $this->getAbsolutePath();

      list($originalNameNoExtension) = explode('.', $this->getName());
      $derivativeName = $originalNameNoExtension.'_'.$usageId.'.mp3';

      $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;

      self::convertAudioToMp3($originalFullPath, $derivativeFullPath);

      if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
      {
        $derivative = new QubitDigitalObject;
        $derivative->setPath($this->getPath());
        $derivative->setName($derivativeName);
        $derivative->parentId = $this->id;
        $derivative->setByteSize($byteSize);
        $derivative->usageId = $usageId;
        $derivative->setMimeAndMediaType();
        $derivative->createDerivatives = false;
        $derivative->indexOnSave = false;
        $derivative->save($connection);
      }
    }
  }

  public static function convertAudioToMp3($originalPath, $newPath)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    $command = 'ffmpeg -y -i '.$originalPath.' '.$newPath.' 2>&1';
    exec($command, $output, $status);

    if ($status)
    {
      $error = true;

      for ($i = count($output) - 1; $i >= 0; $i--)
      {
        if (strpos($output[$i], 'output buffer too small'))
        {
          $error = false;

          break;
        }
      }
    }

    chmod($newPath, 0644);

    return true;
  }

  /*
   * -----------------------------------------------------------------------
   * VIDEO
   * -----------------------------------------------------------------------
   */

  /**
   * Create video derivatives (either flv movie or thumbnail)
   *
   * @param integer  $usageId  usage type id
   * @return QubitDigitalObject derivative object
   */
  public function createVideoDerivative($usageId, $connection = null)
  {
    // Build new filename and path
    $originalFullPath = $this->getAbsolutePath();
    list($originalNameNoExtension) = explode('.', $this->getName());

    switch ($usageId)
    {
      case QubitTerm::REFERENCE_ID:
        $derivativeName = $originalNameNoExtension.'_'.$usageId.'.flv';
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        self::convertVideoToFlash($originalFullPath, $derivativeFullPath);
        break;
      case QubitTerm::THUMBNAIL_ID:
      default:
        $extension = '.'.self::THUMB_EXTENSION;
        $derivativeName = $originalNameNoExtension.'_'.$usageId.$extension;
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        $maxDimensions = self::getImageMaxDimensions($usageId);
        self::convertVideoToThumbnail($originalFullPath, $derivativeFullPath, $maxDimensions[0], $maxDimensions[1]);
    }

    if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
    {
      $derivative = new QubitDigitalObject;
      $derivative->setPath($this->getPath());
      $derivative->setName($derivativeName);
      $derivative->parentId = $this->id;
      $derivative->setByteSize($byteSize);
      $derivative->usageId = $usageId;
      $derivative->setMimeAndMediaType();
      $derivative->createDerivatives = false;
      $derivative->indexOnSave = false;
      $derivative->save($connection);

      return $derivative;
    }
    $originalFullPath = $this->getAbsolutePath();
    list($originalNameNoExtension) = explode('.', $this->getName());

    switch ($usageId)
    {
      case QubitTerm::REFERENCE_ID:
        $derivativeName = $originalNameNoExtension.'_'.$usageId.'.flv';
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        self::convertVideoToFlash($originalFullPath, $derivativeFullPath);
        break;
      case QubitTerm::THUMBNAIL_ID:
      default:
        $extension = '.'.self::THUMB_EXTENSION;
        $derivativeName = $originalNameNoExtension.'_'.$usageId.$extension;
        $derivativeFullPath = sfConfig::get('sf_web_dir').$this->getPath().$derivativeName;
        $maxDimensions = self::getImageMaxDimensions($usageId);
        self::convertVideoToThumbnail($originalFullPath, $derivativeFullPath, $maxDimensions[0], $maxDimensions[1]);
    }

    if (file_exists($derivativeFullPath) && 0 < ($byteSize = filesize($derivativeFullPath)))
    {
      $derivative = new QubitDigitalObject;
      $derivative->setPath($this->getPath());
      $derivative->setName($derivativeName);
      $derivative->parentId = $this->id;
      $derivative->setByteSize($byteSize);
      $derivative->usageId = $usageId;
      $derivative->setMimeAndMediaType();
      $derivative->createDerivatives = false;
      $derivative->indexOnSave = false;
      $derivative->save($connection);

      return $derivative;
    }
  }

  /**
   * Test if FFmpeg library is installed
   *
   * @return boolean  true if FFmpeg is found
   */
  public static function hasFfmpeg()
  {
    $command = 'ffmpeg -version 2>&1';
    exec($command, $output, $status);

    return 0 < count($output) && false !== strpos(strtolower($output[0]), 'ffmpeg');
  }

  /**
   * Create a flash video derivative using the FFmpeg library.
   *
   * @param string  $originalPath path to original video
   * @param string  $newPath      path to derivative video
   * @param integer $maxwidth     derivative video maximum width
   * @param integer $maxheight    derivative video maximum height
   *
   * @return boolean  success or failure
   *
   * @todo implement $maxwidth and $maxheight constraints on video
   */
  public static function convertVideoToFlash($originalPath, $newPath, $width=null, $height=null)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    $command = 'ffmpeg -y -i '.$originalPath.' -ar 44100 '.$newPath.' 2>&1';
    exec($command, $output, $status);

    chmod($newPath, 0644);

    return true;
  }

  /**
   * Create a flash video derivative using the FFmpeg library.
   *
   * @param string  $originalPath path to original video
   * @param string  $newPath      path to derivative video
   * @param integer $maxwidth     derivative video maximum width
   * @param integer $maxheight    derivative video maximum height
   *
   * @return boolean  success or failure
   *
   * @todo implement $maxwidth and $maxheight constraints on video
   */
  public static function convertVideoToThumbnail($originalPath, $newPath, $width = null, $height = null)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    // Do conversion to jpeg
    $command = "ffmpeg -itsoffset -30 -i $originalPath -vframes 1 -vf \"scale='min($width,iw):-1'\" $newPath";
    exec($command.' 2>&1', $output, $status);

    chmod($newPath, 0644);

    return true;
  }

  /**
   * Return true if derived mimeType is "video/*"
   *
   * @param string $filename
   * @return boolean
   */
  public static function isVideoFile($filename)
  {
    $mimeType = self::deriveMimeType($filename);
    if (strtolower(substr($mimeType, 0, 5)) == 'video')
    {
      return true;
    }
    else
    {
      return false;
    }
  }

  /**
   * Create a thumbnail from a video file using FFmpeg library
   *
   * @param string $originalImageName
   * @param integer $width
   * @param integer $height
   *
   * @return string (thumbnail's bitstream)
   */
  public static function createThumbnailFromVideo($originalPath, $width=null, $height=null)
  {
    // Test for FFmpeg library
    if (!self::hasFfmpeg())
    {
      return false;
    }

    Qubit::createUploadDirsIfNeeded();
    $tmpDir = sfConfig::get('sf_upload_dir').'/tmp';
    
    // Get a unique file name (to avoid clashing file names)
    $tmpFileName = null;
    $tmpFilePath = null;
    while (file_exists($tmpFilePath) || null === $tmpFileName)
    {
      $uniqueString = substr(md5(time().$tmpFileName), 0, 8);
      $tmpFileName = 'TMP'.$uniqueString;
      $tmpFilePath = $tmpDir.'/'.$tmpFileName.'.jpg';
    }

    // Do conversion to jpeg
    $command = 'ffmpeg -i '.$originalPath.' -vframes 1 -an -f image2 -s '.$width.'x'.$height.' '.$tmpFilePath.' 2>&1';
    exec($command, $output, $status);

    chmod($tmpFilePath, 0644);

    return file_get_contents($tmpFilePath);
  }


  /*
   * -----------------------------------------------------------------------
   * TEXT METHODS
   * -----------------------------------------------------------------------
   */

  public static function hasPdfToText()
  {
    exec('which pdftotext', $output, $status);

    return 0 == $status && 0 < count($output);
  }

  /**
   * Test if text extraction is possible
   *
   * @param string mime-type
   * @return boolean true if extraction is supported
   */
  public static function canExtractText($mimeType)
  {
    // Only works for PDFs
    if ('application/pdf' != $mimeType)
    {
      return false;
    }

    // Requires pdftotext binary
    if (!self::hasPdfToText())
    {
      return false;
    }

    return true;
  }

  /**
   * Extracts text from the current digital object
   * and creates a 'transcript' property
   *
   * @return String Text extracted
   */
  public function extractText($connection = null)
  {
    if (!self::canExtractText($this->mimeType))
    {
      return;
    }

    if (QubitTerm::EXTERNAL_URI_ID == $this->usageId)
    {
      $path = $this->localPath;

      // Create new temporary copy from external resources if the old copy is missing
      if (!isset($path) || !file_exists($path))
      {
        $filename = $this->getFilenameFromUri($this->getPath());
        $contents = $this->downloadExternalObject($this->getPath());

        if (false === $path = Qubit::saveTemporaryFile($filename, $contents))
        {
          return;
        }
      }
    }
    else
    {
      $path = $this->getAbsolutePath();
    }

    // Stop if the local copy is missing
    if (!file_exists($path))
    {
      return;
    }

    $command = sprintf('pdftotext %s - 2> /dev/null', $path);
    exec($command, $output, $status);

    if (0 == $status && 0 < count($output))
    {
      $text = implode(PHP_EOL, $output);

      // Update or create 'transcript' property
      $criteria = new Criteria;
      $criteria->add(QubitProperty::OBJECT_ID, $this->id);
      $criteria->add(QubitProperty::NAME, 'transcript');
      $criteria->add(QubitProperty::SCOPE, 'Text extracted from source PDF file\'s text layer using pdftotext');

      if (null === $property = QubitProperty::getOne($criteria))
      {
        $property = new QubitProperty;
        $property->objectId = $this->id;
        $property->name = 'transcript';
        $property->scope = 'Text extracted from source PDF file\'s text layer using pdftotext';
      }

      $property->value = $text;
      $property->indexOnSave = false;

      $property->save($connection);

      return $text;
    }
  }

  /* -----------------------------------------------------------------------
   * CHECKSUMS
   * --------------------------------------------------------------------- */

  /**
   * Set a checksum value for this digital object
   *
   * @param string $value   the checksum string
   * @param array  $options optional parameters
   *
   * @return QubitDigitalObject this object
   */
  public function setChecksum($value, $options)
  {
    if (isset($options['checksumType']))
    {
      $this->setChecksumType($options['checksumType']);
    }

    $this->checksum = $value;

    return $this;
  }

  /**
   * Generate a checksum from the file specified
   *
   * @param string $filename name of file
   * @return string checksum
   */
  public function generateChecksumFromFile($filename)
  {
    if (!isset($this->checksumType))
    {
      $this->checksumType = 'sha256';
    }

    if (!in_array($this->checksumType, hash_algos()))
    {
      throw new Exception('Invalid checksum this->checksumType "'.$this->checksumType.'"');
    }

    $this->checksum = hash_file($this->checksumType, $filename);

    return $this;
  }

  /* -----------------------------------------------------------------------
   * Display as compound object
   * --------------------------------------------------------------------- */

  /**
   * Setter for "displayAsCompound" property
   *
   * @param string $value new value for property
   * @return QubitInformationObject this object
   */
  public function setDisplayAsCompoundObject($value)
  {
    $criteria = new Criteria;
    $criteria->add(QubitProperty::OBJECT_ID, $this->id);
    $criteria->add(QubitProperty::NAME, 'displayAsCompound');

    $displayAsCompoundProp = QubitProperty::getOne($criteria);
    if (is_null($displayAsCompoundProp))
    {
      $displayAsCompoundProp = new QubitProperty;
      $displayAsCompoundProp->setObjectId($this->id);
      $displayAsCompoundProp->setName('displayAsCompound');
    }

    $displayAsCompoundProp->setValue($value, array('sourceCulture' => true));
    $displayAsCompoundProp->save();

    return $this;
  }

  /**
   * Getter for related "displayAsCompound" property
   *
   * @return string property value
   */
  public function getDisplayAsCompoundObject()
  {
    $displayAsCompoundProp = QubitProperty::getOneByObjectIdAndName($this->id, 'displayAsCompound');
    if (null !== $displayAsCompoundProp)
    {
      return $displayAsCompoundProp->getValue(array('sourceCulture' => true));
    }
  }

  /**
   * Decide whether to show child digital objects as a compound object based
   * on 'displayAsCompound' toggle and available digital objects.
   *
   * @return boolean
   */
  public function showAsCompoundDigitalObject()
  {
    // Return false if this digital object is not linked directly to an
    // information object
    if (null === $this->informationObjectId)
    {
      return false;
    }

    // Return false if "show compound" toggle is not set to '1' (yes)
    $showCompoundProp = QubitProperty::getOneByObjectIdAndName($this->id, 'displayAsCompound');
    if (null === $showCompoundProp || '1' != $showCompoundProp->getValue(array('sourceCulture' => true)) )
    {
      return false;
    }

    // Return false if this object has no children with digital objects
    $criteria = new Criteria;
    $criteria->addJoin(QubitInformationObject::ID, QubitDigitalObject::INFORMATION_OBJECT_ID);
    $criteria->add(QubitInformationObject::PARENT_ID, $this->informationObjectId);

    if (0 === count(QubitDigitalObject::get($criteria)))
    {
      return false;
    }

    return true;
  }

  /**
   * Recursively remove empty directories
   *
   * @param string $dir directory name
   *
   * @return void
   */
  public static function pruneEmptyDirs($dir)
  {
    // Remove any extra whitespace or trailing slash
    $dir = rtrim(trim($dir), '/');

    do
    {
      if (sfConfig::get('sf_upload_dir') == $dir || sfConfig::get('sf_upload_dir').'/r' == $dir)
      {
        return; // Protect uploads/ and uploads/r/
      }

      if (self::isEmptyDir($dir))
      {
        rmdir($dir);
      }
      else
      {
        return;
      }
    } while (strrpos($dir, '/') && $dir = substr($dir, 0, strrpos($dir, '/')));
  }

  /**
   * Check if directory is empty
   *
   * @param string $dir directory name
   *
   * @return boolean true if empty
   */
  public static function isEmptyDir($dir)
  {
    if (is_dir($dir))
    {
      $files = scandir($dir);

      return (2 >= count($files)); // Always have "." and ".." dirs
    }
  }

  /**
   * Check if uploads are allowed
   *
   * @return boolean true if allowed
   */
  public static function isUploadAllowed()
  {
    return 0 !== sfConfig::get('app_upload_limit', 0);
  }

  /**
   * Check the upload limit has been reached
   *
   * @return boolean true if reached
   */
  public static function reachedAppUploadLimit()
  {
    if (sfConfig::get('app_upload_limit', 0) < 1)
    {
      return false;
    }

    $size = Qubit::getDirectorySize(sfConfig::get('sf_upload_dir'));
    if ($size < 0)
    {
      return false;
    }

    return $size > sfConfig::get('app_upload_limit', 0) * pow(1024, 3);
  }
}
