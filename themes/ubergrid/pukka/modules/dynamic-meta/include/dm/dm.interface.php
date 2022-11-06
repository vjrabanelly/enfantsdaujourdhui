<?php if ( ! defined('PUKKA_VERSION')) exit('No direct script access allowed');

interface DMInterface {    
	public function getName();
	public function getSlug();
	public function getInputHTML($data = '');
	public function getOutputHTML($data);
	public function addStyles();
	public function addScripts();
}