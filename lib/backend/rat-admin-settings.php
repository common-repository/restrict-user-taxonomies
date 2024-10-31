<?php

class SGI_RAT_Settings
{
	
	private $version;

	private $opts;

	public function __construct()
	{

		//Version check
		if ($rat_ver = get_option('sgi_rat_ver')) :

			if (version_compare(SGI_RAT_VERSION,$rat_ver,'>')) :

				update_option('sgi_rat_ver', SGI_RAT_VERSION);

			endif;

			$this->version = SGI_RAT_VERSION;

		else :

			$rat_ver = SGI_RAT_VERSION;
			add_option('sgi_rat_ver', $rat_ver, 'no');

		endif;

		$this->opts = sgi_rat_get_default_options();

		add_action('admin_init', [&$this, 'register_settings']);
        add_action('admin_menu', [&$this, 'add_settings_menu']);

        add_filter('plugin_action_links_'.SGI_RAT_BASENAME, [&$this, 'add_settings_link']);

	}

	public function add_settings_link($links)
	{
		
		$links[] = sprintf(
			'<a href="%s">%s</a>',
			admin_url('options-general.php?page=sgi-restrict-user-taxonomies'),
			__('Settings','restrict-user-taxonomies')
		);

		return $links;

	}

	public function add_settings_menu()
	{

		add_submenu_page(
            'options-general.php',
            __('Restrict Taxonomies', 'restrict-user-taxonomies'),
            __('Restrict Taxonomies', 'restrict-user-taxonomies'),
            'manage_options',
            'sgi-restrict-user-taxonomies',
            [&$this, 'settings_callback']
        );

	}

	public function settings_callback()
	{

		printf (
			'<div class="wrap"><h1>%s</h1>',
			__('Restrict User Taxonomies Settings','serbian-latinisation')
		);

        echo '<form method="POST" action="options.php">';

        settings_fields('sgi_rat_settings');

        do_settings_sections('sgi-restrict-user-taxonomies');

        submit_button();

        echo "</form>";

        echo '</div>';

	}

	public function register_settings()
	{

		register_setting(
            'sgi_rat_settings',
            'sgi_rat_opts',
            [&$this, 'sanitize_opts']
        );

        add_settings_section(
            'sgi_rat_relation',
            __('Taxonomy Relation Settings','serbian-latinisation'),
            array(&$this, 'core_section_callback'),
            'sgi-restrict-user-taxonomies'
        );

        add_settings_field(
            'sgi_rat_opts_relation',
            __('Taxonomy Relation', 'serbian-latinisation'),
            [&$this, 'relation_callback'],
            'sgi-restrict-user-taxonomies',
            'sgi_rat_relation',
            $this->opts['relation']
        );

        add_settings_section(
            'sgi_rat_taxonomies',
            __('Taxonomy Settings','serbian-latinisation'),
            [&$this, 'taxonomy_section_callback'],
            'sgi-restrict-user-taxonomies'
        );

        foreach ($this->opts['taxonomies'] as $taxonomy_name => $taxonomy_opts) :

        	$taxonomy = get_taxonomy($taxonomy_name);

        	add_settings_field(
	            "sgi_rat_opts_{$taxonomy_name}",
	            $taxonomy->label,
	            [&$this, 'taxonomy_callback'],
	            'sgi-restrict-user-taxonomies',
	            'sgi_rat_taxonomies',
	            [
	            	'name' => $taxonomy_name,
	            	'hier' => $taxonomy->hierarchical,
	            	'opts' => $taxonomy_opts
	            ]
	        );

        endforeach;

	}

	public function core_section_callback()
	{

		printf(
			'<p>%s</p>',
			__(
				'Taxonomy relation controls logical relationship between multiple restrictable taxonomies.',
				'restrict-user-taxonomies'
			)
		);

	}

	public function relation_callback($relation)
	{

		echo '<select name="sgi_rat_opts[relation]">';

		printf (
			'<option value="AND" %s>AND</option>',
			selected('AND',$relation,false)
		);

		printf (
			'<option value="OR" %s>OR</option>',
			selected('OR',$relation,false)
		);

		echo '</select>';

		printf (
			'<p class="description"><strong>AND</strong> %s<br><strong>OR</strong> %s</p>',
			__(
				'is exclusive - post must have all selected terms in taxonomies.',
				'restrict-user-taxonomies'
			),
			__(
				'is inclusive - post must have any of selected terms in any taxonomy',
				'restrict-user-taxonomies'
			)
		);

	}

	public function taxonomy_section_callback()
	{

		printf(
			'<p>%s</p>',
			__('Settings that control plugin behaviour for hierarchical taxonomies', 'restrict-user-taxonomies')
		);

	}

	public function taxonomy_callback($tax_data)
	{
			
		$helper_text = __('Include term children for selected restrictable terms',' restrict-user-taxonomies');
		$disabled = ($tax_data['hier']) ? '' : 'disabled';

		if ($disabled == 'disabled') :

			$helper_text .= sprintf (
				'<br><strong>%s</strong>',
				__('This option is currently disabled because the taxonomy is not hierarchical')
			);

		endif;

		printf (
			'<label for="sgi_rat_opts[taxonomies][%s]">
				<input type="checkbox" name="sgi_rat_opts[taxonomies][%s][include_chidren]" %s %s> %s
			</label>
			<p class="description">%s</p>',
			$tax_data['name'],
			$tax_data['name'],
			$disabled,
			checked(true, $tax_data['opts']['include_children'], false),
			__('Include children', 'restrict-user-taxonomies'),
			$helper_text
		);




	}

	public function sanitize_opts($opts)
	{
		$saved_opts['relation'] = $opts['relation'];

		foreach ($this->opts['taxonomies'] as $taxonomy_name => $taxonomy_opts) :

			if ( isset($opts['taxonomies'][$taxonomy_name]) ) :

				$saved_opts['taxonomies'][$taxonomy_name]['include_children'] = true;

			else :

				$saved_opts['taxonomies'][$taxonomy_name]['include_children'] = false;

			endif;

		endforeach;

		return $saved_opts;

	}

}