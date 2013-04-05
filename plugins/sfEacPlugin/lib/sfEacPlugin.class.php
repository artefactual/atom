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

class sfEacPlugin implements ArrayAccess
{
  protected
    $resource;

  // Arrays not allowed in class constants
  protected static
    $from6392 = array(

      // :r !curl http://loc.gov/standards/iso639-2/ISO-639-2_utf-8.txt
      // 2>/dev/null | sed -n "s/\([^|]\+\)|[^|]*|\([^|]\+\)|\([^|]*\).*/'\1'
      // => '\2', \/\/ \3/p"
      'aar' => 'aa', // Afar
      'abk' => 'ab', // Abkhazian
      'afr' => 'af', // Afrikaans
      'aka' => 'ak', // Akan
      'alb' => 'sq', // Albanian
      'amh' => 'am', // Amharic
      'ara' => 'ar', // Arabic
      'arg' => 'an', // Aragonese
      'arm' => 'hy', // Armenian
      'asm' => 'as', // Assamese
      'ava' => 'av', // Avaric
      'ave' => 'ae', // Avestan
      'aym' => 'ay', // Aymara
      'aze' => 'az', // Azerbaijani
      'bak' => 'ba', // Bashkir
      'bam' => 'bm', // Bambara
      'baq' => 'eu', // Basque
      'bel' => 'be', // Belarusian
      'ben' => 'bn', // Bengali
      'bih' => 'bh', // Bihari languages
      'bis' => 'bi', // Bislama
      'bos' => 'bs', // Bosnian
      'bre' => 'br', // Breton
      'bul' => 'bg', // Bulgarian
      'bur' => 'my', // Burmese
      'cat' => 'ca', // Catalan; Valencian
      'cha' => 'ch', // Chamorro
      'che' => 'ce', // Chechen
      'chi' => 'zh', // Chinese
      'chu' => 'cu', // Church Slavic; Old Slavonic; Church Slavonic; Old
                     // Bulgarian; Old Church Slavonic
      'chv' => 'cv', // Chuvash
      'cor' => 'kw', // Cornish
      'cos' => 'co', // Corsican
      'cre' => 'cr', // Cree
      'cze' => 'cs', // Czech
      'dan' => 'da', // Danish
      'div' => 'dv', // Divehi; Dhivehi; Maldivian
      'dut' => 'nl', // Dutch; Flemish
      'dzo' => 'dz', // Dzongkha
      'eng' => 'en', // English
      'epo' => 'eo', // Esperanto
      'est' => 'et', // Estonian
      'ewe' => 'ee', // Ewe
      'fao' => 'fo', // Faroese
      'fij' => 'fj', // Fijian
      'fin' => 'fi', // Finnish
      'fre' => 'fr', // French
      'fry' => 'fy', // Western Frisian
      'ful' => 'ff', // Fulah
      'geo' => 'ka', // Georgian
      'ger' => 'de', // German
      'gla' => 'gd', // Gaelic; Scottish Gaelic
      'gle' => 'ga', // Irish
      'glg' => 'gl', // Galician
      'glv' => 'gv', // Manx
      'gre' => 'el', // Greek, Modern (1453-)
      'grn' => 'gn', // Guarani
      'guj' => 'gu', // Gujarati
      'hat' => 'ht', // Haitian; Haitian Creole
      'hau' => 'ha', // Hausa
      'heb' => 'he', // Hebrew
      'her' => 'hz', // Herero
      'hin' => 'hi', // Hindi
      'hmo' => 'ho', // Hiri Motu
      'hrv' => 'hr', // Croatian
      'hun' => 'hu', // Hungarian
      'ibo' => 'ig', // Igbo
      'ice' => 'is', // Icelandic
      'ido' => 'io', // Ido
      'iii' => 'ii', // Sichuan Yi; Nuosu
      'iku' => 'iu', // Inuktitut
      'ile' => 'ie', // Interlingue; Occidental
      'ina' => 'ia', // Interlingua (International Auxiliary Language
                     // Association)
      'ind' => 'id', // Indonesian
      'ipk' => 'ik', // Inupiaq
      'ita' => 'it', // Italian
      'jav' => 'jv', // Javanese
      'jpn' => 'ja', // Japanese
      'kal' => 'kl', // Kalaallisut; Greenlandic
      'kan' => 'kn', // Kannada
      'kas' => 'ks', // Kashmiri
      'kau' => 'kr', // Kanuri
      'kaz' => 'kk', // Kazakh
      'khm' => 'km', // Central Khmer
      'kik' => 'ki', // Kikuyu; Gikuyu
      'kin' => 'rw', // Kinyarwanda
      'kir' => 'ky', // Kirghiz; Kyrgyz
      'kom' => 'kv', // Komi
      'kon' => 'kg', // Kongo
      'kor' => 'ko', // Korean
      'kua' => 'kj', // Kuanyama; Kwanyama
      'kur' => 'ku', // Kurdish
      'lao' => 'lo', // Lao
      'lat' => 'la', // Latin
      'lav' => 'lv', // Latvian
      'lim' => 'li', // Limburgan; Limburger; Limburgish
      'lin' => 'ln', // Lingala
      'lit' => 'lt', // Lithuanian
      'ltz' => 'lb', // Luxembourgish; Letzeburgesch
      'lub' => 'lu', // Luba-Katanga
      'lug' => 'lg', // Ganda
      'mac' => 'mk', // Macedonian
      'mah' => 'mh', // Marshallese
      'mal' => 'ml', // Malayalam
      'mao' => 'mi', // Maori
      'mar' => 'mr', // Marathi
      'may' => 'ms', // Malay
      'mlg' => 'mg', // Malagasy
      'mlt' => 'mt', // Maltese
      'mon' => 'mn', // Mongolian
      'nau' => 'na', // Nauru
      'nav' => 'nv', // Navajo; Navaho
      'nbl' => 'nr', // Ndebele, South; South Ndebele
      'nde' => 'nd', // Ndebele, North; North Ndebele
      'ndo' => 'ng', // Ndonga
      'nep' => 'ne', // Nepali
      'nno' => 'nn', // Norwegian Nynorsk; Nynorsk, Norwegian
      'nob' => 'nb', // Bokmål, Norwegian; Norwegian Bokmål
      'nor' => 'no', // Norwegian
      'nya' => 'ny', // Chichewa; Chewa; Nyanja
      'oci' => 'oc', // Occitan (post 1500); Provençal
      'oji' => 'oj', // Ojibwa
      'ori' => 'or', // Oriya
      'orm' => 'om', // Oromo
      'oss' => 'os', // Ossetian; Ossetic
      'pan' => 'pa', // Panjabi; Punjabi
      'per' => 'fa', // Persian
      'pli' => 'pi', // Pali
      'pol' => 'pl', // Polish
      'por' => 'pt', // Portuguese
      'pus' => 'ps', // Pushto; Pashto
      'que' => 'qu', // Quechua
      'roh' => 'rm', // Romansh
      'rum' => 'ro', // Romanian; Moldavian; Moldovan
      'run' => 'rn', // Rundi
      'rus' => 'ru', // Russian
      'sag' => 'sg', // Sango
      'san' => 'sa', // Sanskrit
      'sin' => 'si', // Sinhala; Sinhalese
      'slo' => 'sk', // Slovak
      'slv' => 'sl', // Slovenian
      'sme' => 'se', // Northern Sami
      'smo' => 'sm', // Samoan
      'sna' => 'sn', // Shona
      'snd' => 'sd', // Sindhi
      'som' => 'so', // Somali
      'sot' => 'st', // Sotho, Southern
      'spa' => 'es', // Spanish; Castilian
      'srd' => 'sc', // Sardinian
      'srp' => 'sr', // Serbian
      'ssw' => 'ss', // Swati
      'sun' => 'su', // Sundanese
      'swa' => 'sw', // Swahili
      'swe' => 'sv', // Swedish
      'tah' => 'ty', // Tahitian
      'tam' => 'ta', // Tamil
      'tat' => 'tt', // Tatar
      'tel' => 'te', // Telugu
      'tgk' => 'tg', // Tajik
      'tgl' => 'tl', // Tagalog
      'tha' => 'th', // Thai
      'tib' => 'bo', // Tibetan
      'tir' => 'ti', // Tigrinya
      'ton' => 'to', // Tonga (Tonga Islands)
      'tsn' => 'tn', // Tswana
      'tso' => 'ts', // Tsonga
      'tuk' => 'tk', // Turkmen
      'tur' => 'tr', // Turkish
      'twi' => 'tw', // Twi
      'uig' => 'ug', // Uighur; Uyghur
      'ukr' => 'uk', // Ukrainian
      'urd' => 'ur', // Urdu
      'uzb' => 'uz', // Uzbek
      'ven' => 've', // Venda
      'vie' => 'vi', // Vietnamese
      'vol' => 'vo', // Volapük
      'wel' => 'cy', // Welsh
      'wln' => 'wa', // Walloon
      'wol' => 'wo', // Wolof
      'xho' => 'xh', // Xhosa
      'yid' => 'yi', // Yiddish
      'yor' => 'yo', // Yoruba
      'zha' => 'za', // Zhuang; Chuang
      'zul' => 'zu'); // Zulu

