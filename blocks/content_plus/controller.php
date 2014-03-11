<?php 
/**
 * @author 		Daniel Mitchell <glockops \at\ gmail.com>
 * @copyright  	Copyright (c) 2012 D. Mitchell.
 * @license    	Creative Commons Attribution-ShareAlike 3.0 Unported
 *				http://creativecommons.org/licenses/by-sa/3.0/
 */
  
defined('C5_EXECUTE') or die(_("Access Denied."));
class ContentPlusBlockController extends BlockController {
	
	protected $btTable = 'btContentPlus';
	protected $btInterfaceWidth = "640";
	protected $btInterfaceHeight = "450";
	
	// Allow full caching
	// DEVNOTE: The cache may need to be cleared or the block resaved if
	// file titles/descriptions are changed.
	protected $btCacheBlockRecord = true;
	protected $btCacheBlockOutput = true;
	protected $btCacheBlockOutputOnPost = true;
	protected $btCacheBlockOutputForRegisteredUsers = true;
	protected $btCacheBlockOutputLifetime = CACHE_LIFETIME;
		
	public function getBlockTypeName() {
		return t("Content+");
	}
	
	public function getBlockTypeDescription() {
		return t("A content block with support for images and pullquotes.");
	}
	
	/**
	 * Add block content to the search index.
	 */
	public function getSearchableContent(){
		return $this->content;
	}
	
	public function on_page_view() {
		$html = Loader::helper('html/v2');
		$this->addHeaderItem($html->css('content.plus.css','content_plus'));
	}
	
	public function edit() {
		$this->includeUIElements();
		$this->set('content',$this->translateFromEditMode($this->content));
		if($this->type == 'image') {
			$this->set('enable_image',true);
			$this->set('imageFID',File::getByID($this->fID));
			$this->set('image_width',$this->width);
			$this->set('image_height',$this->height);
			$this->set('img_style',$this->style);
			switch($this->link_type) {
				case 'page':
					$this->set('link_page',$this->link_value);
					break;
				case 'url':
					$this->set('link_url',$this->link_value);
					break;
			}
		} elseif($this->type == 'pullquote') {
			$this->set('enable_quote',true);
			$this->set('quote_width',$this->width);
			$this->set('quote_style',$this->style);
		}
		
		switch($this->link_type) {
			
		}
		
	}
	
	public function add() {
		$this->includeUIElements();
		
		// Set some default values
		//$this->set('width',300);
		//$this->set('height',200);
		$this->set('img_style','img-border-simple');
		$this->set('quote_style','bq-bubble-square');
	}
	
	public function view() {
		$this->set('content',$this->generateContent());
	}
	
	public function save($args) {
		
		// Type & style overwritten if enable_image or enable_pullquote is checked.
		$args['type'] = 'text';
		$args['style'] = NULL;
		
		// If layout isn't selected use none
		$args['layout'] = (!empty($args['layout'])) ? $args['layout'] : 'none';
		
		// Save Image Options
		if($args['enable_image']) {
			$args['type'] = 'image';
			
			// If both values are zero, use the actual image size
			if($args['img_width'] <= 0 && $args['img_height'] <= 0) {
				$img = File::getByID($args['fID']);
				$args['img_width'] = $img->getAttribute('width');
				$args['img_height'] = $img->getAttribute('height');
			}
			
			// Compute width/height based on any provided value (used when only one value is provided)
			$args['width'] = ($args['img_width'] > 0) ? $args['img_width'] : ($args['img_height']*1.5);
			$args['height'] = ($args['img_height'] > 0) ? $args['img_height'] : ($args['img_width']*1.5);
			
			// Set checkbox values
			// For some reason, concrete5 doesn't save a 0/false value for unchecked boxes
			$args['caption'] = ($args['caption']>0) ? 1 : 0;
			$args['captionCentered'] = ($args['captionCentered']>0) ? 1 : 0;
			$args['link_target'] = ($args['link_target']>0) ? 1 : 0;
			
			// Prevent 'none' being selected for a layout
			$args['layout'] = ($args['layout'] == 'none') ? 'float-left' : $args['layout'];
			
			// Configure Links
			switch($args['link_type']) {
				case 'url':
					$args['link_value'] = $args['link_url'];
					break;
				case 'page':
					$args['link_value'] = intval($args['link_page']);
					break;
				case 'none':
				default:
					$args['link_value'] = NULL;
					break;
			}
			
			// Apply Style
			$args['style'] = $args['img_style'];
			
			// Destroy pullquote data
			$args['author'] = $args['pullquote'] = NULL;
			
		} // Save Quote Options
		  elseif($args['enable_quote']) {
			
			$args['type'] = 'pullquote';
			$args['width'] = (!empty($args['quote_width'])) ? $args['quote_width'] : 300;
			
			// Prevent 'none' being selected for a layout
			$args['layout'] = ($args['layout'] == 'none') ? 'float-left' : $args['layout'];
			
			// Destroy unneeded data
			$args['fID'] = $args['height'] = $args['caption'] = $args['captionCentered'] = $args['link_target'] = NULL;
			$args['link_type'] = 'none';
			$args['link_value'] = NULL;
			
			// Apply Style
			$args['style'] = $args['quote_style'];
			
		} // Save Just Text Options
		  else {
			  
			// Prevent options from being set when an image or pullquote is not present
			$args['layout'] = $args['link_type'] = 'none';
			$args['style'] = NULL;
			
			// Destroy unneeded data
			$args['fID'] = $args['width'] = $args['height'] = $args['caption'] = $args['captionCentered'] = $args['link_target'] = NULL;
			$args['author'] = $args['pullquote'] = $args['link_value'] = NULL;
			  
		}
		
		// Translate TinyMCE content
		$args['content'] = $this->translateTo($args['content']);
		parent::save($args);
		
	}
	
