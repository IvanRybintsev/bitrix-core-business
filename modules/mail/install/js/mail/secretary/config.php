<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/secretary.bundle.css',
	'js' => 'dist/secretary.bundle.js',
	'rel' => [
		'main.core',
	],
	'skip_core' => false,
];