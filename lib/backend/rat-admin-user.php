<?php

class SGI_RAT_User
{

	private $taxonomies;

	private $registered_taxonomies;

	public function __construct()
	{

		$this->registered_taxonomies = sgi_rat_get_restrictable_taxonomies();

		if (current_user_can('manage_options')) :

			add_action('show_user_profile', [&$this, 'display_fields']);
			add_action('edit_user_profile', [&$this, 'display_fields']);

			add_action('personal_options_update', [&$this, 'save_fields']);
			add_action('edit_user_profile_update', [&$this, 'save_fields']);

			add_action('admin_enqueue_scripts', [&$this, 'add_scripts'],20);

		endif;

	}

	public function add_scripts()
	{

		global $pagenow;

		if ( ($pagenow != 'profile.php') && ($pagenow != 'user-edit.php') )
			return;

		wp_register_style( 'sgi-rat-vendor', plugins_url('assets/css/sgi-rat-vendor.min.css',SGI_RAT_BASENAME), null, SGI_RAT_VERSION );
		wp_register_style( 'sgi-rat', plugins_url('assets/css/sgi-rat.min.css',SGI_RAT_BASENAME), null, SGI_RAT_VERSION );

		wp_register_script( 'sgi-rat-vendor-js', plugins_url( "assets/js/sgi-rat-vendor.min.js", SGI_RAT_BASENAME ), array('jquery'), SGI_RAT_VERSION, true);
		wp_register_script( 'sgi-rat-js', plugins_url( "assets/js/sgi-rat.min.js", SGI_RAT_BASENAME ), array('jquery'), SGI_RAT_VERSION, true);

		wp_enqueue_style('sgi-rat-vendor');
		wp_enqueue_style('sgi-rat');

		wp_enqueue_script('sgi-rat-vendor-js');
		wp_enqueue_script('sgi-rat-js');


	}

	private function select_box($taxonomy_name, $user_id)
	{

		$html = '';

		$html .= sprintf(
			'<select name="sgi_rat[%s][]" class="sgi-rat-select2" data-tax="%s" multiple style="width:500px;">',
			$taxonomy_name,
			$taxonomy_name
		);

		if (is_array($this->taxonomies)) :

			$parsed = [];

			foreach($this->taxonomies[$taxonomy_name] as $tax_term_id) :

				$tax_term = get_term_by('term_id', $tax_term_id, $taxonomy_name);

				if (function_exists('icl_get_languages') && defined('ICL_LANGUAGE_CODE')) :

					$orig_term_id = intval(icl_object_id($tax_term_id, $taxonomy_name, false, ICL_LANGUAGE_CODE));

				else :

					$orig_term_id = $tax_term_id;

				endif;

				if (in_array($orig_term_id, $parsed))
					continue;

				$parsed[] = $orig_term_id;

				$html .= sprintf(
					'<option value="%s" selected>%s</option>',
					$tax_term_id,
					$tax_term->name
				);

			endforeach;

		endif;

		$html .= '</select>';

		return $html;
		
	}

	public function display_fields($user)
	{

		$this->taxonomies = get_the_author_meta('sgi_rat_tax', $user->ID);

		printf(
			'<h3>%s</h3>',
			__('Restrict Taxonomies','restrict-user-taxonomies')
		);

		echo '<table class="form-table">';

		foreach ($this->registered_taxonomies as $tax_name) :

			$taxonomy = get_taxonomy($tax_name);

			printf(
				'<tr>
					<th>
						<label for="%s">%s</label>
					</th>
					<td>
						%s
					</td>
				</tr>',
				$tax_name,
				$taxonomy->label,
				$this->select_box($tax_name, $user->ID)
			);
			

		endforeach;

		echo '</table>';

	}

	public function save_fields($user_id)
	{

		$taxonomies = $_POST['sgi_rat'];

		$save = [];

		foreach ($taxonomies as $taxonomy_name => $terms) :

			$tmp = [];

			foreach ($terms as $term) :

				$tmp[] = intval($term);

			endforeach;

			$save[$taxonomy_name] = $tmp;

		endforeach;

		update_usermeta($user_id, 'sgi_rat_tax', $save);
	}

}