	/**
	 * Loads required assets and variables when in edit or add mode.
	 * Called by edit() and add()
	 */
	private function includeUIElements() {
		
		// Include Javascript and CSS
		$html = Loader::helper("html/v2");
		$this->addHeaderItem($html->javascript("bootstrap-tabs.js","content_plus"));
		//$this->addHeaderItem($html->javascript("bootstrap-dropdown.js","content_plus"));
		$this->addHeaderItem($html->css("content.plus.ui.css","content_plus"));
		$this->addHeaderItem($html->javascript("content.plus.ui.js","content_plus"));
		
		// Include Helpers
		$this->set('form',Loader::helper('form'));
		$this->set('al',Loader::helper('concrete/asset_library'));
		$this->set('pageselect',Loader::helper('form/page_selector'));
		
		// Set Common Variables
		$this->set('link_types',array('none'=>'No Link','page'=>'Page','url'=>'URL'));
		$this->set('link_url',"http://");
		$this->set('layouts',array('none'=>'No Layout','float-left'=>'Float Left','float-right'=>'Float Right','column-left'=>'Column Left','column-right'=>'Column Right','above'=>'Above','below'=>'Below'));
		$this->set('img_styles',array(
			''=>t('No style'),
			'img-border-simple'=>t('Border (Simple)')
		));
		$this->set('quote_styles',array(
			''=>t('No style'),
			'bq-bubble-square'=>t('Quote Bubble (square)'),
			'bq-vertical-dots'=>t('Vertical Dots'),
			'bq-bordered'=>t('Double Border'),
			'bq-allcaps'=>t('All Caps')
		));

	}
	
