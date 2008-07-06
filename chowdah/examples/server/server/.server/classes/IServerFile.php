<?php

//==============================================================================
// server file interface
//==============================================================================

interface IServerFile
{
	public function getMetadata($key);
	public function setMetadata($key, $value);
	public function deleteMetadata($key);
}

?>