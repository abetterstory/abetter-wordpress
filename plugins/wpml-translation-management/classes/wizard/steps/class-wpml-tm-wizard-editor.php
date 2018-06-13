<?php

class WPML_TM_Wizard_Translation_Editor_Step extends WPML_Twig_Template_Loader {

	private $model = array(
		'editor_types' => array(
			'ate'     => ICL_TM_TMETHOD_ATE,
			'classic' => ICL_TM_TMETHOD_EDITOR,
			'manual'  => ICL_TM_TMETHOD_MANUAL,
		)
	);
	/**
	 * @var WPML_TM_MCS_ATE
	 */
	private $mscs_ate;

	public function __construct( WPML_TM_MCS_ATE $mcs_ate ) {
		$this->mscs_ate = $mcs_ate;

		parent::__construct( array(
				WPML_TM_PATH . '/templates/wizard',
				$mcs_ate->get_template_path(),
			)
		);
	}

	public function render() {
		$this->add_strings();

		return $this->get_template()->show( $this->model, 'translation-editor-step.twig' );
	}

	public function add_strings() {

		$this->model['strings'] = array(
			'title'        => __( 'Choose Your Translation Editor', 'wpml-translation-management' ),
			'summary'      => __( 'WPML offers two kinds of translation editors for you to choose from. The classic editor is always free (included with WPML) and offers basic editing features. Our Advanced Translation Editor allows your translators to work much faster and more accurately.',
			                      'wpml-translation-management' ),
			'options'      => array(
				'classic' => array('heading' => __( 'WPML’s Classic Translation Editor', 'wpml-translation-management' ),),
				'ate'     => array('heading' => __( 'WPML’s Advanced Translation Editor', 'wpml-translation-management'), ),
			),
			'feature_1'    => __( 'Support for all content types', 'wpml-translation-management' ),
			'feature_2'    => __( 'Spell checker', 'wpml-translation-management' ),
			'feature_3'    => __( 'Translation Memory', 'wpml-translation-management' ),
			'feature_4'    => __( 'Machine Translation', 'wpml-translation-management' ),
			'feature_5'    => __( 'Translator preview', 'wpml-translation-management' ),
			'feature_6'    => __( 'Cost', 'wpml-translation-management' ),
			'classic_cost' => __( 'Included in WPML', 'wpml-translation-management' ),
			'ate_cost_1'   => __( '$8 / month / translator', 'wpml-translation-management' ),
			'ate_cost_2'   => __( 'Free during the beta period', 'wpml-translation-management' ),
			'select'       => __( 'Select', 'wpml-translation-management' ),
			'continue'     => __( 'Continue', 'wpml-translation-management' ),
			'go_back'      => __( 'Back to adding translators', 'wpml-translation-management' ),
			'after_table'  => __( '* You can always switch between the Classic and Advanced translation editors later.', 'wpml-translation-management' ),
			'ate'          => $this->mscs_ate->get_model(),
		);
	}

}