	/**
	 * GENERATE CONTENT
	 * Outputs a nicely formatted content plus block
	 * Called by view()
	 * @return	str		HTML of Content Plus Block
	 */
	private function generateContent() {
		$content = $this->translateFrom($this->content);
		$plus = $this->generatePlus();
		$html = '<div class="cpb '.$this->layout.'">';
		switch($this->layout) {
			// Content must render before image/pullquote
			case 'below':
				$html .= 
				'<div class="cpb-content">'.
					$content.
				'</div>'.
				$plus;
				break;
			// Need special padding to simulate float
			case 'column-left':
				$html .= 
				$plus.
				'<div class="cpb-content" style="margin-left:'.($this->width+20).'px">'.
					$content.
				'</div>';
				break;
			case 'column-right':
				$html .= 
				$plus.
				'<div class="cpb-content" style="margin-right:'.($this->width+20).'px">'.
					$content.
				'</div>';
				break;
			// Content must render after image/pullquote			
			case 'float-left':
			case 'float-right':
			case 'above':
			case 'none':
			default:
				$html .= 
				$plus.
				'<div class="cpb-content">'.
					$content.
				'</div>';
				break;
		}
		$html .= '</div>';
		return $html;
	}
	/**
	 * GENERATE PLUS
	 * Outputs a nicely formatted plus block
	 * Called by generateContent(), which is called by view()
	 */
	private function generatePlus() {
		
		switch($this->type) {
			// Generate Image Block
			case 'image':

				if($this->fID > 0) {
					$im = Loader::helper('image');
					
					// Set Link
					$anchorEnd = '</a>';
					$target = ($this->link_target) ? 'target="_blank"' : '';
					switch($this->link_type) {
						case 'url':
							// Simple URL Link
							$linkedAsset = (!empty($this->link_value)) ? html_entity_decode($this->link_value) : '#url_link_broken';
							$anchorStart = '<a href="'.$linkedAsset.'" '.$target.'>';
							break;
						case 'page':
							// Link to internal Concrete5 Page
							$nh = Loader::helper('navigation');
							$linkedAsset = (intval($this->link_value)) ? $nh->getLinkToCollection(Page::getByID($this->link_value)) : '#page_link_broken';
							$anchorStart = '<a href="'.$linkedAsset.'" '.$target.'>';
							break;
						case 'none':
						default:
							$anchorStart = $anchorEnd = '';
							break;
					}
					
					// Configure Image (Remove 20px if style is being used)
					$imgWidth = ($this->style == '') ? $this->width : (intval($this->width) - 20);
					$imageID = File::getByID($this->fID);
					if(is_object($imageID)) {
						$image = $im->getThumbnail($imageID,$imgWidth,$this->height);
						$image->alt = $imageID->getTitle();

						// Set Caption
						$center = ($this->captionCentered) ? 'style="text-align:center;"' : '';
						$caption = ($this->caption && $imageID->getDescription()) ? '<div class="cpb-image-caption"'.$center.'>'.html_entity_decode($imageID->getDescription()).'</div>' : '';				
						
						// Generate HTML for Image block
						$html =
						'<div class="cpb-image '.$this->style.'" style="max-width:'.intval($this->width).'px;">'.
							$anchorStart.
							'<img src="'.$image->src.'" alt="'.$image->alt.'" />'.
							$anchorEnd.
							$caption.
						'</div>';
					}
					return $html;
					
				}	
				
				break;
			// Generate pullquote block
			case 'pullquote':
				if(!empty($this->pullquote)) {
					$authorAttribution = (!empty($this->author)) ? '<span class="quote-attr"><span></span>'.$this->author.'</span>' : '';
					
					// Generate HTML for pullquote block
					$html = 
					'<div class="cpb-quote '.$this->style.'" style="max-width:'.intval($this->width).'px;">'.
						'<blockquote>'.$this->pullquote.'</blockquote>'.
						$authorAttribution.
					'</div>';
					
					return $html;
				}
				break;
			
			// No blocks needed
			case 'text':
			default:
				// Do nothing
				break;
		}
		return '';
	}
	
	
	
	//========================================================
	// WYSIWYG HELPER FUNCTIONS (COPIED FROM "CONTENT" BLOCK)
	//========================================================
	
	function br2nl($str) {
		$str = str_replace("\r\n", "\n", $str);
		$str = str_replace("<br />\n", "\n", $str);
		return $str;
	}
	
	function translateFromEditMode($text) {
		// old stuff. Can remove in a later version.
		$text = str_replace('href="{[CCM:BASE_URL]}', 'href="' . BASE_URL . DIR_REL, $text);
		$text = str_replace('src="{[CCM:REL_DIR_FILES_UPLOADED]}', 'src="' . BASE_URL . REL_DIR_FILES_UPLOADED, $text);

		// we have the second one below with the backslash due to a screwup in the
		// 5.1 release. Can remove in a later version.

		$text = preg_replace(
			array(
				'/{\[CCM:BASE_URL\]}/i',
				'/{CCM:BASE_URL}/i'),
			array(
				BASE_URL . DIR_REL,
				BASE_URL . DIR_REL)
			, $text);
			
		// now we add in support for the links
		
		$text = preg_replace(
			'/{CCM:CID_([0-9]+)}/i',
			BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME . '?cID=\\1',
			$text);

		// now we add in support for the files
		
		$text = preg_replace_callback(
			'/{CCM:FID_([0-9]+)}/i',
			array('contentPlusBlockController', 'replaceFileIDInEditMode'),
			$text);
		

		return $text;
	}
	
