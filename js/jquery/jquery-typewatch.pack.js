/*
 *	TypeWatch 0.3.0
 *	requires jQuery 1.1.3
 *	
 *	Examples/Docs: www.dennydotnet.com
 *	Copyright(c) 2007 Denny Ferrassoli
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
*/

jQuery.fn.extend({typeWatch:function(A){waitTextbox(this,A);}});function waitTextbox(A,B){A.each(function(){thisEl=jQuery(this);if(this.type.toLowerCase()=="text"||this.nodeName.toLowerCase()=="textarea"){var C=750;var D=function(){};var E=true;var F=window["tmr_"+thisEl[0].id];var G=window["txt_"+thisEl[0].id];var H=window["cb_"+thisEl[0].id];if(F!=null)window["tmr_"+thisEl[0].id]=null;if(G!=null)window["txt_"+thisEl[0].id]=null;if(H!=null)window["cb_"+thisEl[0].id]=null;if(B){if(B["wait"]!=null)C=parseInt(B["wait"]);if(B["callback"]!=null)D=B["callback"];if(B["highlight"]!=null)E=B["highlight"];}window["txt_"+thisEl[0].id]=thisEl.val().toLowerCase();window["cb_"+thisEl[0].id]=D.toString();window["tmr_"+thisEl[0].id]=setTimeout(buildFunc(thisEl[0].id,D.toString(),C),C);if(E){thisEl.focus(function(){this.select();});}thisEl.keydown(function(){clearTimeout(window["tmr_"+this.id]);window["tmr_"+this.id]=setTimeout(buildFunc(this.id,window["cb_"+this.id],C),C);});}});}function waitTextboxCheck(A,B,C){var D=jQuery("#"+A).val();if(D.length>2&&D.toLowerCase()!=window["txt_"+A]){window["txt_"+A]=D.toLowerCase();B(D);}window["tmr_"+A]=setTimeout(buildFunc(A,B,C),C);}function buildFunc(A,B,C){return "waitTextboxCheck('"+A+"', "+B+", "+C+")"}