<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="Quiki.html.xsl" />
  
  <!-- page title -->
  <xsl:param name="title" select="'Login'" />

  <xsl:template match="/login">
    <h2>Login</h2>
    <p>The username or password you entered was incorrect. Please <a href="login">refresh this page</a> and try again.</p>
  </xsl:template>
</xsl:stylesheet>