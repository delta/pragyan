/**
 * @package pragyan
 * @author Abhishek Shrivastava i.abhi27 [at] gmail.com
 * @description Tex Plugin for ckEditor for Pragyan CMS
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
var a= {  
        exec:function(editor){ 
			var theSelectedText = editor.getSelection().getNative();
			var FormattedText = '[tex]'+theSelectedText+'[/tex]';
			editor.insertHtml(FormattedText);
        }  
    },
b='tex';  
CKEDITOR.plugins.add(b,{  
        init:function(editor){  
            editor.addCommand(b,a);  
            editor.ui.addButton('tex',{  
                label:'Convert to Image using Tex',   
                icon: this.path + 'tex.png',  
                command:b  
            });  
        }  
    });      

  
CKEDITOR.config.syrinx_siteBase = "";  
