<?php
// ensure this file is being included by a parent file
defined('_JEXEC') or die('Restricted access');
/**
 * @version $Id: fun_edit.php 88 2007-09-18 15:47:39Z soeren $
 * @package joomlaXplorer
 * @copyright soeren 2007
 * @author The joomlaXplorer project (http://joomlacode.org/gf/project/joomlaxplorer/)
 * @author The  The QuiX project (http://quixplorer.sourceforge.net)
 * 
 * @license
 * The contents of this file are subject to the Mozilla Public License
 * Version 1.1 (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.mozilla.org/MPL/
 * 
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 * 
 * Alternatively, the contents of this file may be used under the terms
 * of the GNU General Public License Version 2 or later (the "GPL"), in
 * which case the provisions of the GPL are applicable instead of
 * those above. If you wish to allow use of your version of this file only
 * under the terms of the GPL and not to allow others to use
 * your version of this file under the MPL, indicate your decision by
 * deleting  the provisions above and replace  them with the notice and
 * other provisions required by the GPL.  If you do not delete
 * the provisions above, a recipient may use your version of this file
 * under either the MPL or the GPL."
 * 
 * 
 */
	
/**
 * File-Edit Functions
 *
 */
//------------------------------------------------------------------------------
function savefile($file_name) {			// save edited file
	if( get_magic_quotes_gpc() ) {
		$code = stripslashes($GLOBALS['__POST']["code"]);
	}
	else {
		$code = $GLOBALS['__POST']["code"];
	}
	
	$res = $GLOBALS['nx_File']->file_put_contents( $file_name, $code );
	
	if( $res==false || PEAR::isError( $res )) {
		$err = basename($file_name).": ".$GLOBALS["error_msg"]["savefile"];
		if( PEAR::isError( $res ) ) {
			$err .= $res->getMessage();
		}
		show_error( $err );
	}
	
}
//------------------------------------------------------------------------------
function edit_file($dir, $item) {		// edit file
	global $mainframe;
	$jApp = JFactory::getApplication();
	if(($GLOBALS["permissions"]&01)!=01) 
	  show_error($GLOBALS["error_msg"]["accessfunc"]);
	$fname = get_abs_item($dir, $item);
	if(!get_is_file($fname)) 
	  show_error($item.": ".$GLOBALS["error_msg"]["fileexist"]);
	if(!get_show_item($dir, $item)) 
	  show_error($item.": ".$GLOBALS["error_msg"]["accessfile"]);	
	
	if(isset($GLOBALS['__POST']["dosave"]) && $GLOBALS['__POST']["dosave"]=="yes") {
		// Save / Save As
		$item=basename(stripslashes($GLOBALS['__POST']["fname"]));
		$fname2=get_abs_item($dir, $item);
		if(!isset($item) || $item=="") 
		  show_error($GLOBALS["error_msg"]["miscnoname"]);
		if($fname!=$fname2 && @$GLOBALS['nx_File']->file_exists($fname2)) 
		  show_error($item.": ".$GLOBALS["error_msg"]["itemdoesexist"]);
		savefile($fname2);
		$fname=$fname2;
		if( !empty( $GLOBALS['__POST']['return_to'])) {
			$return_to = urldecode($GLOBALS['__POST']['return_to']);
			//$mainframe->redirect( $return_to );
			$jApp->redirect( $return_to );
		}
		elseif( !empty( $GLOBALS['__POST']['return_to_dir'])) {
			//$mainframe->redirect( $_SERVER['PHP_SELF'].'?option=com_ninjaxplorer&dir='.$dir, 'The File '.$item.' was saved.');
			$jApp->redirect($_SERVER['PHP_SELF'].'?option=com_ninjaxplorer&dir='.$dir, 'The File '.$item.' was saved.');
		}
	}
	
	// header
	$s_item=get_rel_item($dir,$item);	if(strlen($s_item)>50) $s_item="...".substr($s_item,-47);
	show_header( $GLOBALS["messages"]["actedit"].": /".$s_item );
	
	$s_info = pathinfo( $s_item );
	$s_extension = str_replace('.', '', $s_info['extension'] );
	switch (strtolower($s_extension)) {
		case 'txt':
		case 'ini':
			$cp_lang = 'text'; break;
		case 'cs':
			$cp_lang = 'csharp'; break;
		case 'css':
			$cp_lang = 'css'; break;
		case 'html':
		case 'htm':
		case 'xml':
		case 'xhtml':
			$cp_lang = 'html'; break;
		case 'java':
			$cp_lang = 'java'; break;
		case 'js':
			$cp_lang = 'javascript'; break;
		case 'pl': 
			$cp_lang = 'perl'; break;
		case 'ruby': 
			$cp_lang = 'ruby'; break;
		case 'sql':
			$cp_lang = 'sql'; break;
		case 'vb':
		case 'vbs':
			$cp_lang = 'vbscript'; break;
		case 'php':
			$cp_lang = 'php'; break;
		default: 
			$cp_lang = 'generic';
	}
	// Form
	echo '<script type="text/javascript" src="components/com_ninjaxplorer/scripts/codemirror/js/codemirror.js"></script>';
	echo '<link rel="stylesheet" type="text/css" href="components/com_ninjaxplorer/scripts/codemirror/css/codemirror.css"/>';
	echo "<br/><form name=\"editfrm\" id=\"editfrm\" method=\"post\" action=\"".make_link("edit",$dir,$item)."\">\n";
	if( !empty( $GLOBALS['__GET']['return_to'])) {
		$close_action = 'window.location=\''.urldecode($GLOBALS['__GET']['return_to']).'\';';
		echo "<input type=\"hidden\" name=\"return_to\" value=\"". $GLOBALS['__GET']['return_to']."\" />\n";
	}
	else {
		$close_action = 'window.location=\''. make_link('list',$dir,NULL)."'";
	}
	$submit_action = 'UpdateCode();document.editfrm.submit();';
	
	echo "
<table class=\"adminform\">
	<tr>
		<td style=\"text-align: center;\">
			<input type=\"button\" value=\"".$GLOBALS["messages"]["btnsave"]."\" onclick=\"$submit_action\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type=\"button\" onclick=\"ResetCode();\" value=\"".$GLOBALS["messages"]["btnreset"]."\" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type=\"button\" value=\"".$GLOBALS["messages"]["btnclose"]."\" onclick=\"javascript:$close_action\" />
		</td>
	</tr>
	<tr>
		<td >
			<div id=\"positionIndicator\" style=\"width: 20%;float:left;\">"
			.$GLOBALS["messages"]["line"].": <input type=\"text\" name=\"txtLine\" class=\"inputbox\" size=\"6\" onchange=\"setCaretPosition(document.editfrm.code, this.value);return false;\" />&nbsp;&nbsp;&nbsp;"
			.$GLOBALS["messages"]["column"].": <input type=\"text\" name=\"txtColumn\" class=\"inputbox\" size=\"6\" readonly=\"readonly\" />
          </div>
			<div id=\"returnDirector\" style=\"width:70%;text-align: center;float:left;\">
				<input type=\"checkbox\" value=\"1\" name=\"return_to_dir\" id=\"return_to_dir\" />
				<label for=\"return_to_dir\">".$GLOBALS["messages"]["returndir"]."</label>
			</div>";
	
	echo "
		</td>
	</tr>
	<tr><td>";

	echo "<input type=\"hidden\" name=\"dosave\" value=\"yes\" />\n";
	
	// Show File In TextArea
	$content = $GLOBALS['nx_File']->file_get_contents( $fname );
	if( get_magic_quotes_runtime()) {
		$content = stripslashes( $content );
	}
	$content = htmlspecialchars( $content );

		//echo '[<a href="javascript:;" onclick="positionIndicator.toggle(); codearea.toggleEditor();return false;">'.$GLOBALS['messages']['editor_simple'].' / '.$GLOBALS['messages']['editor_syntaxhighlight'].'</a>]';
		echo '<div id="editorarea">
		<textarea class="'.$cp_lang.'" style="width:95%;" name="codearea" id="codearea" rows="25" cols="120" wrap="off" onmouseup="updatePosition(this)" onmousedown="updatePosition(this)" onkeyup="updatePosition(this)" onkeydown="updatePosition(this)" onfocus="updatePosition(this)">'
		. $content 
		.'</textarea>
		<input type="hidden" name="code" value="" />
		</div><br/>';
	echo "
	</td>
	</tr>";
		
		echo "
	<tr>
		<td align=\"right\">
			<label for=\"fname\">".$GLOBALS["messages"]["copyfile"]."</label>
			<input type=\"text\" name=\"fname\" value=\"".$item."\" size=\"40\" />
		</td>
	</tr>
</table>
<br/>";
	
	echo "
</form>
<br/>\n";
?>
<script type="text/javascript">
var editor = CodeMirror.fromTextArea('codearea', {
parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "tokenizephp.js", "parsephp.js", "parsephphtmlmixed.js"],
stylesheet: ["components/com_ninjaxplorer/scripts/codemirror/css/csscolors.css", 
			 "components/com_ninjaxplorer/scripts/codemirror/css/jscolors.css", 
			 "components/com_ninjaxplorer/scripts/codemirror/css/phpcolors.css", 
			 "components/com_ninjaxplorer/scripts/codemirror/css/sparqlcolors.css", 
			 "components/com_ninjaxplorer/scripts/codemirror/css/xmlcolors.css"],
path: "components/com_ninjaxplorer/scripts/codemirror/js/",
lineNumbers: true,
tabMode: "indent",
matchBrackets: true,
searchMode: 'inline',
});
function UpdateCode()
{
	document.editfrm.code.value = editor.getCode();
}
function ResetCode()
{
	editor.setCode(document.editfrm.codearea.value);
}

