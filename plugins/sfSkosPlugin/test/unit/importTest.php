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

require_once dirname(__FILE__).'/../../../../test/bootstrap/unit.php';

$t = new lime_test(17, new lime_output_color());

$t->diag('Initializing configuration.');
$configuration = ProjectConfiguration::getApplicationConfiguration('qubit', 'test', true);
sfContext::createInstance($configuration);

// Really small vocabulary in Turtle
$vocabSimple = <<<'EOT'
@prefix skos: <http://www.w3.org/2004/02/skos/core#> .

<http://example.com/foo>
  a skos:Concept ;
  skos:prefLabel "Foo" .

<http://example.com/bar>
  a skos:Concept ;
  skos:related <http://example.com/foo> ;
  skos:prefLabel "Bar ORIGINAL" ;
  skos:prefLabel "Bar ESPAÑOL"@es .
EOT;

$europeUnescoThesaurus = <<<'EOT'
<?xml version="1.0" encoding="utf-8" ?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
         xmlns:skos="http://www.w3.org/2004/02/skos/core#"
         xmlns:isothes="http://purl.org/iso25964/skos-thes#"
         xmlns:owl="http://www.w3.org/2002/07/owl#"
         xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
         xmlns:dc="http://purl.org/dc/terms/">

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept920">
    <skos:prefLabel xml:lang="en">Romania</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Roumanie</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Rumanía</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Румыния</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept13519">
    <skos:prefLabel xml:lang="en">Lithuanian SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS de Lituanie</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Литовская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Lituania</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept898">
    <skos:prefLabel xml:lang="fr">Tchécoslovaquie</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Checoslovaquia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Чехословакия</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Czechoslovakia</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept891">
    <skos:prefLabel xml:lang="es">Azerbaiyán</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Азербайджан</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Azerbaïdjan</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Azerbaijan</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept914">
    <skos:prefLabel xml:lang="es">Malta</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Malta</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Мальта</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Malte</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept917">
    <skos:prefLabel xml:lang="ru">Нидерланды</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Pays-Bas</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Netherlands</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Países Bajos</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept888">
    <skos:prefLabel xml:lang="es">Albania</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Albania</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Albanie</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Албания</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept928">
    <skos:prefLabel xml:lang="ru">Западная Европа</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Europe occidentale</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Western Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Europa Occidental</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept3557">
    <skos:prefLabel xml:lang="ru">Северная Европа</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Northern Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Europe du Nord</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Europa del Norte</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept10293">
    <skos:prefLabel xml:lang="fr">RSS d'Estonie</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Estonia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Estonian SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Эстонская ССР</skos:prefLabel>
  </skos:Concept>

  <isothes:ConceptGroup rdf:about="http://vocabularies.unesco.org/thesaurus/domain7">
    <skos:notation>7</skos:notation>
    <isothes:subGroup rdf:resource="http://vocabularies.unesco.org/thesaurus/mt7.20"/>
    <rdf:type rdf:resource="http://vocabularies.unesco.org/ontology#Domain"/>
    <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Collection"/>
    <skos:prefLabel xml:lang="en">Countries and country groupings</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Pays et ensembles de pay</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Страны и группы стран</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Países y agrupaciones de países</skos:prefLabel>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/mt7.20"/>
  </isothes:ConceptGroup>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept892">
    <skos:prefLabel xml:lang="en">Belarus</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Bélarus</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Belarrús</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Беларусь</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept900">
    <skos:prefLabel xml:lang="fr">Estonie</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Estonia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Estonia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Эстония</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept915">
    <skos:prefLabel xml:lang="ru">Республика Молдова</skos:prefLabel>
    <skos:prefLabel xml:lang="es">República de Moldova</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Moldova R</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Moldova R</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept17058">
    <skos:prefLabel xml:lang="es">Serbia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Serbia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Сербия</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Serbie</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept890">
    <skos:prefLabel xml:lang="ru">Австрия</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Autriche</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Austria</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Austria</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept908">
    <skos:prefLabel xml:lang="es">Hungría</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Hungary</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Hongrie</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Венгрия</skos:prefLabel>
  </skos:Concept>

  <skos:ConceptScheme rdf:about="http://vocabularies.unesco.org/thesaurus">
    <skos:prefLabel xml:lang="en">UNESCO Thesaurus</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Thésaurus de l'UNESCO</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Тезаурус ЮНЕСКО</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Tesauro de la UNESCO</skos:prefLabel>
  </skos:ConceptScheme>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept909">
    <skos:prefLabel xml:lang="en">Ireland</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Ирландия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Irlanda</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Irlande</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept905">
    <skos:prefLabel xml:lang="ru">ФРГ</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Alemania, República Federal</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Germany FR</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Allemagne, république fédérale</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept901">
    <skos:prefLabel xml:lang="es">Europa</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Европа</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept923">
    <skos:prefLabel xml:lang="fr">Espagne</skos:prefLabel>
    <skos:prefLabel xml:lang="es">España</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Spain</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Испания</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept12132">
    <skos:prefLabel xml:lang="en">Iceland</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Islande</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Islandia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Исландия</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept16123">
    <skos:prefLabel xml:lang="ru">Украинская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Ukrainian SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Ucrania</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS d'Ukraine</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept5270">
    <skos:prefLabel xml:lang="es">Rusia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Россия</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Russia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Russie</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept910">
    <skos:prefLabel xml:lang="ru">Италия</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Italie</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Italy</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Italia</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept927">
    <skos:prefLabel xml:lang="ru">СССР</skos:prefLabel>
    <skos:prefLabel xml:lang="es">URSS</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">URSS</skos:prefLabel>
    <skos:prefLabel xml:lang="en">USSR</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept16624">
    <skos:prefLabel xml:lang="fr">SSR de Tadjikistan</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Tajik SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Таджикская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Tayikistán</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept4239">
    <skos:prefLabel xml:lang="en">Bosnia and Herzegovina</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Босния и Герцеговина</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Bosnie-Herzégovine</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Bosnia-Herzegovina</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept893">
    <skos:prefLabel xml:lang="en">Belgium</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Бельгия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Bélgica</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Belgique</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept918">
    <skos:prefLabel xml:lang="fr">Pologne</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Polonia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Польша</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Poland</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept13074">
    <skos:prefLabel xml:lang="es">RSS de Kirguizistán</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Киргизская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Kirghiz SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS kirghize</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept10481">
    <skos:prefLabel xml:lang="fr">Suède</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Швеция</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Suecia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Sweden</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept924">
    <skos:prefLabel xml:lang="ru">Швейцария</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Suiza</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Switzerland</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Suisse</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept8621">
    <skos:prefLabel xml:lang="es">Eslovaquia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Slovaquie</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Slovakia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Словакия</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept902">
    <skos:prefLabel xml:lang="es">Francia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">France</skos:prefLabel>
    <skos:prefLabel xml:lang="en">France</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Франция</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept889">
    <skos:prefLabel xml:lang="ru">Армения</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Armenia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Armenia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Arménie</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept13448">
    <skos:prefLabel xml:lang="ru">Лихтенштейн</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Liechtenstein</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Liechtenstein</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Liechtenstein</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept11934">
    <skos:prefLabel xml:lang="ru">Папский престол</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Saint-Siège</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Santa Sede</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Holy See</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept13268">
    <skos:prefLabel xml:lang="es">RSS de Letonia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS de Lettonie</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Latvian SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Латвийская ССР</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept7343">
    <skos:prefLabel xml:lang="ru">Чешская Республика</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">République tchèque</skos:prefLabel>
    <skos:prefLabel xml:lang="es">República Checa</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Czech Republic</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept17059">
    <skos:prefLabel xml:lang="fr">Monténégro</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Montenegro</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Montenegro</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Черногория</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept7623">
    <skos:prefLabel xml:lang="fr">Groenland</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Гренландия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Groenlandia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Greenland</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept13062">
    <skos:prefLabel xml:lang="ru">Казахская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS de Kazakhstan</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Kazajistán</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Kazakh SSR</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept10480">
    <skos:prefLabel xml:lang="es">Finlandia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Finland</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Финляндия</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Finlande</skos:prefLabel>
  </skos:Concept>

  <owl:Class rdf:about="http://vocabularies.unesco.org/ontology#MicroThesaurus">
    <rdfs:label xml:lang="ru">Микротезаурус</rdfs:label>
    <rdfs:label xml:lang="fr">Micro-Thesaurus</rdfs:label>
    <rdfs:label xml:lang="en">Micro-Thesaurus</rdfs:label>
    <rdfs:label xml:lang="es">Microtesauro</rdfs:label>
  </owl:Class>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept14609">
    <skos:prefLabel xml:lang="ru">Норвегия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Noruega</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Norvège</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Norway</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept2368">
    <skos:prefLabel xml:lang="es">Andorra</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Andorra</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Андорра</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Andorre</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept926">
    <skos:prefLabel xml:lang="es">Ucrania</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Украина</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Ukraine</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Ukraine</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept906">
    <skos:prefLabel xml:lang="es">Gibraltar</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Gibraltar</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Gibraltar</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Гибралтар</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept8623">
    <skos:prefLabel xml:lang="en">The former Yugoslav Republic of Macedonia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Бывшая Республика Македония в составе Югославии</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Ex-république yougoslave de Macédoine</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Ex República yugoslava de Macedonia</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept8622">
    <skos:prefLabel xml:lang="es">Eslovenia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Slovénie</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Slovenia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Словения</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept2886">
    <skos:prefLabel xml:lang="es">RSS de Armenia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS d'Arménie</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Armenian SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Армянская ССР</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept16622">
    <skos:prefLabel xml:lang="fr">SSR d'Ouzbékistan</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Узбекская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Uzbekistán</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Uzbek SSR</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept911">
    <skos:prefLabel xml:lang="ru">Латвия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Letonia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Latvia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Lettonie</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept899">
    <skos:prefLabel xml:lang="fr">Europe orientale</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Восточная Европа</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Eastern Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Europa Oriental</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept919">
    <skos:prefLabel xml:lang="ru">Португалия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Portugal</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Portugal</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Portugal</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept904">
    <skos:prefLabel xml:lang="fr">Allemagne</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Германия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Alemania</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Germany</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept17070">
    <skos:prefLabel xml:lang="en">Faroes</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Iles Féroé</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Islas Feroe</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept4785">
    <skos:prefLabel xml:lang="ru">Грузия</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Georgia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Georgia</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Géorgie</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept894">
    <skos:prefLabel xml:lang="es">Bulgaria</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Bulgaria</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Bulgarie</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Болгария</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept925">
    <skos:prefLabel xml:lang="en">UK</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Royaume-Uni</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Reino Unido</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">СК - Соединенное Королевство</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept897">
    <skos:prefLabel xml:lang="ru">Кипр</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Cyprus</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Chipre</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Chypre</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept4483">
    <skos:prefLabel xml:lang="ru">Белорусская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">RSS de Biélorussie</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Bielorrusia</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Byelorussian SSR</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept907">
    <skos:prefLabel xml:lang="en">Greece</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Grecia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Греция</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Grèce</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept14245">
    <skos:prefLabel xml:lang="en">Moldavian SSR</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">SSR de Moldavie</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Молдавская ССР</skos:prefLabel>
    <skos:prefLabel xml:lang="es">RSS de Moldavia</skos:prefLabel>
  </skos:Concept>

  <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept896">
    <skos:prefLabel xml:lang="en">Croatia</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Хорватия</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Croatie</skos:prefLabel>
    <skos:prefLabel xml:lang="es">Croacia</skos:prefLabel>
  </skos:Concept>

  <isothes:ConceptGroup rdf:about="http://vocabularies.unesco.org/thesaurus/mt7.20">
    <skos:notation>7.20</skos:notation>
    <isothes:superGroup rdf:resource="http://vocabularies.unesco.org/thesaurus/domain7"/>
    <dc:modified rdf:datatype="http://www.w3.org/2001/XMLSchema#date">2006-08-09</dc:modified>
    <rdf:type rdf:resource="http://vocabularies.unesco.org/ontology#MicroThesaurus"/>
    <rdf:type rdf:resource="http://www.w3.org/2004/02/skos/core#Collection"/>
    <skos:inScheme rdf:resource="http://vocabularies.unesco.org/thesaurus"/>
    <skos:prefLabel xml:lang="es">Europa</skos:prefLabel>
    <skos:prefLabel xml:lang="fr">Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="en">Europe</skos:prefLabel>
    <skos:prefLabel xml:lang="ru">Европа</skos:prefLabel>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept920"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept13519"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept898"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept891"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept914"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept917"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept888"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept928"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept3557"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept10293"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept892"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept900"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept915"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept17058"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept890"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept908"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept909"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept905"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept901"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept923"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept12132"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept16123"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept5270"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept910"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept927"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept16624"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept4239"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept893"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept918"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept13074"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept10481"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept924"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept8621"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept902"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept889"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept13448"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept11934"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept13268"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept7343"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept17059"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept7623"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept13062"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept10480"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept14609"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept2368"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept926"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept906"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept8623"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept8622"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept2886"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept16622"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept911"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept899"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept919"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept904"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept17070"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept4785"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept894"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept925"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept897"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept4483"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept907"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept14245"/>
    <skos:member rdf:resource="http://vocabularies.unesco.org/thesaurus/concept896"/>
    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept903">
        <skos:prefLabel xml:lang="es">República Democrática Alemana</skos:prefLabel>
        <skos:prefLabel xml:lang="en">German DR</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">ГДР</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">République démocratique allemande</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept895">
        <skos:prefLabel xml:lang="en">Caucasian States</skos:prefLabel>
        <skos:prefLabel xml:lang="es">Estado caucasiano</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Государства Кавказа</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">État du Caucase</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept3485">
        <skos:prefLabel xml:lang="fr">RSS d'Azerbaïdjan</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Азербайджанская ССР</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Azerbaijan SSR</skos:prefLabel>
        <skos:prefLabel xml:lang="es">RSS de Azerbaiyán</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept16126">
        <skos:prefLabel xml:lang="fr">RSSF de Russie</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Russian SFSR</skos:prefLabel>
        <skos:prefLabel xml:lang="es">RFSS de Rusia</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Российская СФСР</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept913">
        <skos:prefLabel xml:lang="fr">Luxembourg</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Luxembourg</skos:prefLabel>
        <skos:prefLabel xml:lang="es">Luxemburgo</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Люксембург</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept929">
        <skos:prefLabel xml:lang="es">Yugoslavia</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Yugoslavia</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Югославия</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">Yougoslavie</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept921">
        <skos:prefLabel xml:lang="fr">Fédération de Russie</skos:prefLabel>
        <skos:prefLabel xml:lang="es">Federación de Rusia</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Российская Федерация</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Russian Federation</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept7367">
        <skos:prefLabel xml:lang="fr">Danemark</skos:prefLabel>
        <skos:prefLabel xml:lang="es">Dinamarca</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Дания</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Denmark</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept922">
        <skos:prefLabel xml:lang="fr">Serbie-Monténégro</skos:prefLabel>
        <skos:prefLabel xml:lang="es">Serbia y Montenegro</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Serbia and Montenegro</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Сербия и Черногория</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept916">
        <skos:prefLabel xml:lang="ru">Монако</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">Monaco</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Monaco</skos:prefLabel>
        <skos:prefLabel xml:lang="es">Mónaco</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept11467">
        <skos:prefLabel xml:lang="fr">RSS de Géorgie</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Грузинская ССР</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Georgian SSR</skos:prefLabel>
        <skos:prefLabel xml:lang="es">RSS de Georgia</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept16130">
        <skos:prefLabel xml:lang="es">RSS de Turkmenistán</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Turkmen SSR</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Туркменская ССР</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">RSS turkmène</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept912">
        <skos:prefLabel xml:lang="es">Lituania</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Литва</skos:prefLabel>
        <skos:prefLabel xml:lang="en">Lithuania</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">Lituanie</skos:prefLabel>
      </skos:Concept>
    </skos:member>

    <skos:member>
      <skos:Concept rdf:about="http://vocabularies.unesco.org/thesaurus/concept13963">
        <skos:prefLabel xml:lang="es">San Marino</skos:prefLabel>
        <skos:prefLabel xml:lang="en">San Marino</skos:prefLabel>
        <skos:prefLabel xml:lang="ru">Сан-Марино</skos:prefLabel>
        <skos:prefLabel xml:lang="fr">Saint-Marin</skos:prefLabel>
      </skos:Concept>
    </skos:member>

  </isothes:ConceptGroup>

