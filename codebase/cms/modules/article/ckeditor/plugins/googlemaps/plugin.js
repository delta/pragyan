/**
 * @package pragyan
 * @author Abhishek Shrivastava i.abhi27 [at] gmail.com
 * @description Google Maps plugin for ckEditor for Pragyan CMS
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
var a= {  
        exec:function(editor){  
			var theSelectedText = editor.getSelection().getNative();
			var FormattedText = '[googlemaps]'+theSelectedText+'[/googlemaps]';
			editor.insertHtml(FormattedText);
        }  
    },
b='googlemaps';  
CKEDITOR.plugins.add(b,{  
        init:function(editor){  
            editor.addCommand(b,a);  
            editor.ui.addButton('googlemaps',{  
                label:'Display the location in Google Maps',   
                icon: this.path + 'GoogleMaps.gif',  
                command:b  
            });  
        }  
    });      

  
CKEDITOR.config.syrinx_siteBase = "";  
