<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="Quiki.html.xsl" />
  
  <!-- page title -->
  <xsl:param name="title" select="concat('User &quot;', /user/name, '&quot;')" />

  <xsl:template match="/user">
    <h2>User Profile</h2>

    <h3><xsl:value-of select="name" /></h3>
    <dl id="user-profile">
      <dt class="name"><strong>Username</strong></dt>
         <dd class="name"><xsl:value-of select="name" /></dd>
    </dl>
  </xsl:template>
</xsl:stylesheet>