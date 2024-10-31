<?php

class SGI_RAT_Get_Terms
{

	public function __construct()
	{

		add_action('wp_ajax_rat_get_terms', [&$this, 'handle_request']);

	}

	public function handle_request()
	{

		$registered_taxonomies = get_taxonomies();

		$taxonomy = $_REQUEST['tax'];
		$search_string = $_REQUEST['q'];

		if (!in_array($taxonomy, $registered_taxonomies)) :

			echo json_encode([]);

			exit;

		endif;

		$return_array = [];

		$terms = $this->fetch_terms($search_string, $taxonomy);

		foreach ($terms as $term) :

			$return_array[] = [
				'id'   => $term->term_id,
				'text' => $term->name
			];

		endforeach;

		echo json_encode($return_array);

		exit;

	}

	private function fetch_terms($search_string, $taxonomy)
	{

		if (version_compare(WP_VERSION, '4.5', '<')) :

			return get_terms(
				$taxonomy,
				[
					'hide_empty' => false,
					'search'	 => $search_string
				]
			);

		else :

			return get_terms([
				'taxonomy'   => $taxonomy,
				'hide_empty' => false,
				'search'	 => $search_string
			]);


		endif;

	}

}