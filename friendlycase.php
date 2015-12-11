<?php
/*
Plugin Name: FriendlyCase
Plugin URI: http://badcat.com/friendlycase/
Description:  Enable Friendly, User-Definable Word Capitalization and Sentence Casing in Post and Page Titles.
Author:  FriendlyCase Team
Author URI: http://badcat.com/friendlycase/
Version: 1.0.7
*/
/*
/--------------------------------------------------------------------\
|                                                                    |
| License: GPL                                                       |
|                                                                    |
| Copyright (C) 2011 - 2012, Joseph Kelter, Saul Rosenbaum,          |
| Sharon Rooney Ross                                                 |
| http://badcat.com , http://visualchutzpah.com,                     |
| http://moonlightcoaching.com/                                      |
| All rights reserved.                                               |
|                                                                    |
| This program is free software; you can redistribute it and/or      |
| modify it under the terms of the GNU General Public License        |
| as published by the Free Software Foundation; either version 2     |
| of the License, or (at your option) any later version.             |
|                                                                    |
| This program is distributed in the hope that it will be useful,    |
| but WITHOUT ANY WARRANTY; without even the implied warranty of     |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the      |
| GNU General Public License for more details.                       |
|                                                                    |
| You should have received a copy of the GNU General Public License  |
| along with this program; if not, write to the                      |
| Free Software Foundation, Inc.                                     |
| 51 Franklin Street, Fifth Floor                                    |
| Boston, MA  02110-1301, USA                                        |
|                                                                    |
\--------------------------------------------------------------------/
*/

add_filter('the_title', 'friendlycase');
function friendlycase($title){
		$ourData = get_option('fc_options');
		$lowerCaseWords = array_map('trim', explode(',', $ourData['fc_text_lc'])); 
		$upperCaseWords = array_map('trim', explode(',', $ourData['fc_text_uc'])); 
		$ignoredWords = array_map('trim', explode(',', $ourData['fc_text_ig']));
		$firstonly = $ourData['fc_firstonly'];
		$wrapinspans = $ourData['fc_wrappit'];
		$words = explode(' ', $title); 
			$countCycles = 0;
			foreach ($words as $k => $word) {
				$countCycles = $countCycles + 1;
				if(in_arrayi($word, $lowerCaseWords)){
					$words[$k] = ($wrapinspans == 1)? "<span class='fc_lc'>".mb_strtolower($word)."</span>":mb_strtolower($word);
				}elseif(in_arrayi($word, $upperCaseWords)){
					$words[$k] = ($wrapinspans == 1)? "<span class='fc_uc'>".strtoupper($word)."</span>":strtoupper($word);
				}elseif(in_arrayi($word, $ignoredWords)){
					$n = in_arrayi($word, $ignoredWords);
					$words[$k] = ($wrapinspans == 1)? "<span class='fc_ig'>".$ignoredWords[$n]."</span>":$ignoredWords[$n];
				}elseif($firstonly == 1){
					if($countCycles == 1){ 
						$words[$k] = ucwords(mb_strtolower($word));
					}else{
						$words[$k] = mb_strtolower($word);
					}
				}else{
					$words[$k] = ucwords(mb_strtolower($word));
				}
			}
		$newtitle = implode(' ', $words);
		return ucfirst($newtitle); 
		}
	function in_arrayi($element,$array){
		return array_search(mb_strtolower($element),array_map('mb_strtolower',$array));
	}
	add_action('admin_init', 'friendlycaseoptions_init' );
	add_action('admin_menu', 'friendlycaseoptions_add_page');