  public function __construct(QubitActor $resource)
  {
    $this->resource = $resource;
  }

  public function offsetExists($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__isset'), $args);
  }

  public function __get($name)
  {
    switch ($name)
    {
      case 'biogHist':

        return self::toDiscursiveSet($this->resource->history);

      case 'entityType':

        switch ($this->resource->entityTypeId)
        {
          case QubitTerm::CORPORATE_BODY_ID:

            return 'corporateBody';

          case QubitTerm::FAMILY_ID:

            return 'family';

          case QubitTerm::PERSON_ID:

            return 'person';
        }

        return;

      case 'existDates':

        // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>, simple
        // natural language parsing?
        return '<date>'.esc_specialchars($this->resource->datesOfExistence).'</date>';

      case 'generalContext':

        return self::toDiscursiveSet($this->resource->generalContext);

      case 'maintenanceHistory':
        ProjectConfiguration::getActive()->loadHelpers('Date');

        $createdAt = format_date($this->resource->createdAt, 's');
        $updatedAt = format_date($this->resource->updatedAt, 's');
        $createdDisplay = format_date($this->resource->createdAt, 'F');
        $updatedDisplay = format_date($this->resource->updatedAt, 'F');

        $isaar = new sfIsaarPlugin($this->resource);

        $revisionHistory = $this->resource->getRevisionHistory(array('cultureFallback' => true));
        $maintenanceNotes = $isaar->maintenanceNotes;

        return <<<return
<maintenanceEvent id="5.4.6">
  <eventDescription>$revisionHistory</eventDescription>
  <eventDateTime standardDateTime="$createdAt">$createdDisplay</eventDateTime>
</maintenanceEvent>

<maintenanceEvent id="5.4.9">
  <eventDescription>$maintenanceNotes</eventDescription>
  <eventDateTime standardDateTime="$updatedAt">$updatedDisplay</eventDateTime>
</maintenanceEvent>

return;

      case 'maintenanceStatus':

        switch (strtolower($this->resource->descriptionStatus))
        {
          case 'draft':

            return 'new';

          case 'revised':

            return 'revised';

          case 'final':

            return 'deleted';

          default:

            return 'new';
        }

      case 'publicationStatus':

        return 'approved';

      case 'resourceRelation':
        $criteria = new Criteria;
        $criteria->add(QubitEvent::ACTOR_ID, $this->resource->id);
        $criteria->addJoin(QubitEvent::INFORMATION_OBJECT_ID, QubitInformationObject::ID);

        return QubitEvent::get($criteria);

      case 'functionRelation':
        $criteria = new Criteria;
        $criteria->addAlias('subj', QubitObject::TABLE_NAME);
        $criteria->addJoin(QubitRelation::SUBJECT_ID, 'subj.id');
        $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
        $criteria->add('subj.class_name', 'QubitFunction');

        return QubitRelation::get($criteria);

      case 'structureOrGenealogy':

        return self::toDiscursiveSet($this->resource->internalStructures);

      case 'subjectOf':
        $criteria = new Criteria;
        $criteria->add(QubitRelation::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitRelation::TYPE_ID, QubitTerm::NAME_ACCESS_POINT_ID);

        return QubitRelation::get($criteria);
    }
  }

