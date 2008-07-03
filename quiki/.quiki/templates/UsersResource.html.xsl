<?xml version="1.0" ?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
  <xsl:import href="Quiki.html.xsl" />
  
  <!-- page title -->
  <xsl:param name="title" select="'Users'" />
  
  <xsl:template match="/users">
    <h2>Users</h2>
    <ul id="users">
      <xsl:for-each select="user">
        <li><a href="{name}"><strong class="user-name"><xsl:value-of select="name" /></strong></a></li>
      </xsl:for-each>
    </ul>
  </xsl:template>
    
  <xsl:template match="/registration">
    <h2>Register</h2>
    <form id="register" action="?register" method="post">
      <xsl:if test="error">
	<h3>Error</h3>
        <p><xsl:value-of select="error" /></p>
      </xsl:if>
	      
      <dl class="form">
  	<dt class="name">Username:</dt>
	  <dd class="name">
 	    <input type="text" name="name" maxlength="255" value="{name}" /><xsl:text> </xsl:text>
	    <em>Enter a user ID with up to 255 alphanumeric characters.</em>
	  </dd>
	<dt class="email">E-mail:</dt>
  	  <dd class="email">
            <input type="text" name="email" maxlength="255" value="{email}" /><xsl:text> </xsl:text>
            <em>Enter a valid e-mail address. This will be used to validate your account.</em>
          </dd>
      </dl>
      <input type="submit" value="Register" />
    </form>
  </xsl:template>
    
  <xsl:template match="/registration[@authorized='false']" priority="2">    
    <h2>Registration Unauthorized</h2>
    <p>You may not register an account while you are still logged in.</p>
  </xsl:template>

  <xsl:template match="/registration[@success='true']" priority="2">
    <h2>Registration Complete</h2>
    <p>Registration for the user <strong class="user-name"><xsl:value-of select="name" /></strong> has succeeded. An email has been sent to <strong><xsl:value-of select="email" /></strong> with your information and temporary password.</p>
  </xsl:template>
</xsl:stylesheet>