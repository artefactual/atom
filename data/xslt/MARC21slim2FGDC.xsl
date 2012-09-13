<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:marc="http://www.loc.gov/MARC21/slim"  xmlns:xsl="http://www.w3.org/1999/XSL/Transform" exclude-result-prefixes="marc">
    <xsl:import href="MARC21slimUtils.xsl"/>
    <xsl:output method="xml" encoding="UTF-8" indent="yes"/>

<xsl:template match="/">
    <metadata>
        <xsl:apply-templates/>
    </metadata>
</xsl:template>

    <xsl:template match="marc:record">
        <idinfo>
            <citation>
                <citeinfo>

                <xsl:if test="marc:datafield[@tag=100]/marc:subfield[@code='a']!=''">
                    <origin><xsl:value-of select="marc:datafield[@tag=100]/marc:subfield[@code='a']" /></origin>
                </xsl:if>

                <xsl:if test="marc:datafield[@tag=110]/marc:subfield[@code='a']!=''">
                    <origin><xsl:value-of select="marc:datafield[@tag=110]/marc:subfield[@code='a']" /></origin>
                </xsl:if>

                <xsl:if test="marc:datafield[@tag=111]/marc:subfield[@code='a']!=''">
                    <origin><xsl:value-of select="marc:datafield[@tag=111]/marc:subfield[@code='a']" /></origin>
                </xsl:if>

                <pubdate><xsl:value-of select="marc:datafield[@tag=260]/marc:subfield[@code='c']" /></pubdate>
                <pubtime><xsl:value-of select="marc:datafield[@tag=260]/marc:subfield[@code='c']" /></pubtime>

                <title><xsl:value-of select="marc:datafield[@tag=245]/marc:subfield[@code='a']" /></title>

                <edition><xsl:value-of select="marc:datafield[@tag=250]/marc:subfield[@code='a']" /></edition>

                <pubinfo>
                    <pubplace><xsl:value-of select="marc:datafield[@tag=260]/marc:subfield[@code='a']" /></pubplace>
                    <publish><xsl:value-of select="marc:datafield[@tag=260]/marc:subfield[@code='b']" /></publish>
                </pubinfo>

                <xsl:for-each select="marc:datafield[@tag=500]">
                    <othercit><xsl:value-of select="marc:subfield[@code='a']" /></othercit>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=856]">
                    <onlink><xsl:value-of select="marc:subfield[@code='u']" /></onlink>
                </xsl:for-each>

                </citeinfo>
            </citation>
            <descript>
                <xsl:for-each select="marc:datafield[@tag=520]">
                    <abstract><xsl:value-of select="marc:subfield[@code='a']" /></abstract>
                </xsl:for-each>
            </descript>
            <timeperd>
            </timeperd>
            <status>
                <update><xsl:value-of select="marc:datafield[@tag=310]/marc:subfield[@code='c']" /></update>

                <xsl:for-each select="marc:datafield[@tag=583]">
                    <progress><xsl:value-of select="marc:subfield[@code='a']" /></progress>
                </xsl:for-each>

            </status>
            <spdom>
                <bounding>
                    <westbc><xsl:value-of select="marc:datafield[@tag=034]/marc:subfield[@code='d']" /></westbc>
                    <eastbc><xsl:value-of select="marc:datafield[@tag=034]/marc:subfield[@code='e']" /></eastbc>
                    <northbc><xsl:value-of select="marc:datafield[@tag=034]/marc:subfield[@code='f']" /></northbc>
                    <southbc><xsl:value-of select="marc:datafield[@tag=034]/marc:subfield[@code='g']" /></southbc>
                </bounding>
                <dsgpoly>
                    <dsgpolyo>
                        <grngpoin>
                            <gringlat><xsl:value-of select="marc:datafield[@tag=034]/marc:subfield[@code='s']" /></gringlat>
                            <gringlon><xsl:value-of select="marc:datafield[@tag=034]/marc:subfield[@code='t']" /></gringlon>
                        </grngpoin>
                    </dsgpolyo>
                </dsgpoly>
            </spdom>
            <keywords>
                <xsl:for-each select="marc:datafield[@tag=650]">
                    <theme>
                        <themekt><xsl:value-of select="marc:subfield[@code='2']" /></themekt>
                        <themekey><xsl:value-of select="marc:subfield[@code='a']" /></themekey>
                    </theme>
                </xsl:for-each>

                <xsl:for-each select="marc:datafield[@tag=651]">
                    <place>
                        <placekt><xsl:value-of select="marc:subfield[@code='2']" /></placekt>
                        <placekey><xsl:value-of select="marc:subfield[@code='a']" /></placekey>
                    </place>
                </xsl:for-each>


            </keywords>

            <xsl:for-each select="marc:datafield[@tag=506]">
                <acconst><xsl:value-of select="marc:subfield[@code='a']" /></acconst>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=540]">
                <useconst><xsl:value-of select="marc:subfield[@code='a']" /></useconst>
            </xsl:for-each>


            <xsl:for-each select="marc:datafield[@tag=856]">
                <browse>
                    <browsen><xsl:value-of select="marc:subfield[@code='f']" /></browsen>
                    <browsed><xsl:value-of select="marc:subfield[@code='z']" /></browsed>
                </browse>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=700]|marc:datafield[@tag=710]|marc:datafield[@tag=711]">
                <datacred><xsl:value-of select="marc:subfield[@code='a']" /></datacred>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=355]">
                <secinfo>
                    <secsys><xsl:value-of select="marc:subfield[@code='e']" /></secsys>
                    <secclass><xsl:value-of select="marc:subfield[@code='a']" /></secclass>
                    <sechandl><xsl:value-of select="marc:subfield[@code='b']" /></sechandl>
                </secinfo>
            </xsl:for-each>

            <xsl:for-each select="marc:datafield[@tag=538]">
                <native><xsl:value-of select="marc:subfield[@code='a']" /></native>
            </xsl:for-each>

            <crossref>
            </crossref>

        </idinfo>

        <dataqual>
            <attracc>
                <attraccr><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='a']" /></attraccr>
                <qattracc>
                    <attraccv><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='b']" /></attraccv>
                    <attracce><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='c']" /></attracce>
                </qattracc>
            </attracc>



            <logic><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='d']" /></logic>
            <complete><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='e']" /></complete>

            <posacc>
                <horizpa><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='f']" /></horizpa>
                <qhorizpa>
                    <horizpav><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='g']" /></horizpav>
                    <horizpae><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='h']" /></horizpae>
                </qhorizpa>

                <vertacc>
                    <vertaccr><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='i']" /></vertaccr>
                    <qvertpa>
                        <vertaccv><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='j']" /></vertaccv>
                        <vertacce><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='k']" /></vertacce>
                    </qvertpa>
                </vertacc>
            </posacc>

            <lineage>

                <srcinfo>
                    <srcscale><xsl:value-of select="marc:datafield[@tag=786]/marc:subfield[@code='m']" /></srcscale>
                    <typesrc><xsl:value-of select="marc:datafield[@tag=786]/marc:subfield[@code='h']" /></typesrc>
                    <srctime>
                        <srccurr><xsl:value-of select="marc:datafield[@tag=786]/marc:subfield[@code='j']" /></srccurr>
                    </srctime>
                    <srccitea><xsl:value-of select="marc:datafield[@tag=786]/marc:subfield[@code='p']" /></srccitea>
                    <srccontr><xsl:value-of select="marc:datafield[@tag=786]/marc:subfield[@code='v']" /></srccontr>
                </srcinfo>

                <procstep>
                    <procdesc><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='a']" /></procdesc>
                    <procdate><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='c']" /></procdate>
                    <proctime><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='c']" /></proctime>
                    <srcprod><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='b']" /></srcprod>

                </procstep>
            </lineage>

            <cloud><xsl:value-of select="marc:datafield[@tag=514]/marc:subfield[@code='m']" /></cloud>


        </dataqual>

        <spdoinfo>
            <indspref><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='i']" /></indspref>
            <direct><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='a']" /></direct>
            <xsl:variable name="object" select="marc:datafield[@tag=352]/marc:subfield[@code='a']" />

            <xsl:if test="contains(translate($object,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'raster')=false">

                <ptvctinf>
                    <sdtsterm>
                        <sdtstype><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='b']" /></sdtstype>
                        <ptvctcnt><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='c']" /></ptvctcnt>
                    </sdtsterm>

                    <vpfterm>
                        <vpflevel><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='g']" /></vpflevel>
                        <vpfinfo><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='b']" /></vpfinfo>
                    </vpfterm>
                </ptvctinf>
            </xsl:if>

            <xsl:if test="contains(translate($object,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'raster')">
                <rastinfo>
                    <rasttype><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='b']" /></rasttype>
                    <rowcount><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='d']" /></rowcount>
                    <colcount><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='e']" /></colcount>
                    <vrtcount><xsl:value-of select="marc:datafield[@tag=352]/marc:subfield[@code='f']" /></vrtcount>
                </rastinfo>
            </xsl:if>
        </spdoinfo>

        <spref>
            <xsl:for-each select="marc:datafield[@tag=342]">
                <!--This is the branch for the horizontal grid system-->
                <xsl:if test="@ind1=0">
                    <horizsys>
                        <geograph>
                            <latres><xsl:value-of select="marc:subfield[@code='c']" /></latres>
                            <longres><xsl:value-of select="marc:subfield[@code='d']" /></longres>
                            <geogunit><xsl:value-of select="marc:subfield[@code='b']" /></geogunit>
                        </geograph>

                        <xsl:if test="@ind2=3">
                            <!--Planar system-->
                            <planar>
                                <mapproj>

                                   <mapprojn><xsl:value-of select="marc:subfield[@code='a']" /></mapprojn>
                                   <xsl:variable name="mapproj" select="marc:subfield[@code='a']" />

                                    <xsl:if test="contains(translate($mapproj,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'albers')">
                                        <albers>
                                            <mapprojp>
                                                <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </albers>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'azimuthal equidistant')">
                                        <azimequi>
                                            <mapprojp>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </azimequi>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'equidistant conic')">
                                        <equicon>
                                            <mapprojp>
                                                <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </equicon>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'equirectangular')">
                                        <equirect>
                                            <mapprojp>
                                                <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </equirect>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'general vertical near')">
                                        <gvnsp>
                                            <mapprojp>
                                                <heightpt><xsl:value-of select="marc:subfield[@code='l']" /></heightpt>
                                                <longpc><xsl:value-of select="marc:subfield[@code='g']" /></longpc>
                                                <latprjc><xsl:value-of select="marc:subfield[@code='h']" /></latprjc>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </gvnsp>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'gnomonic')">
                                        <gnomonic>
                                            <mapprojp>
                                                <longpc><xsl:value-of select="marc:subfield[@code='g']" /></longpc>
                                                <latprjc><xsl:value-of select="marc:subfield[@code='h']" /></latprjc>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </gnomonic>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'lambert azimuthal')">
                                        <lamberta>
                                            <mapprojp>
                                                <longpc><xsl:value-of select="marc:subfield[@code='g']" /></longpc>
                                                <latprjc><xsl:value-of select="marc:subfield[@code='h']" /></latprjc>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </lamberta>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'lambert conformal')">
                                        <lambertc>
                                            <mapprojp>
                                                <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </lambertc>
                                    </xsl:if>

                                   <xsl:if test="translate($mapproj,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')='Mercator'">
                                        <mercator>
                                            <mapprojp>
                                                <xsl:if test="@ind2=1">
                                                    <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                </xsl:if>
                                                <xsl:if test="@ind1=1">
                                                    <sfequat><xsl:value-of select="marc:subfield[@code='k']" /></sfequat>
                                                </xsl:if>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </mercator>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'alaska')">
                                        <modsak>
                                            <mapprojp>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </modsak>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'miller cylindrical')">
                                        <miller>
                                            <mapprojp>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </miller>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'oblique mercator')">
                                        <obqmerc>
                                            <mapprojp>
                                                <xsl:if test="@ind1=1">
                                                    <sfctrlin><xsl:value-of select="marc:subfield[@code='k']" /></sfctrlin>
                                                    <obqlazim>
                                                        <azimangl><xsl:value-of select="marc:subfield[@code='m']" /></azimangl>
                                                        <azimptl><xsl:value-of select="marc:subfield[@code='n']" /></azimptl>
                                                    </obqlazim>
                                                </xsl:if>

                                                <xsl:if test="@ind2=1">
                                                    <!--This should repeat, but I haven't set this up-->
                                                    <obqlpt>
                                                        <obqllat><xsl:value-of select="marc:subfield[@code='e']" /></obqllat>
                                                        <obqllong><xsl:value-of select="marc:subfield[@code='f']" /></obqllong>
                                                    </obqlpt>
                                                    <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                    <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                    <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                                </xsl:if>

                                            </mapprojp>
                                        </obqmerc>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'orthographic')">
                                        <orthogr>
                                            <mapprojp>
                                                <longpc><xsl:value-of select="marc:subfield[@code='g']" /></longpc>
                                                <latprjc><xsl:value-of select="marc:subfield[@code='h']" /></latprjc>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </orthogr>
                                    </xsl:if>


                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'polar stereographic')">
                                        <polarst>
                                            <mapprojp>
                                                <svlong><xsl:value-of select="marc:subfield[@code='n']" /></svlong>
                                                <xsl:if test="@ind1=1">
                                                    <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                </xsl:if>

                                                <xsl:if test="@ind2=1">
                                                    <sfprjorg><xsl:value-of select="marc:subfield[@code='k']" /></sfprjorg>
                                                </xsl:if>

                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </polarst>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'polyconic')">
                                        <polycon>
                                            <mapprojp>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </polycon>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'robinson')">
                                        <robinson>
                                            <mapprojp>
                                                <longpc><xsl:value-of select="marc:subfield[@code='g']" /></longpc>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </robinson>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'sinusoidal')">
                                        <sinusoid>
                                            <mapprojp>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </sinusoid>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'space oblique mercator')">
                                        <spaceobq>
                                            <mapprojp>
                                                <landsat><xsl:value-of select="marc:subfield[@code='o']" /></landsat>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </spaceobq>
                                    </xsl:if>



                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'stereographic')">
                                        <stereo>
                                            <mapprojp>
                                                <longpc><xsl:value-of select="marc:subfield[@code='g']" /></longpc>
                                                <latprjc><xsl:value-of select="marc:subfield[@code='h']" /></latprjc>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </stereo>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'transverse mercator')">
                                        <transmer>
                                            <mapprojp>
                                                <sfctrmer><xsl:value-of select="marc:subfield[@code='k']" /></sfctrmer>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </transmer>
                                    </xsl:if>

                                    <xsl:if test="contains(translate($mapproj, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'van der grinten')">
                                        <vdgrin>
                                            <mapprojp>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                            </mapprojp>
                                        </vdgrin>
                                    </xsl:if>


                                </mapproj>
                            </planar>
                        </xsl:if>
                        <xsl:if test="@ind2=2">
                            <!--Grid Coordinate system-->
                            <gridsys>
                                <gridsysn><xsl:value-of select="marc:subfield[@code='a']" /></gridsysn>
                                <xsl:variable name="gridname" select="marc:subfield[@code='a']" />
                                <xsl:if test="contains(translate($gridname,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'universal transverse mercator')">
                                    <utm>
                                        <utmzone><xsl:value-of select="marc:subfield[@code='p']" /></utmzone>
                                        <mapprojp>
                                                <sfctrmer><xsl:value-of select="marc:subfield[@code='k']" /></sfctrmer>
                                                <longcm><xsl:value-of select="marc:subfield[@code='g']" /></longcm>
                                                <latprjo><xsl:value-of select="marc:subfield[@code='h']" /></latprjo>
                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                        </mapprojp>
                                    </utm>
                                </xsl:if>

                                <xsl:if test="contains(translate($gridname,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'), 'universal polar stereographic')">
                                    <ups>
                                        <upszone><xsl:value-of select="marc:subfield[@code='p']" /></upszone>
                                        <mapprojp>
                                                <svlong><xsl:value-of select="marc:subfield[@code='n']" /></svlong>
                                                <xsl:if test="@ind1=1">
                                                    <stdparll><xsl:value-of select="marc:subfield[@code='e']" /></stdparll>
                                                </xsl:if>

                                                <xsl:if test="@ind2=1">
                                                    <sfprjorg><xsl:value-of select="marc:subfield[@code='k']" /></sfprjorg>
                                                </xsl:if>

                                                <feast><xsl:value-of select="marc:subfield[@code='i']" /></feast>
                                                <fnorth><xsl:value-of select="marc:subfield[@code='j']" /></fnorth>
                                        </mapprojp>
                                    </ups>
                                </xsl:if>

                                <xsl:if test="contains(translate($gridname,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'1927')">
                                    <spcs>
                                        <spcszone>1927</spcszone>
                                    </spcs>
                                </xsl:if>

                                <xsl:if test="contains(translate($gridname, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'1983')">
                                    <spcs>
                                        <spcszone>1983</spcszone>
                                    </spcs>
                                </xsl:if>

                                <xsl:if test="contains(translate($gridname,'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz'),'arc')">
                                    <arcsys>
                                        <arczone><xsl:value-of select="marc:subfield[@code='p']" /></arczone>
                                    </arcsys>
                                </xsl:if>

                                <xsl:if test="translate(contains($gridname,'other'), 'ABCDEFGHIJKLMNOPQRSTUVWXYZ','abcdefghijklmnopqrstuvwxyz')">
                                    <othergrd>
                                    </othergrd>
                                </xsl:if>
                            </gridsys>
                        </xsl:if>

                        <planci>
                            <plance><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='a']" /></plance>
                            <coordrep>
                                <absres><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='c']" /></absres>
                                <ordres><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='d']" /></ordres>
                            </coordrep>
                            <distbrep>
                                <distres><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='e']" /></distres>
                                <bearres><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='f']" /></bearres>
                                <bearunit><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='a']" /></bearunit>
                                <bearrefd><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='n']" /></bearrefd>
                                <bearrefm><xsl:value-of select="marc:datafield[@tag=343]/marc:subfield[@code='i']" /></bearrefm>
                            </distbrep>
                        </planci>


                    </horizsys>
                </xsl:if>
                <xsl:if test="@ind1=1">
                    <vertdef>
                        <altsys>
                            <altdatum><xsl:value-of select="marc:subfield[@code='a']" /></altdatum>
                            <altres><xsl:value-of select="marc:field[@code='t']" /></altres>
                            <altunits><xsl:value-of select="marc:subfield[@code='b']" /></altunits>
                            <altenc><xsl:value-of select="marc:subfield[@code='u']" /></altenc>
                        </altsys>


                    </vertdef>

                </xsl:if>


            </xsl:for-each>

        </spref>


        <eainfo>
            <xsl:for-each select="marc:datafield[@tag=552]">
                <detailed>

                    <enttype>
                        <enttypl><xsl:value-of select="marc:subfield[@code='a']" /></enttypl>
                        <enttypd><xsl:value-of select="marc:subfield[@code='b']" /></enttypd>
                    </enttype>

                    <attr>
                        <attrlabl><xsl:value-of select="marc:subfield[@code='c']" /></attrlabl>
                        <attrdef><xsl:value-of select="marc:subfield[@code='d']" /></attrdef>
                        <attrdomv>
                            <edom>
                                <edomv><xsl:value-of select="marc:subfield[@code='e']" /></edomv>
                                <edomvd><xsl:value-of select="marc:subfield[@code='f']" /></edomvd>
                            </edom>

                            <rdom>
                                <rdommin><xsl:value-of select="substring-before(marc:subfield[@code='g'],'-')" /></rdommin>
                                <rdommax><xsl:value-of select="substring-after(marc:subfield[@code='g'],'-')" /></rdommax>
                            </rdom>
                            <codesetd>
                                <codesets><xsl:value-of select="marc:subfield[@code='h']" /></codesets>
                            </codesetd>
                            <udom><xsl:value-of select="marc:subfield[@code='i']" /></udom>
                        </attrdomv>

                        <attrunit><xsl:value-of select="marc:subfield[@code='j']" /></attrunit>
                        <begdatea><xsl:value-of select="substring-before(marc:subfield[@code='k'],'-')" /></begdatea>
                        <enddatea><xsl:value-of select="substring-after(marc:subfield[@code='k'],'-')" /></enddatea>
                        <attrvai>
                            <attrva><xsl:value-of select="marc:subfield[@code='l']" /></attrva>
                            <attrvae><xsl:value-of select="marc:subfield[@code='m']" /></attrvae>
                        </attrvai>

                        <attrmfrq><xsl:value-of select="marc:subfield[@code='n']" /></attrmfrq>
                    </attr>

                    <overview>
                        <eaover><xsl:value-of select="marc:subfield[@code='o']" /></eaover>
                        <eadetcit><xsl:value-of select="marc:subfield[@code='p']" /></eadetcit>
                    </overview>

                </detailed>
            </xsl:for-each>

        </eainfo>


        <distinfo>
            <xsl:for-each select="marc:datafield[@tag=037]">
                <stdorder>
                    <nondig><xsl:value-of select="marc:subfield[@code='f']" /></nondig>
                    <digform>
                        <digtinfo>
                            <formname><xsl:value-of select="marc:subfield[@code='g']" /></formname>
                            <formspec><xsl:value-of select="marc:subfield[@code='h']" /></formspec>
                            <formcont><xsl:value-of select="marc:subfield[@code='n']" /></formcont>
                        </digtinfo>
                        <digtopt>
                            <onlinopt>
                                 <computer>
                                    <dialinst>
                                        <lowbps><xsl:value-of select="substring-before(marc:datafield[@tag='856']/marc:subfield[@code='j'],'-')" /></lowbps>
                                        <highbps><xsl:value-of select="substring-after(marc:datafield[@tag='856']/marc:subfield[@code='j'],'-')" /></highbps>
                                        <numdata><xsl:value-of select="marc:datafield[@tag='856']/marc:subfield[@code='r']" /></numdata>
                                        <compress><xsl:value-of select="marc:datafield[@tag='856']/marc:subfield[@code='c']" /></compress>
                                        <dialtel><xsl:value-of select="marc:datafield[@tag='856']/marc:subfield[@code='b']" /></dialtel>
                                        <dialfile><xsl:value-of select="marc:datafield[@tag='856']/marc:subfield[@code='f']" /></dialfile>
                                    </dialinst>
                                    <accinstr><xsl:value-of select="marc:datafield[@tag='856']/marc:subfield[@code='i']" /></accinstr>
                                </computer>
                            </onlinopt>
                        </digtopt>
                    </digform>
                    <custom><xsl:value-of select="marc:subfield[@code='c']" /></custom>
                </stdorder>
                <availabl>
                    <timeinfo>
                        <sngdate>
                            <caldate><xsl:value-of select="marc:datafield[@tag=045]/marc:subfield[@code='b']" /></caldate>
                        </sngdate>

                    </timeinfo>
                </availabl>
            </xsl:for-each>
        </distinfo>

        <metainfo>
            <metd><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='c']" /></metd>
            <metrd><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='z']" /></metrd>
            <metc>
                <cntinfo>
                    <cntperp>
                        <cntper><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='p']" /></cntper>
                        <cntorg><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='q']" /></cntorg>
                    </cntperp>
                    <cntaddr>
                        <addrtype><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='i']" /></addrtype>
                        <address><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='a']" /></address>
                        <city><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='b']" /></city>
                        <state><xsl:value-of select="substring-before(marc:datafield[@tag=270]/marc:subfield[@code='c'],',')" /></state>
                        <postal><xsl:value-of select="substring-after(marc:datafield[@tag=270]/marc:subfield[@code='c'],',')" /></postal>
                        <country><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='d']" /></country>
                    </cntaddr>
                    <cntvoice><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='k']" /></cntvoice>
                    <cnttdd><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='h']" /></cnttdd>
                    <cntemail><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='m']" /></cntemail>
                    <hours><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='r']" /></hours>
                    <cntinst><xsl:value-of select="marc:datafield[@tag=270]/marc:subfield[@code='z']" /></cntinst>
                </cntinfo>
            </metc>
            <metstdn><xsl:value-of select="marc:datafield[@tag=583]/marc:subfield[@code='f']" /></metstdn>
            <metac><xsl:value-of select="marc:datafield[@tag=506]/marc:subfield[@code='a']" /></metac>
        </metainfo>



    </xsl:template>
</xsl:stylesheet>
