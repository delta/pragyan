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