<!--

document.getElementById('positionIndicator').style.display='none';
document.getElementById('returnDirector').style.width='100%';

//http://www.bazon.net/mishoo/home.epl?NEWS_ID=1345
function doGetCaretPosition (textarea) {

	var txt = textarea.value;
	var len = txt.length;
	var erg = txt.split("\n");
	var pos = -1;
	if(typeof textarea.selectionStart != "undefined") { // FOR MOZILLA
		pos = textarea.selectionStart;
	}
	else if(typeof document.selection != "undefined") { // FOR MSIE
		range_sel = document.selection.createRange();
		range_obj = textarea.createTextRange();
		range_obj.moveToBookmark(range_sel.getBookmark());
		range_obj.moveEnd('character',textarea.value.length);
		pos = len - range_obj.text.length;
	}
	if(pos != -1) {
		var ind = 0;
		for(;erg.length;ind++) {
			len = erg[ind].length + 1;
			if(pos < len)
			break;
			pos -= len;
		}
		ind++; pos++;
		return [ind, pos]; // ind = LINE, pos = COLUMN

	}
}
/**
* This function allows us to change the position of the caret
* (cursor) in the textarea
* Various workarounds for IE, Firefox and Opera are included
* Firefox doesn't count empty lines, IE does...
*/
function setCaretPosition( textarea, linenum ) {
	if (isNaN(linenum)) {
		updatePosition( textarea );
		return;
	}
	var txt = textarea.value;
	var len = txt.length;
	var erg = txt.split("\n");
		
	var ind = 0;
	var pos = 0;
	var nonempty = -1;
	var empty = -1;
	for(;ind < linenum;ind++) {
		/*alert( "Springe zu Zeile: "+linenum
				+"\naktuelle Zeile: "+ (ind+1) 
				+ "\naktuelle Position: "+pos 
				+ "\nText in dieser Zeile: "+erg[ind]);*/
		if( !erg[ind] && pos < len ) { empty++; pos++; continue; }
		else if( !erg[ind] ) break;
		pos += erg[ind].length;
		nonempty++;
	}
	try {
		pos -= erg[ind-1].length;	
	} catch(e) {}
	
	textarea.focus();
	
	if(textarea.setSelectionRange)
	{
		pos += nonempty;
		textarea.setSelectionRange(pos,pos);
	}
	else if (textarea.createTextRange) {
		pos -= empty;
		var range = textarea.createTextRange();
		range.collapse(true);
		range.moveEnd('character', pos);
		range.moveStart('character', pos);
		
		range.select();
	}
}
/** 
* Updates the Position Indicator fields
*/
function updatePosition(textBox) {
	var posArray = doGetCaretPosition(textBox);
    document.forms[0].txtLine.value = posArray[0];
    document.forms[0].txtColumn.value = posArray[1];
}
// -->
</script><?php

}
//------------------------------------------------------------------------------
?>