function friendlycaseoptions_init(){
	register_setting( 'friendlycase_options', 'fc_options', 'friendlycaseoptions_validate' );
}
function friendlycaseoptions_add_page() {
	add_options_page('FriendlyCase Settings Page', 'FriendlyCase Settings', 'manage_options', 'friendlycase_options', 'friendlycaseoptions_do_page');
}
function friendlycaseoptions_do_page() {
?>
<div id="fc-wrap">
	<h2>FriendlyCase Settings</h2>
	<h3>Which words in the titles should we change and how?</h3>
	<p>Use a comma separated list in the sections below. <em><strong>Note:</strong> A word may be used in only one section at a time.</em></p>
	<p>Checking the "Wrap FriendlyCased words in CSS Spans" option will wrap your words in FriendlyCase CSS Class spans.<br />
	You can then add these classes to your theme stylesheet.</p>
	<p>Checking the "Make only first word Uppercase" will make the Titles become "Sentence cased".</p>
	<div id="fc-table">
		<form method="post" action="options.php">
		<?php settings_fields('friendlycase_options'); ?>
		<?php
		$ourDefaultData[fc_text_lc] = "a, am, an, and, at, for, of, in, is, on, or, the, to, with";
		$ourDefaultData[fc_text_uc] = "CIA, FBI, NSA";
		$ourDefaultData[fc_text_ig] = "FAQs, FriendlyCase, WordPress"; 
		$ourDefaultData[fc_wrappit] = "1"; 
		$ourDefaultData[fc_firstonly] = "0"; 
		?>
		<?php $options = get_option('fc_options', $ourDefaultData); ?>
			<table class="form-table">
				<tr valign="top"><th scope="row" class="fc-row">Words to always <strong>make lowercase</strong>:<br />
				<span class="description">CSS Class name: <code>fc_lc</code></span></th>
					<td><textarea name="fc_options[fc_text_lc]" class="fc-textarea" rows="4" wrap='on'><?php echo $options['fc_text_lc']; ?></textarea></td>
				</tr>
				<tr valign="top" ><th scope="row"  class="fc-row">Words to always <strong>make UPPERCASE</strong>:<br />
				<span class="description">CSS Class name: <code>fc_uc</code></span></th>
					<td><textarea name="fc_options[fc_text_uc]" class="fc-textarea" rows="4" wrap='on'><?php echo $options['fc_text_uc']; ?></textarea></td>
				</tr>
				<tr valign="top"><th scope="row" class="fc-row">Words to always <strong>leave as typed</strong>:<br />
				<span class="description">CSS Class name: <code>fc_ig</code></span></th>
					<td><textarea name="fc_options[fc_text_ig]" class="fc-textarea" rows="4" wrap='on'><?php echo $options['fc_text_ig']; ?></textarea></td>
				</tr>
				<tr valign="top"><th scope="row" class="fc-row">Wrap FriendlyCased words in CSS spans:</th>
					<td><input type="checkbox" name="fc_options[fc_wrappit]" value="1" <?php checked('1', $options['fc_wrappit']); ?> /></td>
				</tr>
				<tr valign="top"><th scope="row" class="fc-row">Make only first word Uppercase<br />(Also known as Sentence case):</th>
					<td><input type="checkbox" name="fc_options[fc_firstonly]" value="1" <?php checked('1', $options['fc_firstonly']); ?> /></td>
				</tr>
			</table>
	</div>
		<p class="submit"><input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" /></p>
		</form>
</div>
<style type="text/css">
	#fc-table {border: 1px solid #DFDFDF; border-radius: 3px; width: 90%; padding: 20px;}
	#fc-wrap p { padding-top:5px;}
	th.fc-row {width: 250px;}
	.fc-textarea {width: 350px;}
</style>
<?php
}
function friendlycaseoptions_validate($input) {
	$input['fc_text_lc'] =  wp_filter_nohtml_kses($input['fc_text_lc']);
	$input['fc_text_uc'] =  wp_filter_nohtml_kses($input['fc_text_uc']);
	$input['fc_text_ig'] =  wp_filter_nohtml_kses($input['fc_text_ig']);
	$input['fc_wrappit'] =  wp_filter_nohtml_kses($input['fc_wrappit']);
	$input['fc_firstonly'] =  wp_filter_nohtml_kses($input['fc_firstonly']);
	return $input;
}
?>