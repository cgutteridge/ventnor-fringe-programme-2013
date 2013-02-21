<?xml version="1.0" encoding='utf-8'?>
<xsl:stylesheet version="1.0" 
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema#"
    xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
    xmlns:rdfs="http://www.w3.org/2000/01/rdf-schema#"
    xmlns:xtypes="http://purl.org/xtypes/"
    xmlns:owl="http://www.w3.org/2002/07/owl#"

    xmlns:foaf='http://xmlns.com/foaf/0.1/'
    xmlns:skos="http://www.w3.org/2004/02/skos/core#"
    xmlns:event="http://purl.org/NET/c4dm/event.owl#"
    xmlns:prog="http://purl.org/prog/"
    xmlns:dcterms="http://purl.org/dc/terms/"
    xmlns:geo="http://www.w3.org/2003/01/geo/wgs84_pos#"
    xmlns:tl="http://purl.org/NET/c4dm/timeline.owl#"
    xmlns:spacerel="http://data.ordnancesurvey.co.uk/ontology/spatialrelations/"
    xmlns:rooms="http://vocab.deri.ie/rooms#"

    xmlns:progx="http://purl.org/prog/testing/"

    xmlns:g="http://purl.org/openorg/grinder/ns/"
    xmlns:places="http://purl.org/openorg/grinder/ns/places/"
    xmlns:subjects="http://purl.org/openorg/grinder/ns/subjects/"
>

  <xsl:output method="xml" version="1.0" encoding="UTF-8" indent="yes" omit-xml-declaration="no" />

  <xsl:variable name='base-place-uri' select='/g:grinder-data/g:set[@name="base-place-uri"]' />
  <xsl:variable name='base-event-uri' select='/g:grinder-data/g:set[@name="base-event-uri"]' />
  <xsl:variable name='base-act-uri' select='/g:grinder-data/g:set[@name="base-act-uri"]' />
  <xsl:variable name='base-activity-uri' select='/g:grinder-data/g:set[@name="base-activity-uri"]' />
  <xsl:variable name='base-promotor-uri' select='/g:grinder-data/g:set[@name="base-promotor-uri"]' />
  <xsl:variable name='base-subject-uri' select='/g:grinder-data/g:set[@name="base-subject-uri"]' />

  <xsl:variable name='timezone' select='/g:grinder-data/g:set[@name="timezone"]' />
  <xsl:variable name='master-event-uri' select='/g:grinder-data/g:set[@name="master-event-uri"]' />

  <xsl:template match="/g:grinder-data">
    <rdf:RDF>
      <xsl:comment>TOP</xsl:comment>

<event:Event rdf:about="{$master-event-uri}">
  <event:place>
    <geo:SpatialLocation rdf:about="{$base-place-uri}Ventnor">
      <rdfs:label>Ventnor</rdfs:label>
      <owl:sameAs rdf:resource="http://dbpedia.org/resource/Ventnor"/>
    </geo:SpatialLocation>
  </event:place>

  <rdfs:label>Ventnor Fringe Festival, 2013</rdfs:label>
  <foaf:homepage rdf:resource="http://www.vfringe.co.uk/"/>
  <rdfs:label>Ventnor Fringe 2013</rdfs:label>
  <event:time><tl:Interval>
    <tl:start rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">2012-08-14</tl:start>
    <tl:end rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime">2012-08-17</tl:end>
  </tl:Interval></event:time>
</event:Event>

<prog:Programme rdf:about="{$master-event-uri}#programme">
  <prog:describes rdf:resource="{$master-event-uri}" />
  <xsl:apply-templates select="g:row" />
