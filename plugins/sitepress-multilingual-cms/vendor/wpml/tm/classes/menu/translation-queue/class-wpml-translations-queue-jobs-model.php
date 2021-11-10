<?php

use WPML\Element\API\PostTranslations;
use WPML\LIB\WP\Post;
use WPML\TM\ATE\Review\PreviewLink;
use WPML\TM\Jobs\Utils\ElementLink;
use WPML\FP\Obj;
use WPML\TM\ATE\Review\ReviewStatus;
use WPML\FP\Lst;

/**
 * Created by OnTheGo Systems
 */
class WPML_Translations_Queue_Jobs_Model {

	/** @var  TranslationManagement $tm_instance */
	private $tm_instance;

	/** @var array $translation_jobs */
	private $translation_jobs;

	/** @var WPML_TM_API $tm_api */
	private $tm_api;

	/** @var array $post_types */
	private $post_types;

	private $post_type_names = array();

	/** @var ElementLink $element_link */
	private $element_link;

	/**
	 * WPML_Translations_Queue_Jobs_Model constructor.
	 *
	 * @param SitePress $sitepress
	 * @param TranslationManagement $tm_instance
	 * @param WPML_TM_API $tm_api
	 * @param ElementLink $elemen_link
	 * @param array $translation_jobs
	 */
	public function __construct(
		SitePress $sitepress,
		TranslationManagement $tm_instance,
		WPML_TM_API $tm_api,
		ElementLink $elemen_link,
		array $translation_jobs
	) {
		$this->tm_instance      = $tm_instance;
		$this->tm_api           = $tm_api;
		$this->element_link     = $elemen_link;
		$this->translation_jobs = $translation_jobs;

		$this->post_types = $sitepress->get_translatable_documents( true );
		$this->post_types = apply_filters( 'wpml_get_translatable_types', $this->post_types );
	}

	public function get() {
		$model = array();

		$model['strings'] = array(
			'job_id'    => __( 'Job ID', 'wpml-translation-management' ),
			'title'     => __( 'Title', 'wpml-translation-management' ),
			'type'      => __( 'Type', 'wpml-translation-management' ),
			'language'  => __( 'Language', 'wpml-translation-management' ),
			'status'    => __( 'Translation status', 'wpml-translation-management' ),
			'deadline'  => __( 'Deadline', 'wpml-translation-management' ),
			'check_all' => __( 'Check all', 'wpml-translation-management' ),
			'confirm'   => __( 'Are you sure you want to resign from this job?', 'wpml-translation-management' ),
		);

		$model['jobs'] = array();

		foreach ( $this->translation_jobs as $job ) {

			// Show the job as "In progress" if the job was already translated and
			// the translator then clicks edit and
			// then clicks "Back to list" on ATE side.
			$job->status = (int) $job->status === ICL_TM_WAITING_FOR_TRANSLATOR && (int) $job->translated === 1
				? ICL_TM_IN_PROGRESS :
				$job->status;

			$job->post_title   = apply_filters( 'the_title', $job->post_title, $job->original_doc_id );
			$job->tm_post_link = $this->get_post_link( $job );
			$job->post_type    = $this->get_post_type( $job );
			$job->icon         = apply_filters(
				'wpml_tm_translation_queue_job_icon',
				$this->tm_instance->status2icon_class( $job->status, $job->needs_update, ReviewStatus::doesJobNeedReview( $job ) ),
				$job
			);
			$job->status_text  = $this->get_status_text( $job );
			$job->edit_url     = $this->get_edit_url( $job );
			$job->resignUrl    = $this->get_resign_url( $job );
			$job->viewLink     = ReviewStatus::doesJobNeedReview( $job ) ? PreviewLink::getByJob( $job ) : $this->get_view_translation_link( $job );

			$model['jobs'][] = $job;
		}

		return $model;
	}

	private function get_post_link( $job ) {
		return $this->element_link->getOriginal( $job );
	}

	private function get_view_translation_link( $job ) {
		return $this->element_link->getTranslation( $job );
	}

	private function get_post_type( $job ) {
		if ( ! isset( $this->post_type_names[ $job->original_post_type ] ) ) {
			$type = $job->original_post_type;
			$name = $type;
			switch ( $job->element_type_prefix ) {
				case 'post':
					$type = substr( $type, 5 );
					break;

				case 'package':
					$type = substr( $type, 8 );
					break;

				case 'st-batch':
					$name = __( 'Strings', 'wpml-translation-management' );
					break;
			}

			$this->post_type_names[ $job->original_post_type ] =
				Obj::pathOr( $name, [ $type, 'labels', 'singular_name' ], $this->post_types );;
		}

		return $this->post_type_names[ $job->original_post_type ];
	}

	private function get_status_text( $job ) {
		if ( ReviewStatus::doesJobNeedReview( $job ) ) {
			return $this->get_target_status( $job ) . __( 'Pending review', 'wpml-translation-management' );
		}

		if ( (int) $job->status === ICL_TM_WAITING_FOR_TRANSLATOR && (int) $job->automatic === 1 ) {
			$status = __( 'Waiting for translation', 'wpml-translation-management' );
		} else {
			if (
				Lst::includes( (int) $job->status, [ ICL_TM_WAITING_FOR_TRANSLATOR, ICL_TM_IN_PROGRESS ] ) &&
				(int) Obj::propOr( - 1, 'ate-return-job', $_GET ) === (int) $job->job_id &&
			    Obj::propOr( 'false', 'back', $_GET ) === 'false'
			) {
				$status = __( 'Updating', 'wpml-translation-management' );
			} else {
				$status = $this->tm_api->get_translation_status_label( $job->status );
			}
		}
		if ( $job->needs_update ) {
			$status .= __( ' - (needs update)', 'wpml-translation-management' );
		}

		return $status;
	}

	private function get_target_status( $job ) {
		if ( $job->element_type_prefix === 'post' ) {
			$target = PostTranslations::getInLanguage( $job->original_doc_id, $job->language_code );
			$status = Post::getStatus( $target->element_id );
			$statuses = [
				'draft' => __( 'Draft', 'wpml-translation-management' ) . ' — ',
				'publish' => __( 'Published', 'wpml-translation-management' ) . ' — ',
			];
			return Obj::propOr( '', $status, $statuses);
		} else {
			return '';
		}
	}

	private function get_edit_url( $job ) {
		$edit_url = '';
		if ( $job->original_doc_id ) {
			$translation_queue_page = admin_url( 'admin.php?page='
			                                     . WPML_TM_FOLDER
			                                     . '/menu/translations-queue.php&job_id='
			                                     . $job->job_id );
			$edit_url               = apply_filters( 'icl_job_edit_url', $translation_queue_page, $job->job_id );
		}

		return $edit_url;
	}

	private function get_resign_url( $job ) {
		return admin_url( 'admin.php?page='
		                  . WPML_TM_FOLDER
		                  . '/menu/translations-queue.php&icl_tm_action=save_translation&resign=1&job_id='
		                  . $job->job_id );
	}

}


