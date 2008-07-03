<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" version="4.01" indent="yes" encoding="UTF-8"
   doctype-system="http://www.w3.org/TR/html4/strict.dtd"
   doctype-public="-//W3C//DTD HTML 4.01//EN" />

  <xsl:template match="/">
    <html lang="en-US">
      <head>
        <title>Quiki Installation</title>
      </head>

      <body>
        <h1>Quiki Installation</h1>
        <p>Welcome to Quiki, the simple wiki for Chowdah. Please take a moment to set up your installation.</p>

        <form method="post" action=".">
          <h2>Database Credentials</h2>
          <dl>
            <dt><label>Host:</label></dt>
              <dd><input type="text" name="host" value="localhost" /></dd>
            <dt><label>User:</label></dt>
              <dd><input type="text" name="user" value="" /></dd>
            <dt><label>Password:</label></dt>
              <dd><input type="password" name="password" value="" /></dd>
            <dt><label>Database Name:</label></dt>
              <dd><input type="text" name="name" value="quiki" /></dd>
          </dl>
          <p><input type="submit" value="Submit" />&#160;<input type="reset" value="Clear" /></p>
        </form>

        <p id="footer">Quiki is Copyright &#169; 2008 Tim Cameron Ryan.</p>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