</prog:Programme>
  <xsl:apply-templates select="places:row" />
  <xsl:apply-templates select="subjects:row" />
    </rdf:RDF>
  </xsl:template>

  <xsl:template match="g:row">
    <prog:has_streamed_event>
      <event:Event rdf:about="{$base-event-uri}{g:venue/@tag}-{g:name/@tag}-{g:date}-{g:start}">
        <rdfs:label><xsl:value-of select="g:name" /></rdfs:label>
        <foaf:page rdf:resource="http://vfringe.totl.net/?activity={g:name/@tag}" />
        <xsl:if test="g:tickets-url/text()">
           <progx:ticketsLink rdf:resource="{g:tickets-url}" />
        </xsl:if>
        <xsl:if test="g:more-info/text()">
           <progx:infoLink rdf:resource="{g:more-info}" />
        </xsl:if>
        <xsl:if test="g:time-note/text()">
           <progx:timeNote><xsl:value-of select="g:time-note" /></progx:timeNote>
        </xsl:if>
        <xsl:if test="g:start/text()">
          <event:time>
            <tl:Interval>
              <tl:start rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime"><xsl:value-of select="g:date" />T<xsl:value-of select="g:start" /><xsl:value-of select="$timezone" /></tl:start>

              <xsl:if test="g:end/text()">
                <tl:end rdf:datatype="http://www.w3.org/2001/XMLSchema#dateTime"><xsl:value-of select="g:date" />T<xsl:value-of select="g:end" /><xsl:value-of select="$timezone" /></tl:end>
              </xsl:if>
            </tl:Interval>
          </event:time>
        </xsl:if>
        <prog:speaker>
          <foaf:Agent rdf:about="{$base-act-uri}{g:name/@tag}" rdfs:label="{g:name}">
            <xsl:if test="g:act-homepage/text()">
              <foaf:homepage rdf:resource="{g:act-homepage}" />
            </xsl:if>
            <xsl:if test="g:act-youtube/text()">
              <progx:youtube rdf:resource="{g:act-youtube}" />
            </xsl:if>
            <xsl:for-each select="g:act-reviews">
              <progx:reviews><xsl:value-of select="." /></progx:reviews>
            </xsl:for-each>
          </foaf:Agent>
        </prog:speaker>
        <xsl:if test="g:html-description">
          <dcterms:description rdf:datatype="http://purl.org/xtypes/Fragment-HTML"><xsl:value-of select="g:html-description" /></dcterms:description>
        </xsl:if>
        <xsl:if test="g:promotor/text()">
          <prog:promoter>
            <foaf:Agent rdf:about="{$base-promotor-uri}{g:promotor/@tag}" rdfs:label="{g:promotor}" />
          </prog:promoter>
        </xsl:if>
     
        <xsl:if test="g:venue/text()">
          <event:place>
            <geo:SpatialLocation rdf:about="{$base-place-uri}{g:venue/@tag}" />
          </event:place>
        </xsl:if>
        <xsl:for-each select="g:type">
          <dcterms:subject><skos:Concept rdf:about="{$base-subject-uri}{.}" /></dcterms:subject>
        </xsl:for-each>
        <prog:realises>
          <prog:Activity rdf:about="{$base-activity-uri}{g:name/@tag}">
            <rdfs:label><xsl:value-of select="g:name" /></rdfs:label>
            <xsl:if test="g:html-description">
              <dcterms:description rdf:datatype="http://purl.org/xtypes/Fragment-HTML"><xsl:value-of select="g:html-description" /></dcterms:description>
            </xsl:if>
            <prog:speaker>
              <foaf:Agent rdf:about="{$base-act-uri}{g:name/@tag}" rdfs:label="{g:name}" />
            </prog:speaker>

            <xsl:if test="g:promotor">
              <prog:promoter>
                <foaf:Agent rdf:about="{$base-promotor-uri}{g:promotor/@tag}" rdfs:label="{g:promotor}" />
              </prog:promoter>
            </xsl:if>
            <xsl:if test="g:price/text()">
              <progx:price><xsl:value-of select="g:price" /></progx:price>
            </xsl:if>
            <xsl:if test="g:conc-price/text()">
              <progx:conc-price><xsl:value-of select="g:conc-price" /></progx:conc-price>
            </xsl:if>
            <xsl:if test="g:full-name/text()">
              <progx:full-name><xsl:value-of select="g:full-name" /></progx:full-name>
            </xsl:if>
            <xsl:for-each select="g:type">
              <dcterms:subject><skos:Concept rdf:about="{$base-subject-uri}{.}" /></dcterms:subject>
            </xsl:for-each>
          </prog:Activity>
        </prog:realises>
      </event:Event>
      
    </prog:has_streamed_event>
    <prog:streamed_by_location rdf:resource="{$base-place-uri}{g:venue/@tag}" />
  </xsl:template>