  public function offsetGet($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__get'), $args);
  }

  public function __set($name, $value)
  {
    switch ($name)
    {
      case 'biogHist':
        $this->resource->history = self::fromDiscursiveSet($value);

        return $this;

      case 'entityType':

        switch ($value)
        {
          case 'corporateBody':
            $this->resource->entityTypeId = QubitTerm::CORPORATE_BODY_ID;

            return $this;

          case 'family':
            $this->resource->entityTypeId = QubitTerm::FAMILY_ID;

            return $this;

          case 'person':
            $this->resource->entityTypeId = QubitTerm::PERSON_ID;

            return $this;
        }

        return $this;

      case 'existDates':

        // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>
        $this->resource->datesOfExistence = $value->text();

        return $this;

      case 'generalContext':
        $this->resource->generalContext = self::fromDiscursiveSet($value);

        return $this;

      case 'maintenanceHistory':

        // TODO <maintenanceEvent/>, <agent/>, <agentType/>

        $criteria = new Criteria;
        $criteria->add(QubitNote::OBJECT_ID, $this->resource->id);
        $criteria->add(QubitNote::TYPE_ID, QubitTerm::MAINTENANCE_NOTE_ID);

        if (1 == count($query = QubitNote::get($criteria)))
        {
          $item = $query[0];
        }
        else
        {
          $item = new QubitNote;
          $item->typeId = QubitTerm::MAINTENANCE_NOTE_ID;

          $this->resource->notes[] = $item;
        }

        $item->content = $value->text();

        return $this;

      case 'maintenanceStatus':

        $descriptionStatusMap = array();
        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DESCRIPTION_STATUS_ID) as $item)
        {
          $descriptionStatusMap[$item->name] = $item->id;
        }

