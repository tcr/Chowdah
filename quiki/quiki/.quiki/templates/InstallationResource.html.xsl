<?xml version="1.0" encoding="UTF-8" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:output method="html" version="4.01" indent="yes" encoding="UTF-8"
   doctype-system="http://www.w3.org/TR/html4/strict.dtd"
   doctype-public="-//W3C//DTD HTML 4.01//EN" />

  <xsl:template match="/installation">
    <html lang="en-US">
      <head>
        <title>Quiki Installation</title>
      </head>

      <body>
        <h1>Quiki Installation</h1>
        <p>Welcome to Quiki, the simple wiki for Chowdah. Please take a moment to set up your installation.</p>

        <form method="post" action=".">
          <xsl:if test="@error">
            <div class="error" style="border: 1px solid red; background: #fcc; padding: 0 1em;">
              <p>The following error was found with your submission:</p>
              <blockquote class="exception"><xsl:value-of select="@error" /></blockquote>
              <p>The installation could not be completed. Please verify your information is correct and resubmit.</p>
            </div>
          </xsl:if>
        
          <h2>Database Credentials</h2>
          <dl>
            <dt><label>Host:</label></dt>
              <dd><input type="text" name="db-host" value="{db-host}" /></dd>
            <dt><label>User:</label></dt>
              <dd><input type="text" name="db-user" value="{db-user}" /></dd>
            <dt><label>Password:</label></dt>
              <dd><input type="password" name="db-password" value="{db-password}" /></dd>
            <dt><label>Database Name:</label></dt>
              <dd><input type="text" name="db-name" value="{db-name}" /></dd>
          </dl>
          
          <h2>User Account</h2>
          <dl>
            <dt><label>Username:</label></dt>
              <dd><input type="text" name="account-username" value="{account-username}" /></dd>
            <dt><label>Password:</label></dt>
              <dd><input type="password" name="account-password" value="{account-password}" /></dd>
            <dt><label>E-mail:</label></dt>
              <dd><input type="text" name="account-email" value="{account-email}" /></dd>
          </dl>
          
          <p><input type="submit" value="Submit" />&#160;<input type="reset" value="Clear" /></p>
        </form>

        <p id="footer">Quiki is Copyright &#169; 2008 Tim Cameron Ryan.</p>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>
