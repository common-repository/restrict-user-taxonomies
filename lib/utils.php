<?php

function sgi_rat_get_restrictable_taxonomies()
{

	$excluded_taxonomies = ['nav_menu', 'link_category','post_format', 'language', 'post_translations', 'term_language', 'term_translations'];

	/**
	 * Filter the taxonomies to exclude from display
	 *
	 * @since 1.0.0
	 * @param array $excluded_taxonomies       An array of taxonomy names to exclude
	 */
	$to_exclude = apply_filters('sgi/rat/excluded_taxonomies', $excluded_taxonomies);

	return array_diff(
		get_taxonomies(),
		$to_exclude
	);

}

function sgi_rat_get_default_options()
{

	$registered_taxonomies = sgi_rat_get_restrictable_taxonomies();

	$def_opts = [];

	$def_opts['relation'] = 'AND';

	foreach ($registered_taxonomies as $reg_tax) :

		$def_opts['taxonomies'][$reg_tax]['include_chidren'] = true;

	endforeach;

	/*
	$roles = get_editable_roles();

	foreach ($roles as $role_name => $role_info) :

		$def_opts['roles'][$role_name] = false;

	endforeach;
	*/

	return get_option('sgi_rat_opts', $def_opts);
	
}