        switch ($value)
        {
          case 'revised':
            $this->resource->descriptionStatusId = $descriptionStatusMap['Revised'];

            break;

          case 'deleted':
            $this->resource->descriptionStatusId = $descriptionStatusMap['Final'];

            break;

          case 'new':
          default:
            $this->resource->descriptionStatusId = $descriptionStatusMap['Draft'];
        }

        return $this;

      case 'publicationStatus':

        // TODO
        return $this;

      case 'structureOrGenealogy':
        $this->resource->internalStructures = self::fromDiscursiveSet($value);

        return $this;

      case 'descriptionDetail':

        foreach (QubitTaxonomy::getTermsById(QubitTaxonomy::DESCRIPTION_DETAIL_LEVEL_ID) as $item)
        {
          if($item == trim($value))
          {
            $this->resource->descriptionDetailId = $item->id;

            break;
          }
        }

        return $this;
    }
  }

  public function offsetSet($offset, $value)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__set'), $args);
  }

  public function offsetUnset($offset)
  {
    $args = func_get_args();

    return call_user_func_array(array($this, '__unset'), $args);
  }

  public function parse($doc)
  {
    require_once sfConfig::get('sf_root_dir').'/vendor/FluentDOM/FluentDOM.php';

    $fd = FluentDOM($doc)
      ->namespaces(array('eac' => 'urn:isbn:1-931666-33-4'));

    $this->resource->sourceStandard = 'http://eac.staatsbibliothek-berlin.de/schema/cpf.xsd';

    $this->resource->descriptionIdentifier = $fd->find('eac:control/eac:recordId')->text();

    //$fd->find('eac:control/eac:otherRecordId');

    $this->maintenanceStatus = $fd->find('eac:control/eac:maintenanceStatus')->text();

    $this->publicationStatus = $fd->find('eac:control/eac:publicationStatus')->text();

    // TODO <descriptiveNote/>, <otherAgencyCode/>
    $this->resource->institutionResponsibleIdentifier = $fd->find('eac:control/eac:maintenanceAgency/eac:agencyName')->text();

    // TODO <descriptiveNote/>

    $languages = array();
    foreach ($fd->find('eac:control/eac:languageDeclaration/eac:language') as $node)
    {
      $languages[] = $this->from6392($node->attributes->getNamedItem("languageCode")->textContent);
    }
    $this->resource->language = $languages;

    $scripts = array();
    foreach ($fd->find('eac:control/eac:languageDeclaration/eac:script') as $node)
    {
      $scripts[] = $this->from6392($node->attributes->getNamedItem("scriptCode")->textContent);
    }
    $this->resource->script = $scripts;

    // conventionDeclaration/abbreviation is an identifier, referenced by e.g.
    // <authorizedForm/> and <alternativeForm/>
    //
    // TODO <descriptiveNote/>
    $this->resource->rules = $fd->find('eac:control/eac:conventionDeclaration/eac:citation')->text();

    // TODO <abbreviation/>, <descriptiveNote/>
    //$fd->find('eac:control/eac:localTypeDeclaration');

    // TODO <date/>, <dateRange/>, <term/>
    $this->descriptionDetail = $fd->find('eac:control/eac:localControl[@localType="detailLevel"]/eac:term')->text();

    $this->resource->revisionHistory = $fd->find('eac:control/eac:maintenanceHistory/eac:maintenanceEvent[@id="5.4.6"]/eac:eventDescription')->text();;

    $this->maintenanceHistory = $fd->find('eac:control/eac:maintenanceHistory/eac:maintenanceEvent[@id="5.4.9"]/eac:eventDescription');

    // TODO <descriptiveNote/>, @lastDateTimeVerified
    $this->resource->sources = $fd->find('eac:control/eac:sources/eac:source/eac:sourceEntry')->text();

    // TODO eac:cpfDescription/eac:identity/@identityType

    $this->resource->corporateBodyIdentifiers = $fd->find('eac:cpfDescription/eac:identity/eac:entityId')->text();

    $this->entityType = $fd->find('eac:cpfDescription/eac:identity/eac:entityType')->text();

    // TODO <nameEntryParallel/>, <useDates/>
    $this->resource->authorizedFormOfName = $fd->find('eac:cpfDescription/eac:identity/eac:nameEntry[eac:authorizedForm]/eac:part')->text();

    foreach ($fd->find('eac:cpfDescription/eac:identity/eac:nameEntry[not(eac:authorizedForm) and not(@localType="standardized")]') as $node)
    {
      $item = new QubitOtherName;
      $item->name = $fd->spawn()->add($node)->find('eac:part')->text();
      $item->typeId = QubitTerm::OTHER_FORM_OF_NAME_ID;

      $this->resource->otherNames[] = $item;
    }

    foreach ($fd->find('eac:cpfDescription/eac:identity/eac:nameEntryParallel') as $node)
    {
      $item = new QubitOtherName;
      $item->typeId = QubitTerm::PARALLEL_FORM_OF_NAME_ID;

      foreach ($fd->spawn()->add($node)->find('eac:nameEntry[@xml:lang]') as $node2)
      {
        $item->setName($fd->spawn()->add($node2)->find('eac:part')->text(), array('culture' => $this->from6392($node2->getAttribute('xml:lang'))));
      }

      $this->resource->otherNames[] = $item;
    }

    foreach ($fd->find('eac:cpfDescription/eac:identity/eac:nameEntry[@localType="standardized"]') as $node)
    {
      $item = new QubitOtherName;
      $item->name = $fd->spawn()->add($node)->find('eac:part')->text();
      $item->typeId = QubitTerm::STANDARDIZED_FORM_OF_NAME_ID;

      $this->resource->otherNames[] = $item;
    }
    //$fd->find('eac:cpfDescription/eac:identity/eac:nameEntry/eac:authorizedForm');
    //$fd->find('eac:cpfDescription/eac:identity/eac:nameEntry/eac:alternativeForm');
    //$fd->find('eac:cpfDescription/eac:identity/eac:nameEntry/eac:preferredForm');

    // TODO eac:cpfDescription/eac:identity/eac:descriptiveNote

    $this->existDates = $fd->find('eac:cpfDescription/eac:description/eac:existDates');

    // TODO <address/>, <addressLine/>, <date/>, <dateRange/>, <dateSet/>,
    // <descriptiveNote/>, <placeRole/>, <term/>, @accuracy, @altitude,
    // @countryCode, @latitude, @longitude, @vocabularySource
    $this->resource->places = $fd->find('eac:cpfDescription/eac:description/eac:place/eac:placeEntry|eac:cpfDescription/eac:description/eac:places/eac:place/eac:placeEntry')->text();

    // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>,
    // <placeEntry/>, <term/>
    //$fd->find('eac:cpfDescription/eac:description/eac:localDescription');
    //$fd->find('eac:cpfDescription/eac:description/eac:localDescriptions');

    // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>,
    // <placeEntry/>
    $this->resource->legalStatus = $fd->find('eac:cpfDescription/eac:description/eac:legalStatus/eac:term|eac:cpfDescription/eac:description/eac:legalStatuses/eac:legalStatus/eac:term')->text();

    // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>,
    // <placeEntry/>
    $this->resource->functions = $fd->find('eac:cpfDescription/eac:description/eac:function/eac:term|eac:cpfDescription/eac:description/eac:functions/eac:function/eac:term|eac:cpfDescription/eac:description/eac:occupation/eac:term|eac:cpfDescription/eac:description/eac:occupations/eac:occupation/eac:term')->text();

    //$fd->find('eac:cpfDescription/eac:description/eac:languageUsed');

    // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>,
    // <placeEntry/>
    $this->resource->mandates = $fd->find('eac:cpfDescription/eac:description/eac:mandate/eac:term|eac:cpfDescription/eac:description/eac:mandates/eac:mandate/eac:term')->text();

    $this->structureOrGenealogy = $fd->find('eac:cpfDescription/eac:description/eac:structureOrGenealogy');

    $this->generalContext = $fd->find('eac:cpfDescription/eac:description/eac:generalContext');

    // TODO <abstract/>, <chronList/>
    $this->biogHist = $fd->find('eac:cpfDescription/eac:description/eac:biogHist');

    // TODO @lastDateTimeVerified, <date/>, <dateRange/>, <dateSet/>,
    // <descriptiveNote/>, <placeEntry/>
    foreach ($fd->find('eac:cpfDescription/eac:relations/eac:cpfRelation') as $node)
    {
      $url = preg_replace('/^(?:[^:]+:\/\/[^\/]+)?'.preg_quote(sfContext::getInstance()->request->getPathInfoPrefix(), '/').'/', null, $node->getAttributeNS('http://www.w3.org/1999/xlink', 'href'), -1, $count);

      // @href is one of our resources
      if ($node->hasAttributeNS('http://www.w3.org/1999/xlink', 'href') && 0 < $count)
      {
        $params = sfContext::getInstance()->routing->parse($url);
        $item = $params['_sf_route']->resource;
      }
      else
      {
        $item = new QubitActor;
        $item->authorizedFormOfName = $fd->spawn()->add($node)->find('eac:relationEntry')->text();

        // TODO Cascade save through QubitEvent
        $item->save();
      }

      $relation = new QubitRelation;
      $relation->object = $item;
      $relation->typeId = self::fromCpfRelationType($node->getAttribute('cpfRelationType'));

      if (0 < count($date = self::parseDates($node)))
      {
        $relation->startDate = $date[0][0];
        $relation->endDate = $date[count($date) - 1][1];
      }

      // Multiple, non-contiguous dates
      if (1 < count($date))
      {
        foreach ($date as $key => $value)
        {
          $date[$key] = Qubit::renderDate($value[0]).' - '.Qubit::renderDate($value[1]);
        }

        $note = new QubitNote;
        $note->typeId = QubitTerm::RELATION_NOTE_DATE_ID;
        $note->scope = 'QubitRelation';
        $note->content = implode(', ', $date);

        $relation->notes[] = $note;
      }

      $relation->description = trim($fd->spawn()->add($node)->find('eac:descriptiveNote')->text());

      $this->resource->relationsRelatedBysubjectId[] = $relation;
    }

    $this->itemsSubjectOf = array();
    // TODO @lastDateTimeVerified, <date/>, <dateRange/>, <dateSet/>,
    // <descriptiveNote/>, <placeEntry/>
    foreach ($fd->find('eac:cpfDescription/eac:relations/eac:resourceRelation') as $node)
    {
      $url = preg_replace('/^(?:[^:]+:\/\/[^\/]+)?'.preg_quote(sfContext::getInstance()->request->getPathInfoPrefix(), '/').'/', null, $node->getAttributeNS('http://www.w3.org/1999/xlink', 'href'), -1, $count);

      // @href is one of our resources
      if ($node->hasAttributeNS('http://www.w3.org/1999/xlink', 'href') && 0 < $count)
      {
        $params = sfContext::getInstance()->routing->parse($url);
        $item = $params['_sf_route']->resource;
      }
      else
      {
        $item = new QubitInformationObject;
        $item->parentId = QubitInformationObject::ROOT_ID;
        $item->title = $fd->spawn()->add($node)->find('eac:relationEntry')->text();

        // TODO Cascade save through QubitEvent
        $item->save();
      }

      if($node->getAttribute('resourceRelationType') == "subjectOf")
      {
         $this->itemsSubjectOf[] = $item;
      }
      else
      {
        $event = new QubitEvent;
        $event->informationObject = $item;
        $event->typeId = self::fromResourceRelationType($node->getAttribute('resourceRelationType'), $node->getAttribute('xlink:role'));

        if (0 < count($date = self::parseDates($node)))
        {
          $event->startDate = $date[0][0];
          $event->endDate = $date[count($date) - 1][1];
        }

        // Multiple, non-contiguous dates
        if (1 < count($date))
        {
          foreach ($date as $key => $value)
          {
            $date[$key] = Qubit::renderDate($value[0]).' - '.Qubit::renderDate($value[1]);
          }

          $event->date = implode(', ', $date);
        }

        $this->resource->events[] = $event;
      }
    }

    // TODO <date/>, <dateRange/>, <dateSet/>, <descriptiveNote/>,
    // <placeEntry/>, @lastDateTimeVerified
    foreach ($fd->find('eac:cpfDescription/eac:relations/eac:functionRelation') as $node)
    {
      $url = preg_replace('/^(?:[^:]+:\/\/[^\/]+)?'.preg_quote(sfContext::getInstance()->request->getPathInfoPrefix(), '/').'/', null, $node->getAttributeNS('http://www.w3.org/1999/xlink', 'href'), -1, $count);

      // @href is one of our resources
      if ($node->hasAttributeNS('http://www.w3.org/1999/xlink', 'href') && 0 < $count)
      {
        $params = sfContext::getInstance()->routing->parse($url);
        $item = $params['_sf_route']->resource;
      }
      else
      {
        $item = new QubitFunction;
        $item->authorizedFormOfName = $fd->spawn()->add($node)->find('eac:relationEntry')->text();

        // TODO Cascade save through QubitEvent
        $item->save();
      }

      $relation = new QubitRelation;
      $relation->subject = $item;

      // TODO Set $relation->type by mapping to controlled vocabulary

      $this->resource->relationsRelatedByobjectId[] = $relation;
    }

    // TODO <alternativeSet/>

    return $this;
  }

  public static function from6392($code)
  {
    if (isset(self::$from6392[$code]))
    {
      return self::$from6392[$code];
    }

    return $code;
  }

  public static function to6392($code)
  {
    static $to6392;
    if (!isset($to6392))
    {
      $to6392 = array_flip(self::$from6392);
    }

    if (isset($to6392[$code]))
    {
      return $to6392[$code];
    }

    return $code;
  }

  public static function fromCpfRelationType($value)
  {
    switch ($value)
    {
      case 'associative':

        return QubitTerm::ASSOCIATIVE_RELATION_ID;

      case 'family':

        return QubitTerm::FAMILY_RELATION_ID;

      case 'hierarchical':
      case 'hierarchical-child':
      case 'hierarchical-parent':

        return QubitTerm::HIERARCHICAL_RELATION_ID;

      case 'identity':

        return;

      case 'temporal':
      case 'temporal-earlier':
      case 'temporal-later':

        return QubitTerm::TEMPORAL_RELATION_ID;
    }
  }

  public static function toCpfRelationType($value)
  {
    switch ($value)
    {
      case QubitTerm::ASSOCIATIVE_RELATION_ID:

        return 'associative';

      case QubitTerm::FAMILY_RELATION_ID;

        return 'family';

      case QubitTerm::HIERARCHICAL_RELATION_ID:

        return 'hierarchical';

      case QubitTerm::TEMPORAL_RELATION_ID:

        return 'temporal';
    }
  }

  protected static function fromDiscursiveSet($value)
  {
    $value->namespaces(array('eac' => 'urn:isbn:1-931666-33-4'))
      ->find('eac:list/eac:item')
      ->replaceWith(function ($node)
        {
          return '* '.$node->textContent;
        });

    return $value->text();
  }

  // See render_value()
  protected static function toDiscursiveSet($value)
  {
    // Convert XML entities
    $value = esc_specialchars($value);

    // Simple lists
    $value = preg_replace('/(?:^\*.*\r?\n)*(?:^\*.*)/m', "<list>\n$0\n</list>", $value);
    $value = preg_replace('/(?:^-.*\r?\n)*(?:^-.*)/m', "<list>\n$0\n</list>", $value);
    $value = preg_replace('/^(?:\*|-)\s*(.*)/m', '<item>$1</item>', $value);

    $value = '<p>'.preg_replace('/(?:\r?\n){2,}/', "</p>\n<p>", $value).'</p>';

    return $value;
  }

  public function fromResourceRelationType($resourceRelationType, $xlinkRole)
  {
    switch ($resourceRelationType)
    {
      case 'creatorOf':

        return QubitTerm::CREATION_ID;

      case 'other':

        if (!isset($this->eventTypes))
        {
          $this->eventTypes = QubitTaxonomy::getTermsById(QubitTaxonomy::EVENT_TYPE_ID);
        }

        if (strlen($xlinkRole) > 0)
        {
          foreach ($this->eventTypes as $item)
          {
            if($item->__toString() == $xlinkRole)
            {
              return $item->id;
            }
          }

          $term = new QubitTerm;
          $term->taxonomyId = QubitTaxonomy::EVENT_TYPE_ID;
          $term->parentId = QubitTerm::ROOT_ID;
          $term->name = $xlinkRole;
          $term->save();

          return $term->id;
        }
        else
        {
          $term = new QubitTerm;
          $term->taxonomyId = QubitTaxonomy::EVENT_TYPE_ID;
          $term->parentId = QubitTerm::ROOT_ID;
          $term->name = $resourceRelationType;
          $term->save();

          return $term->id;
        }

      case 'subjectOf':

        return;
    }
  }

  public static function toResourceRelationTypeAndXlinkRole($type)
  {
    switch ($type->id)
    {
      case QubitTerm::CREATION_ID:

        return 'resourceRelationType="creatorOf"';

      default:

        return 'resourceRelationType="other" xlink:role="'.$type.'"';
    }
  }

  public function parseDates($node)
  {
    $dates = array();
    $fd = FluentDOM($node)
      ->namespaces(array('eac' => 'urn:isbn:1-931666-33-4'));

    if (0 < $fd->find('./eac:dateSet/eac:dateRange')->length)
    {
      foreach($fd->find('./eac:dateSet/eac:dateRange') as $node)
      {
        $dates[] = sfEacPlugin::parseDateRange($node);
      }
    }
    else if ($fd->find('./eac:dateRange')->length)
    {
      foreach($fd->find('./eac:dateRange') as $node)
      {
        $dates[] = sfEacPlugin::parseDateRange($node);
      }
    }
    else if (0 < $fd->find('./eac:date')->length)
    {
      $dates[] = array($fd->find('eac:date')->attr('standardDate'), null);
    }

    return $dates;
  }

  public static function parseDateRange($node)
  {
    $fd = FluentDOM($node)
      ->namespaces(array('eac' => 'urn:isbn:1-931666-33-4'));

    $range = array($fd->find('eac:fromDate')->attr('standardDate'), $fd->find('eac:toDate')->attr('standardDate'));

    return $range;
  }

  public static function renderDates($item)
  {
    $dates = null;

    if (isset($item->startDate))
    {
      $startDate = Qubit::renderDate($item->startDate);

      if (isset($item->endDate))
      {
        $endDate = Qubit::renderDate($item->endDate);
        $dates = <<<str
                <dateRange>
                  <fromDate standardDate="$startDate">$startDate</fromDate>
                  <toDate standardDate="$endDate">$endDate</toDate>
                </dateRange>
str;
      }
      else
      {
        $dates = <<<str
                <date standardDate="$startDate">$startDate</date>
str;
      }
    }

    return $dates;
  }
}
