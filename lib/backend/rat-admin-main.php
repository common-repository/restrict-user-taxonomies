<?php

class SGI_RAT_Admin
{

	private $opts;

	private $user_id;

	private $allowed_taxonomies;

	public function __construct()
	{

		global $current_user;

		$this->user_id = $current_user->ID;

		$this->opts = sgi_rat_get_default_options();

		$allowed_taxonomies = get_the_author_meta('sgi_rat_tax', $this->user_id);

		/**
		 * Filter the allowed taxonomies for specific user
		 *
		 * @since 1.0.0
		 * @param array $allowed_taxonomies       An array of taxonomy names to INCLUDE
		 */
		$this->allowed_taxonomies = apply_filters("sgi/rat/user_{$this->user_id}/allowed_taxonomies", $allowed_taxonomies);

		if (!current_user_can('manage_options')) :

			add_action('pre_get_posts', [&$this, 'limit_post_display'],150,1);
			add_filter('get_terms_args', [&$this, 'limit_tax_display'], 150, 50);

		endif;

	}

	public function limit_post_display($query)
	{

		global $pagenow;

		if ($pagenow != 'edit.php')
			return;

		$post_type = isset($_GET['post_type']) ? $_GET['post_type'] : false;

		if ($post_type == 'page')
			return;

		if (current_user_can('manage_options'))
			return;

		if ( !$this->allowed_taxonomies || (count($this->allowed_taxonomies) == 0) )
			return;

		if (!$query->is_main_query()) 
			return;

		$first = true;

		$tax_builder = [];

		foreach ($this->allowed_taxonomies as $taxonomy_name => $terms) :

			if (!$first) :

				$tax_builder['relation'] = $this->opts['relation'];

				$first = false;

			endif;

			if (function_exists('icl_get_languages') && defined('ICL_LANGUAGE_CODE')) :

				$terms = $this->get_wpml_ids($taxonomy_name, $terms);

			endif;

			if ($this->opts['taxonomies'][$taxonomy_name]['include_children']) :

				$terms = $this->get_term_children($taxonomy_name, $terms);

			endif;

			$tax_builder[] = [
				'taxonomy' => $taxonomy_name,
				'field'	   => 'term_id',
				'terms'	   => $terms,
				'operator' => 'IN'
			];

		endforeach;

		//echo '<pre>'; var_dump($tax_builder); die;

		$query->set('tax_query', $tax_builder);

	}

	public function limit_tax_display($args, $taxonomies)
	{

		if (count($taxonomies) > 1)
			return $args;

		$current_tax = $taxonomies[0];

		if ( !$this->allowed_taxonomies || (count($this->allowed_taxonomies[$current_tax]) == 0) )
			return $args;

		///var_dump($this->allowed_taxonomies[$current_tax]);

		$terms = $this->allowed_taxonomies[$current_tax];

		if (function_exists('icl_get_languages') && defined('ICL_LANGUAGE_CODE')) :

			$terms = $this->get_wpml_ids($current_tax, $terms);

		endif;

		if ($this->opts['taxonomies'][$current_tax]['include_children']) :

			$terms = $this->get_term_children($current_tax, $terms);

		endif;

		$args['include'] = $terms;

		return $args;

	}

	private function get_term_children($taxonomy_name, $terms)
	{

		$newterms = $terms;

		foreach ($terms as $term) :

			$newterms = array_merge($newterms, get_term_children($term, $taxonomy_name));

		endforeach;

		return $newterms;


	}

	private function get_wpml_ids($taxonomy_name, $terms)
	{

		$newterms = [];

		foreach ($terms as $term_id) :

			$newterms[] = intval(icl_object_id($term_id, $taxonomy_name, false, ICL_LANGUAGE_CODE));

		endforeach;

		$newterms = array_filter(
			$newterms,
			function($value) { return $value !== 0;}
		);

		return array_unique($newterms);

	}

}