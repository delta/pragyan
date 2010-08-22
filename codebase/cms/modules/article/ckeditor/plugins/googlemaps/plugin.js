/**
 * @package pragyan
 * @author Abhishek Shrivastava i.abhi27 [at] gmail.com
 * @description Google Maps plugin for ckEditor for Pragyan CMS
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
var aa= {  
        exec:function(editor){ 
			var theSelectedText = editor.getSelection().getNative();
			var FormattedText = '[googlemaps]'+theSelectedText+'[/googlemaps]';
			editor.insertHtml(FormattedText);
        }  
    },
bb='googlemaps';  
CKEDITOR.plugins.add(bb,{  
        init:function(editor){  
            editor.addCommand(bb,aa);  
            editor.ui.addButton('googlemaps',{  
                label:'Display the location in Google Maps',   
                icon: this.path + 'GoogleMaps.gif',  
                command:bb  
            });  
        }  
    });      

  
CKEDITOR.config.syrinx_siteBase = "";  
