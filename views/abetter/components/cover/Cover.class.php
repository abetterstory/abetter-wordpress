<?php

namespace App\View\Components;

use \ABetter\Toolkit\Component as BaseComponent;

class CoverComponent extends BaseComponent {

	// --- Variables

	// --- Build

	public function build() {

		$post = $this->post ?? NULL;

		$this->template = _wp_template($post);
		$this->type = (array) _wp_field('cover_type',$post);
		$this->label = _wp_render_field('cover_label',$post);
		$this->headline = _wp_render_field('cover_headline',$post);
		$this->lead = _wp_render_field('cover_lead',$post);
		$this->caption = _wp_render_field('cover_caption',$post);
		$this->image = ($f = _wp_field('cover_image',$post)) ? _relative($f) : "";
		$this->link = _wp_render_field('cover_link',$post);
		$this->target = _wp_field('cover_target',$post);
		$this->url = _wp_field('cover_url',$post);
		$this->video_preview = _wp_field('cover_video_preview',$post);
		$this->video_preview_playrate = _wp_field('cover_video_preview_playrate',$post);
		$this->video_source = _wp_field('cover_video_source',$post);
		$this->video_link = _wp_render_field('cover_video_link',$post);
		$this->style = _wp_field('cover_style',$post);

		// ---

		$this->visible = (!empty($this->type) && !in_array('Hidden',$this->type)) ? TRUE : FALSE;
		$this->url = ($this->target) ? _wp_url($this->target) : $this->url;
		$this->link = (!empty($this->link) && in_array('Link',$this->type)) ? $this->link : '';
		$this->image_background = TRUE;
		$this->image_filter = (preg_match('/filter-([^ "]+)/',$this->style,$filter)) ? "--{$filter[1]}" : "";
		$this->class = '';

		// ---

		$this->video_play = ($this->video_source && in_array('Video',$this->type)) ? $this->video_source : '';
		$this->video_preview = ($this->video_preview && in_array('Video',$this->type)) ? $this->video_preview : '';
		$this->video_link = ($this->video_link) ? $this->video_link : _dictionary('video_play',NULL,'Play video');

		if ($this->video_preview) {
			$this->video_preview_source = preg_replace('/\.[^.]+$/','',$this->video_preview);
			$this->video_preview_image = (preg_match('/(jpg|jpeg|png)/',$this->video_preview,$match)) ? $this->video_preview_source.'.'.$match[1] : NULL;
			$this->video_preview_mp4 = (preg_match('/(mp4|m4v)/',$this->video_preview,$match)) ? $this->video_preview_source.'.'.$match[1] : NULL;
			$this->video_preview_webm = (preg_match('/(webm)/',$this->video_preview,$match)) ? $this->video_preview_source.'.'.$match[1] : NULL;
			$this->video_preview_ogg = (preg_match('/(ogg|ogv)/',$this->video_preview,$match)) ? $this->video_preview_source.'.'.$match[1] : NULL;
			$this->video_preview_playrate = ($this->video_preview_playrate) ? : '0.5';
			$this->image = ($this->video_preview_image) ? : $this->image;
		}

		// ---

		if (!$this->visible && $this->template == 'front') {
			$this->visible = TRUE;
			$this->image = $this->image ? : _pixsum('photo:tech');
			$this->headline = $this->headline ? : "Welcome to "._wp_bloginfo();
			$this->lead = $this->lead ? : _lipsum('normal');
		}

		$this->class = 'parallax';
		$this->image_filter = '--darker';

		// ---

		//$this->visible = FALSE;

	}

}