	function translateFrom($text) {
		// old stuff. Can remove in a later version.
		$text = str_replace('href="{[CCM:BASE_URL]}', 'href="' . BASE_URL . DIR_REL, $text);
		$text = str_replace('src="{[CCM:REL_DIR_FILES_UPLOADED]}', 'src="' . BASE_URL . REL_DIR_FILES_UPLOADED, $text);

		// we have the second one below with the backslash due to a screwup in the
		// 5.1 release. Can remove in a later version.

		$text = preg_replace(
			array(
				'/{\[CCM:BASE_URL\]}/i',
				'/{CCM:BASE_URL}/i'),
			array(
				BASE_URL . DIR_REL,
				BASE_URL . DIR_REL)
			, $text);
			
		// now we add in support for the links
		
		$text = preg_replace_callback(
			'/{CCM:CID_([0-9]+)}/i',
			array('contentPlusBlockController', 'replaceCollectionID'),
			$text);

		$text = preg_replace_callback(
			'/<img [^>]*src\s*=\s*"{CCM:FID_([0-9]+)}"[^>]*>/i',
			array('contentPlusBlockController', 'replaceImageID'),
			$text);

		// now we add in support for the files that we view inline			
		$text = preg_replace_callback(
			'/{CCM:FID_([0-9]+)}/i',
			array('contentPlusBlockController', 'replaceFileID'),
			$text);

		// now files we download
		
		$text = preg_replace_callback(
			'/{CCM:FID_DL_([0-9]+)}/i',
			array('contentPlusBlockController', 'replaceDownloadFileID'),
			$text);
		
		return $text;
	}
	
	private function replaceFileID($match) {
		$fID = $match[1];
		if ($fID > 0) {
			$path = File::getRelativePathFromID($fID);
			return $path;
		}
	}
	
	private function replaceImageID($match) {
		$fID = $match[1];
		if ($fID > 0) {
			preg_match('/width\s*="([0-9]+)"/',$match[0],$matchWidth);
			preg_match('/height\s*="([0-9]+)"/',$match[0],$matchHeight);
			$file = File::getByID($fID);
			if (is_object($file) && (!$file->isError())) {
				$imgHelper = Loader::helper('image');
				$maxWidth = ($matchWidth[1]) ? $matchWidth[1] : $file->getAttribute('width');
				$maxHeight = ($matchHeight[1]) ? $matchHeight[1] : $file->getAttribute('height');
				if ($file->getAttribute('width') > $maxWidth || $file->getAttribute('height') > $maxHeight) {
					$thumb = $imgHelper->getThumbnail($file, $maxWidth, $maxHeight);
					return preg_replace('/{CCM:FID_([0-9]+)}/i', $thumb->src, $match[0]);
				}
			}
			return $match[0];
		}
	}

	private function replaceDownloadFileID($match) {
		$fID = $match[1];
		if ($fID > 0) {
			$c = Page::getCurrentPage();
			return View::url('/download_file', 'view', $fID, $c->getCollectionID());
		}
	}

	private function replaceFileIDInEditMode($match) {
		$fID = $match[1];
		return View::url('/download_file', 'view_inline', $fID);
	}
	
	private function replaceCollectionID($match) {
		$cID = $match[1];
		if ($cID > 0) {
			$c = Page::getByID($cID, 'APPROVED');
			return Loader::helper("navigation")->getLinkToCollection($c);
		}
	}
	
	function translateTo($text) {
		// keep links valid
		$url1 = str_replace('/', '\/', BASE_URL . DIR_REL . '/' . DISPATCHER_FILENAME);
		$url2 = str_replace('/', '\/', BASE_URL . DIR_REL);
		$url3 = View::url('/download_file', 'view_inline');
		$url3 = str_replace('/', '\/', $url3);
		$url3 = str_replace('-', '\-', $url3);
		$url4 = View::url('/download_file', 'view');
		$url4 = str_replace('/', '\/', $url4);
		$url4 = str_replace('-', '\-', $url4);
		$text = preg_replace(
			array(
				'/' . $url1 . '\?cID=([0-9]+)/i', 
				'/' . $url3 . '([0-9]+)\//i', 
				'/' . $url4 . '([0-9]+)\//i', 
				'/' . $url2 . '/i'),
			array(
				'{CCM:CID_\\1}',
				'{CCM:FID_\\1}',
				'{CCM:FID_DL_\\1}',
				'{CCM:BASE_URL}')
			, $text);
		return $text;
	}
	
}