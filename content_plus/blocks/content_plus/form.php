<?php   
/**
 * @author 		Daniel Mitchell <glockops \at\ gmail.com>
 * @copyright  	Copyright (c) 2012 D. Mitchell.
 * @license    	Creative Commons Attribution-ShareAlike 3.0 Unported
 *				http://creativecommons.org/licenses/by-sa/3.0/
 */
defined('C5_EXECUTE') or die(_("Access Denied.")); 
?>
<div class="content-plus-ui ccm-ui">
    
    <!-- Tabs -->
    <ul class="nav nav-tabs" data-tabs="tabs">
        <li class="active"><a href="#plusContent" data-toggle="tab"><?php echo t("Content")?></a></li>
        <li><a href="#plusImage" data-toggle="tab"><?php echo t("Image")?></a></li>
        <li><a href="#plusPullquote" data-toggle="tab"><?php echo t("Pullquote")?></a></li>
        <li><a href="#plusLayout" data-toggle="tab"><?php echo t("Layout &amp; Style")?></a></li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content">
        
        <!-- Content Tab -->
        <div id="plusContent" class="tab-pane active form-stacked">
            <div class="clearfix" style="width:99%">
				<?php 
                // Load TinyMCE Editor Controls
                Loader::element('editor_init');
                Loader::element('editor_config');
                Loader::element('editor_controls'); 
                ?>
                <textarea id="ccm-content" class="advancedEditor ccm-advanced-editor" name="content"><?php  echo $content; ?></textarea>
            </div>
        </div>
        
        <!-- Content Image -->
        <div id="plusImage" class="tab-pane">
        	<h3><?php echo t("Image Options")?></h3>
            <div class="clearfix options">
            	<label class="control-label"><?php echo t("Enable")?></label>
                <div class="input">
                    <ul class="inputs-list">
                        <li>
                            <label class="radio">
                            <?php echo $form->checkbox('enable_image','1',$enable_image,array('class'=>'enable_pane','style'=>'display:inline'))?>
                            <?php echo t("Enable Image Placement (this disables pullquote placement)")?>
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="plus_options image_options">
                <div class="clearfix">
                    <?php echo $form->label('fID',t('Choose Image'))?>
                    <div class="input"><?php   echo $al->image('fID', 'fID', t('Choose Image'),$imageFID);?></div>
                </div>
                <div class="clearfix">
                    <?php echo $form->label('img_width',t('Width / Height'))?>
                    <div class="input"><?php echo $form->text('img_width',$width, array('class'=>'input-mini'))?> X <?php echo $form->text('img_height',$height, array('class'=>'input-mini'))?> px <span class="help-block"><?php echo t("Used to resize image")?></span></div>
                </div>
                <div class="clearfix">
                    <?php echo $form->label('caption',t('Caption'))?>
                    <div class="input">
                        <ul class="inputs-list">
                            <li><label><?php echo $form->checkbox('caption', 1, $caption)?> &nbsp; <?php echo t("Use the image description as the caption.")?></label></li>
                            <li><label><?php echo $form->checkbox('captionCentered', 1, $captionCentered)?> &nbsp; <?php echo t("Center the caption.")?></label></li>
                        </ul>
                    </div>
                </div>
                <div class="clearfix">
                    <?php echo $form->label('link_type',t('Link Image'))?>
                    <div class="input">
                        <?php echo $form->select('link_type',$link_types,$link_type,array('class'=>'input-small'))?>
                        <div class="link_option" id="lkpage">
                            <?php echo $pageselect->selectPage('link_page',$link_page)?>
                        </div>
                        <div class="link_option" id="lkurl">
                            <?php echo $form->text('link_url',$link_url,array('class'=>'input-large'))?>
                        </div>
                        <div class="link_option" id="lkoptions">
                        	<label><?php echo $form->checkbox('link_target', true, $link_target)?> <?php echo t("Open in new window.")?></label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content Pullquote -->
        <div id="plusPullquote" class="tab-pane form-stacked">
        	<h3><?php echo t("Pullquote Options")?></h3>
            <div class="clearfix options">
            	<label class="control-label"><?php echo t("Enable")?></label>
                <div class="input">
                    <ul class="inputs-list">
                        <li>
                            <label class="radio">
                                <?php echo $form->checkbox('enable_quote','1',$enable_quote,array('class'=>'enable_pane','style'=>'display:inline'))?>
                               <?php echo t("Enable Pullquote Placement (this disables image placement)")?> 
                            </label>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="plus_options quote_options">
            	<div class="clearfix">
                    <?php echo $form->label('pullquote',t('Pullquote'))?>
                    <div class="input"><?php echo $form->textarea('pullquote',$pullquote, array('class'=>'input-xlarge','rows'=>'4'))?></div>
                </div>
                <div class="clearfix">
                    <?php echo $form->label('author',t('Author'))?>
                    <div class="input"><?php echo $form->text('author',$author, array('class'=>'input-xlarge'))?></div>
                </div>
                <div class="clearfix">
					<?php echo $form->label('quote_width',t('Width'))?>
                    <div class="input"><?php echo $form->text('quote_width',$width, array('class'=>'input-mini'))?> px</div>
                </div>

            </div>
       </div>
        
        <!-- Content Layout -->
        <div id="plusLayout" class="tab-pane form-stacked">
        	<h3><?php echo t("Layout Options")?></h3>
            <div class="help-block">
            	<p><?php echo t("Image or pullquote must be enabled to use layouts or styles")?></p>
            </div>
            <div class="plus_options layout_options">
            	<div class="clearfix">
                    <?php echo $form->label('layout',t('Choose Layout'))?>
                    <div class="input">
                        <?php echo $form->select('layout',$layouts,$layout,array('class'=>'input-large'))?>
                    </div>
                </div>
            </div> 
            <div class="plus_options image_options">
            	<div class="clearfix">
                    <?php echo $form->label('img_style',t('Choose Style'))?>
                    <div class="input">
                        <?php echo $form->select('img_style',$img_styles,$img_style,array('class'=>'input-large'))?>
                    </div>
                </div>
            </div> 
            <div class="plus_options quote_options">
            	<div class="clearfix">
                    <?php echo $form->label('quote_style',t('Choose Style'))?>
                    <div class="input">
                        <?php echo $form->select('quote_style',$quote_styles,$quote_style,array('class'=>'input-large'))?>
                    </div>
                </div>
            </div>          
        </div>
        
    </div>    
        
</div>