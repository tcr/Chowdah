<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" version="4.01" indent="yes" encoding="UTF-8"
   doctype-system="http://www.w3.org/TR/html4/strict.dtd"
   doctype-public="-//W3C//DTD HTML 4.01//EN" />
  
  <!-- parameters -->
  <xsl:param name="path" select="''" />
  <xsl:param name="title" />
  <xsl:param name="user" />

  <xsl:template match="/">
    <html lang="en-US">
      <head>
        <title>Quiki<xsl:if test="$title"> | <xsl:value-of select="$title" /></xsl:if></title>
        <link rel="stylesheet" type="text/css" href="/styles/quiki.css" media="screen" />
        <script src="/scripts/jquery.js" />
        <script src="/scripts/quiki.js" />
      </head>

      <body>
        <div id="header">
          <h1><a href="/">Quiki</a></h1>
          <ul id="nav">
            <li><a href="/pages/?create">Start a Page</a></li>
            <li><a href="/pages/">Pages</a></li>
            <li><a href="/users/">Users</a></li>
          </ul>
          <p id="user">
            <xsl:choose>
              <xsl:when test="$user">Logged in as <a href="/users/{$user}"><xsl:value-of select="$user" /></a>.</xsl:when>
              <xsl:otherwise><a href="/login">Login</a> or <a href="/users/?register">register</a>.</xsl:otherwise>
            </xsl:choose>
          </p>
        </div>
        
        <hr />

        <div id="body">
          <xsl:apply-templates />
        </div>

        <hr />

        <p id="footer">Quiki is Copyright &#169; 2008 Tim Cameron Ryan.</p>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>