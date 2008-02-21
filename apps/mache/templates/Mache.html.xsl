<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" version="4.01" indent="yes" encoding="UTF-8"
   doctype-system="http://www.w3.org/TR/html4/strict.dtd"
   doctype-public="-//W3C//DTD HTML 4.01//EN" />
  
  <!-- parameters -->
  <xsl:param name="path" select="''" />

  <xsl:template match="/">
    <html lang="en-US">
      <head>
        <title>Mache</title>
        <link rel="stylesheet" type="text/css" href="/styles/mache.css" media="screen" />
      </head>

      <body>
        <div id="header">
          <h1><a href="/">Mache</a></h1>
          <ul id="nav">
            <li><a href="/strips/">Strips</a></li>
          </ul>
        </div>
        
        <hr />

        <div id="body">
          <xsl:apply-templates />
        </div>

        <hr />

        <p id="footer">Mache is Copyright 2008 tim-ryan.</p>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>