</rdf:RDF>
EOT;

// CSS2 vocabulary in RDF/XML
$vocabCSS2 = <<<'EOT'
<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:doc="http://www.w3.org/2000/10/swap/pim/doc#" xmlns:rec="http://www.w3.org/2001/02pd/rec54#" xmlns:contact="http://www.w3.org/2000/10/swap/pim/contact#" xmlns:glos="http://www.w3.org/2003/03/glossary-project/schema#" xmlns:skos="http://www.w3.org/2004/02/skos/core#">
<rdf:Description rdf:about="">
  <dc:rights xmlns:dc="http://purl.org/dc/elements/1.1/" rdf:resource="http://www.w3.org/Consortium/Legal/2002/copyright-documents-20021231" />
</rdf:Description>
  <rdf:Description rdf:about="http://www.w3.org/TR/REC-CSS2">
    <dc:date>1998-05-12</dc:date>
    <dc:title>Glossary of Cascading Style Sheets, level 2 CSS2 Specification</dc:title>
    <doc:version>http://www.w3.org/TR/1998/REC-CSS2-19980512</doc:version>
  </rdf:Description>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#styleSheet">
    <skos:prefLabel xml:lang="en">style sheet</skos:prefLabel>
    <skos:definition xml:lang="en">A set of statements that specify presentation of a document. Style sheets may have three different origins: author, user, and user agent. The interaction of these sources is described in the section on cascading and inheritance.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#validStyleSheet">
    <skos:prefLabel xml:lang="en">valid style sheet</skos:prefLabel>
    <skos:altLabel xml:lang="ru">Распределение по потокам</skos:altLabel>
    <skos:definition xml:lang="en">The validity of a style sheet depends on the level of CSS used for the style sheet. All valid CSS1 style sheets are valid CSS2 style sheets. However, some changes from CSS1 mean that a few CSS1 style sheets will have slightly different semantics in CSS2. A valid CSS2 style sheet must be written according to the grammar of CSS2. Furthermore, it must contain only at-rules, property names, and property values defined in this specification. An illegal (invalid) at-rule, property name, or property value is one that is not valid.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#sourceDocument">
    <skos:prefLabel xml:lang="en">source document</skos:prefLabel>
    <skos:definition xml:lang="en">The document to which one or more style sheets refer. This is encoded in some language that represents the document as a tree of elements. Each element consists of a name that identifies the type of element, optionally a number of attributes, and a (possibly empty) content.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#documentLanguage">
    <skos:prefLabel xml:lang="en">document language</skos:prefLabel>
    <skos:definition xml:lang="en">The encoding language of the source document (e.g., HTML or an XML application).</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#element">
    <skos:prefLabel xml:lang="en">element</skos:prefLabel>
    <skos:definition xml:lang="en">(An SGML term, see [ISO8879].) The primary syntactic constructs of the document language. Most CSS style sheet rules use the names of these elements (such as "P", "TABLE", and "OL" for HTML) to specify rendering information for them.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#replacedElement">
    <skos:prefLabel xml:lang="en">replaced element</skos:prefLabel>
    <skos:definition xml:lang="en">An element for which the CSS formatter knows only the intrinsic dimensions. In HTML, IMG, INPUT, TEXTAREA, SELECT, and OBJECT elements can be examples of replaced elements. For example, the content of the IMG element is often replaced by the image that the "src" attribute designates. CSS does not define how the intrinsic dimensions are found.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#intrinsicDimensions">
    <skos:prefLabel xml:lang="en">intrinsic dimensions</skos:prefLabel>
    <skos:definition xml:lang="en">The width and height as defined by the element itself, not imposed by the surroundings. In CSS2 it is assumed that all replaced elements -- and only replaced elements -- come with intrinsic dimensions.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#attribute">
    <skos:prefLabel xml:lang="en">attribute</skos:prefLabel>
    <skos:definition xml:lang="en">A value associated with an element, consisting of a name, and an associated (textual) value.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#content">
    <skos:prefLabel xml:lang="en">content</skos:prefLabel>
    <skos:definition xml:lang="en">The content associated with an element in the source document; not all elements have content in which case they are called empty. The content of an element may include text, and it may include a number of sub-elements, in which case the element is called the parent of those sub-elements.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#renderedContent">
    <skos:prefLabel xml:lang="en">rendered content</skos:prefLabel>
    <skos:definition xml:lang="en">The content of an element after the rendering that applies to it according to the relevant style sheets has been applied. The rendered content of a replaced element comes from outside the source document. Rendered content may also be alternate text for an element (e.g., the value of the HTML "alt" attribute), and may include items inserted implicitly or explicitly by the style sheet, such as bullets, numbering, etc.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#documentTree">
    <skos:prefLabel xml:lang="en">document tree</skos:prefLabel>
    <skos:definition xml:lang="en">The tree of elements encoded in the source document. Each element in this tree has exactly one parent, with the exception of the root element, which has none.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#child">
    <skos:prefLabel xml:lang="en">child</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called the child of element B if an only if B is the parent of A.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#descendant">
    <skos:prefLabel xml:lang="en">descendant</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a descendant of an element B, if either (1) A is a child of B, or (2) A is the child of some element C that is a descendant of B.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#ancestor">
    <skos:prefLabel xml:lang="en">ancestor</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called an ancestor of an element B, if and only if B is a descendant of A.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#sibling">
    <skos:prefLabel xml:lang="en">sibling</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a sibling of an element B, if and only if B and A share the same parent element. Element A is a preceding sibling if it comes before B in the document tree. Element B is a following sibling if it comes after B in the document tree.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#precedingElement">
    <skos:prefLabel xml:lang="en">preceding element</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a preceding element of an element B, if and only if (1) A is an ancestor of B or (2) A is a preceding sibling of B.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#followingElement">
    <skos:prefLabel xml:lang="en">following element</skos:prefLabel>
    <skos:definition xml:lang="en">An element A is called a following element of an element B, if and only if B is a preceding element of A.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#author">
    <skos:prefLabel xml:lang="en">author</skos:prefLabel>
    <skos:definition xml:lang="en">An author is a person who writes documents and associated style sheets. An authoring tool generates documents and associated style sheets.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#user">
    <skos:prefLabel xml:lang="en">user</skos:prefLabel>
    <skos:definition xml:lang="en">A user is a person who interacts with a user agent to view, hear, or otherwise use a document and its associated style sheet. The user may provide a personal style sheet that encodes personal preferences.A user agent is any program that interprets a document written in the document language and applies associated style sheets according to the terms of this specification. A user agent may display a document, read it aloud, cause it to be printed, convert it to another format, etc.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
  <skos:Concept xmlns="http://www.w3.org/1999/xhtml" rdf:about="http://www.w3.org/2003/03/glossary-project/data/glossaries/CSS2#userAgentUA">
    <skos:prefLabel xml:lang="en">user agent (UA)</skos:prefLabel>
    <skos:definition xml:lang="en">A user agent is any program that interprets a document written in the document language and applies associated style sheets according to the terms of this specification. A user agent may display a document, read it aloud, cause it to be printed, convert it to another format, etc.</skos:definition>
    <rdfs:isDefinedBy rdf:resource="http://www.w3.org/TR/1998/REC-CSS2-19980512"/>
  </skos:Concept>