<!--
        <full-name>String Theatre Presents 'The Red Balloon' (Children's puppet show)</full-name>
        <reviews></reviews>

        <price>2</price>
        <conc-price></conc-price>
        <venue tag='TheBijou'>The Bijou</venue>
        <html-description>&lt;p&gt;London's String Theatre come to the Island for the first time, with a production inspired by Albert Lamorisse's film (of the same name) and tells the story of a small boy's friendship with a balloon, while exploring the poignancy of a child's imagination.&lt;/p&gt;&lt;p&gt;The 40-minute production is performed entirely to specially commissioned music, making it accessible to audiences across the world, and is fresh from a tour of India.&lt;/p&gt;</html-description>
        <promoter>String Theatre</promoter>
        <name tag='StringTheatreSTheRedBalloon'>String Theatre's 'The Red Balloon'</name>
        <date>2012-08-18</date>
        <start>14:00</start>
        <end>14:40</end>

-->

<!--
  <places:row filename='/home/programme/programme.ecs.soton.ac.uk/htdocs/2012/08/vfringe/var/places.csv'>
	<places:name>Ventnor Seafront</places:name>
	<places:html-description></places:html-description>
	<places:latitude>50.59301</places:latitude>
	<places:longitude>-1.205868</places:longitude>
  </places:row>
-->

  <xsl:template match="places:row">
    <geo:SpatialLocation rdf:about="{$base-place-uri}{places:name/@tag}">
      <rdfs:label><xsl:value-of select="places:name" /></rdfs:label>
      <xsl:if test="places:html-description/text()">
        <dcterms:description rdf:datatype="http://purl.org/xtypes/Fragment-HTML">&lt;p&gt;<xsl:value-of select="places:html-description" />&lt;/p&gt;</dcterms:description>
      </xsl:if>
      <foaf:page rdf:resource="http://vfringe.totl.net/location/{places:name}" />
      <xsl:if test="places:latitude/text() and places:longitude/text()">
        <geo:lat rdf:datatype="http://www.w3.org/2001/XMLSchema#float"><xsl:value-of select="places:latitude" /></geo:lat>
        <geo:long rdf:datatype="http://www.w3.org/2001/XMLSchema#float"><xsl:value-of select="places:longitude" /></geo:long>
      </xsl:if>
    </geo:SpatialLocation>
  </xsl:template>
<!--
  <subjects:row filename='/home/programme/programme.ecs.soton.ac.uk/htdocs/2012/08/vfringe/var/subjects.csv'>
	<subjects:id>theatre</subjects:id>
	<subjects:name>Theatre</subjects:name>
  </subjects:row>
-->
  <xsl:template match="subjects:row">
    <skos:Concept rdf:about="{$base-subject-uri}{subjects:id}">
      <rdfs:label><xsl:value-of select="subjects:name" /></rdfs:label>
      <xsl:if test="subjects:description/text()">
        <dcterms:description rdf:datatype="http://purl.org/xtypes/Fragment-HTML">&lt;p&gt;<xsl:value-of select="subjects:description" />&lt;/p&gt;</dcterms:description>
      </xsl:if>
    </skos:Concept>
   
  </xsl:template>


</xsl:stylesheet>