</rdf:RDF>
EOT;

function toDataScheme($string)
{
    return 'data://text/plain;base64,'.base64_encode(trim($string));
}

function withTransaction($callback)
{
    try {
        $conn = Propel::getConnection();
        $conn->beginTransaction();

        return call_user_func($callback, $conn);
    } finally {
        $conn->rollBack();
    }
}

withTransaction(function ($conn) use ($t, $vocabCSS2) {
    // Make sure that Russian is not defined as a supported language
    $criteria = new Criteria();
    $criteria->add(QubitSetting::NAME, 'ru');
    $criteria->add(QubitSetting::SCOPE, 'i18n_languages');
    if (null !== $term = QubitTerm::getOne($criteria)) {
        $term->delete();
    }

    $term = new QubitTerm();
    $term->parentId = QubitTerm::ROOT_ID;
    $term->taxonomyId = QubitTaxonomy::SUBJECT_ID;
    $term->save();
    $termId = $term->id;

    $importer = new sfSkosPlugin(QubitTaxonomy::SUBJECT_ID, ['parentId' => $termId]);
    $importer->load(toDataScheme($vocabCSS2));
    $importer->importGraph();

    QubitTerm::clearCache();
    $term = QubitTerm::getById($termId);

    $t->is(
        floor(($term->rgt - $term->lft) / 2),
        count($importer->getGraph()->allOfType('skos:Concept')),
        'Graph concept count and database descendants count using lft/rgt match'
    );

    $t->is(
        count($term->getDescendants()),
        count($importer->getGraph()->allOfType('skos:Concept')),
        'Graph concept count and database descendants match'
    );

    $criteria = new Criteria();
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::SUBJECT_ID);
    $criteria->addJoin(QubitTerm::ID, QubitTermI18n::ID);
    $criteria->add(QubitTermI18n::NAME, 'user agent (UA)');
    $term = QubitTerm::getOne($criteria);

    $t->is(get_class($term), 'QubitTerm', 'skos:Concept is created');
    $t->is($term->getName(['culture' => 'en']), 'user agent (UA)', 'skos:Concept\'s prefLabel matches the term name');

    $t->is($importer->hasErrors(), true, 'sfSkosPlugin has errors');
    $t->is(count($importer->getErrors()), 1, 'sfSkosPlugin has *one* error');
    $errors = $importer->getErrors();
    $t->is($errors[0], 'The following languages are used in the dataset imported but not supported by AtoM: ru', 'There is an error about Russian being not defined in AtoM');
});

withTransaction(function ($conn) use ($t, $vocabSimple) {
    // Count existing subjects that are children of the root term
    $criteria = new Criteria();
    $criteria->add(QubitTerm::TAXONOMY_ID, QubitTaxonomy::SUBJECT_ID);
    $criteria->add(QubitTerm::PARENT_ID, QubitTerm::ROOT_ID);
    $termCount1 = count(QubitTerm::get($criteria));

    // Import graph
    $importer = new sfSkosPlugin(QubitTaxonomy::SUBJECT_ID);
    $importer->load(toDataScheme($vocabSimple));
    $importer->importGraph();

    $graph = $importer->getGraph();
    $conceptCount = count($graph->allOfType('skos:Concept'));
    $t->is($conceptCount, 2, '$vocabSimple has two concepts');

    // Test that there are two extra subjects after importing the new dataset
    $terms = QubitTerm::get($criteria);
    $termCount2 = count($terms);
    $t->is($termCount1 + $conceptCount, $termCount2, 'Subject taxonomy contains the new concepts in the dataset');

    $match = null;
    foreach ($terms as $item) {
        if ('Bar ESPAÑOL' == $item->getName(['culture' => 'es'])) {
            $match = $item;

            break;
        }
    }
    $t->is(get_class($match), 'QubitTerm', 'Translations are properly imported too');
});

withTransaction(function ($conn) use ($t, $vocabSimple) {
    // Create subject parent term
    $parent = new QubitTerm();
    $parent->parentId = QubitTerm::ROOT_ID;
    $parent->taxonomyId = QubitTaxonomy::SUBJECT_ID;
    $parent->sourceCulture = 'eu'; // Basque!
    $parent->setName('proba', ['culture' => 'eu']);
    $parent->save();

    // Import graph
    $importer = new sfSkosPlugin(QubitTaxonomy::SUBJECT_ID, ['parentId' => $parent->id]);
    $importer->load(toDataScheme($vocabSimple));
    $importer->importGraph();

    // Populate parent term again
    QubitTerm::clearCache();
    $parent = QubitTerm::getById($parent->id);

    // Test hierarchy
    $t->is(count($parent->getDescendants()), count($importer->getGraph()->allOfType('skos:Concept')), 'Term container reflects new hierarchy');

    // Test search
    $search = QubitSearch::getInstance();
    $search->flushBatch();

    foreach ($parent->getDescendants() as $key => $item) {
        try {
            $search->index->getIndex('QubitTerm')->getDocument($item->id);
            $t->pass("Term {$key} is indexed");
        } catch (Elastica\Exception\NotFoundException $e) {
            $t->fail("Term {$key} was not indexed");
        }
    }

    $doc = $search->index->getIndex('QubitTerm')->getDocument($parent->id)->getData();
    $t->is($doc['numberOfDescendants'], count($importer->getGraph()->allOfType('skos:Concept')), 'Parent term ES document :numberOfDescendants: field is up to date');
});

/**
 * Test that getRootConcepts() is matching all the concepts.
 *
 * @param mixed $object
 * @param mixed $name
 */
function getPrivateMethod($object, $name)
{
    $class = new ReflectionClass($object);
    $method = $class->getMethod($name);
    $method->setAccessible(true);

    return $method;
}

$testingDataSets = [
    [
        'name' => 'vocabCSS2',
        'data' => $vocabCSS2,
        'totalConcepts' => 20,
    ],
    [
        'name' => 'vocabSimple',
        'data' => $vocabSimple,
        'totalConcepts' => 22,
    ],
    [
        'name' => 'europeUnescoThesaurus',
        'data' => $europeUnescoThesaurus,
        'totalConcepts' => 100,
    ],
];

$importer = new sfSkosPlugin(QubitTaxonomy::PLACE_ID);
$methodGetRootConcepts = getPrivateMethod($importer, 'getRootConcepts');

foreach ($testingDataSets as $item) {
    $data = $item['data'];
    $totalConcepts = $item['totalConcepts'];

    $importer->load(toDataScheme($data));

    $result = $methodGetRootConcepts->invoke($importer);

    $t->is(count($result), $totalConcepts, "Number of concepts found in {$item[name]} equals to {$totalConcepts}");
}
